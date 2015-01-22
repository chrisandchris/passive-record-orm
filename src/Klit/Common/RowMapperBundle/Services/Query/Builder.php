<?php
namespace Klit\Common\RowMapperBundle\Services\Query;

use Klit\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\AndType;
use Klit\Common\RowMapperBundle\Services\Query\Type\BraceType;
use Klit\Common\RowMapperBundle\Services\Query\Type\CloseType;
use Klit\Common\RowMapperBundle\Services\Query\Type\EqualsType;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldlistType;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldType;
use Klit\Common\RowMapperBundle\Services\Query\Type\LimitType;
use Klit\Common\RowMapperBundle\Services\Query\Type\OrType;
use Klit\Common\RowMapperBundle\Services\Query\Type\SelectType;
use Klit\Common\RowMapperBundle\Services\Query\Type\TableType;
use Klit\Common\RowMapperBundle\Services\Query\Type\TypeInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\ValueType;
use Klit\Common\RowMapperBundle\Services\Query\Type\WhereType;

/**
 * @name Builder
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class Builder {
    private $statement = [];
    /** @var ParserInterface */
    private $parser;

    public function setParser(ParserInterface $parser) {
        $this->parser = $parser;
    }

    private function append(TypeInterface $type) {
        $idx = count($this->statement) - 1;
        if ($idx > 0) {
            if (!$this->isAllowedCall($this->statement[$idx], $type)) {
                throw new \Exception("Illegal call: " . $type->getTypeName()
                    . ' after ' . $this->statement[$idx]->getTypeName());
            }
        }
        $this->statement[] = $type;
    }

    private function isAllowedCall(TypeInterface $parent, TypeInterface $called) {
        if ($parent->getAllowedChildren() === null) {
            return true;
        }
        foreach ($parent->getAllowedChildren() as $Type) {
            if ($called instanceof $Type) {
                return true;
            }
        }
        return false;
    }

    public function select() {
        $this->append(new SelectType());
        return $this;
    }

    public function table($table) {
        $this->append(new TableType($table));
        return $this;
    }

    public function fieldlist(array $fields) {
        $this->append(new FieldlistType($fields));
        return $this;
    }

    public function where() {
        $this->append(new WhereType());
        return $this;
    }

    public function close() {
        $this->append(new CloseType());
        return $this;
    }

    public function field($field) {
        $this->append(new FieldType($field));
        return $this;
    }

    public function equals() {
        $this->append(new EqualsType());
        return $this;
    }

    public function value($value) {
        $this->append(new ValueType($value));
        return $this;
    }

    public function brace() {
        $this->append(new BraceType());
        return $this;
    }

    public function limit($limit = 1) {
        $this->append(new LimitType($limit));
        return $this;
    }

    public function connect($relation = '&') {
        switch ($relation) {
            case '&' :
            case '&&' :
                $this->append(new AndType());
                return $this;
            case '|' :
            case '||' :
                $this->append(new OrType());
                return $this;
        }
        throw new \Exception("unknown connection type: " . $relation);
    }

    public function call($type, $data) {
        if (class_exists('Klit\Common\RowMapperBundle\Services\Query\Type\\' . $type)) {
            $classname = ('Klit\Common\RowMapperBundle\Services\Query\Type\\' . $type);
            $Type = new $classname;
        } else if (class_exists($type)) {
            $Type = new $type;
        } else {
            throw new \Exception("no such class known");
        }

        /** @var $Type TypeInterface */
        $Type->call($data);
        return $this;
    }

    /**
     * Get the query array
     *
     * @return array
     */
    public function getStatement() {
        return $this->statement;
    }

    /**
     * @return SqlQuery
     * @throws \Exception
     */
    public function getSqlQuery() {
        if ($this->parser === null) {
            throw new \Exception("no parser given");
        }
        $this->parser->setStatement($this->statement);
        var_dump($this->statement);
        $this->parser->execute();
        return new SqlQuery(
            $this->parser->getSqlQuery(),
            $this->parser->getParameters()
        );
    }
}
