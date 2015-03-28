<?php
namespace Klit\Common\RowMapperBundle\Services\Query;

use Doctrine\Common\Cache\Cache;
use Klit\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\AndType;
use Klit\Common\RowMapperBundle\Services\Query\Type\BraceType;
use Klit\Common\RowMapperBundle\Services\Query\Type\CloseType;
use Klit\Common\RowMapperBundle\Services\Query\Type\CommaType;
use Klit\Common\RowMapperBundle\Services\Query\Type\DeleteType;
use Klit\Common\RowMapperBundle\Services\Query\Type\EqualsType;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldlistType;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldType;
use Klit\Common\RowMapperBundle\Services\Query\Type\FunctionType;
use Klit\Common\RowMapperBundle\Services\Query\Type\GroupType;
use Klit\Common\RowMapperBundle\Services\Query\Type\InsertType;
use Klit\Common\RowMapperBundle\Services\Query\Type\IsNullType;
use Klit\Common\RowMapperBundle\Services\Query\Type\JoinType;
use Klit\Common\RowMapperBundle\Services\Query\Type\LimitType;
use Klit\Common\RowMapperBundle\Services\Query\Type\NullType;
use Klit\Common\RowMapperBundle\Services\Query\Type\OnType;
use Klit\Common\RowMapperBundle\Services\Query\Type\OrderByType;
use Klit\Common\RowMapperBundle\Services\Query\Type\OrderType;
use Klit\Common\RowMapperBundle\Services\Query\Type\OrType;
use Klit\Common\RowMapperBundle\Services\Query\Type\SelectType;
use Klit\Common\RowMapperBundle\Services\Query\Type\TableType;
use Klit\Common\RowMapperBundle\Services\Query\Type\TypeInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\UpdateType;
use Klit\Common\RowMapperBundle\Services\Query\Type\UsingType;
use Klit\Common\RowMapperBundle\Services\Query\Type\ValueType;
use Klit\Common\RowMapperBundle\Services\Query\Type\WhereType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @name Builder
 * @version 1.0.0-dev
 * @since v2.0.0
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class Builder {
    /** @var array the statement */
    private $statement = [];
    /** @var ParserInterface */
    private $Parser;
    /** @var Cache Doctrine cache interface */
    private $Cache;

    function __construct(ParserInterface $Parser, Cache $Cache = null) {
        $this->Cache = $Cache;
        $this->Parser = $Parser;
    }

    public function setParser(ParserInterface $Parser) {
        $this->Parser = $Parser;
    }

    private function append(TypeInterface $type) {
        $this->statement[] = $type;
    }

    public function select() {
        $this->append(new SelectType());
        return $this;
    }

    public function update($table) {
        $this->append(new UpdateType($table));
        return $this;
    }

    public function delete($table) {
        $this->append(new DeleteType($table));
        return $this;
    }

    public function insert($table) {
        $this->append(new InsertType($table));
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

    /**
     * Synonym for close()
     */
    public function end() {
        $this->close();
    }

    /**
     * Select a field<br />
     * Provide a field name or a path to the field (e.g.: database.table.field as array(database, table, field))
     *
     * @param string|array $identifier path of field or field name
     * @return $this
     */
    public function field($identifier) {
        $this->append(new FieldType($identifier));
        return $this;
    }

    /**
     * Opens a new function<br />
     * <br />
     * <i>close this type by close()</i>
     * @param $name
     * @return $this
     */
    public function f($name) {
        $this->append(new FunctionType($name));
        return $this->brace();
    }

    public function equals() {
        $this->append(new EqualsType());
        return $this;
    }

    public function value($value) {
        $this->append(new ValueType($value));
        return $this;
    }

    public function null() {
        $this->append(new NullType());
        return $this;
    }
    public function isNull() {
        $this->append(new IsNullType());
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

    public function join($table, $type = 'inner') {
        $this->append(new JoinType($table, $type));
        return $this;
    }

    public function using($field) {
        $this->append(new UsingType($field));
        return $this;
    }

    public function on() {
        $this->append(new OnType());
        return $this;
    }

    /**
     * Add a new group<br />
     * <br />
     * Provide a single group by field as parameter $field<br />
     * After this method, add fields by field()<br />
     * <br />
     * <i>must be closed by close()</i>
     * @param null $field
     * @return $this
     */
    public function groupBy($field = null) {
        $this->append(new GroupType());
        if ($field != null) {
            $this->append(new FieldType($field));
            $this->append(new CloseType());
        }
        return $this;
    }

    /**
     * Add a new order<br />
     * <br />
     * <i>must be closed by close()</i>
     * @return $this
     */
    public function order() {
        $this->append(new OrderType());
        return $this;
    }

    /**
     * Add a complete order by command
     *
     * @param array $orders
     * @return $this
     */
    public function orderBy(array $orders) {
        $this->append(new OrderType());
        $idx = 0;
        foreach ($orders as $field => $direction) {
            if (is_numeric($field)) {
                $this->append(new OrderByType($direction));
            } else {
                $this->append(new OrderByType($field, $direction));
            }
            if (++$idx < count($orders)) {
                $this->append(new CommaType());
            }
        }
        $this->append(new CloseType());
        return $this;
    }

    /**
     * Add a order by field
     *
     * @param $field
     * @param string $order
     * @return $this
     */
    public function by($field, $order = 'desc') {
        $this->append(new OrderByType($field, $order));
        return $this;
    }

    public function connect($relation = '&') {
        switch (strtolower($relation)) {
            case 'and' :
            case '&' :
            case '&&' :
                $this->append(new AndType());
                return $this;
            case 'or' :
            case '|' :
            case '||' :
                $this->append(new OrType());
                return $this;
        }
        throw new \Exception("unknown connection type: " . $relation);
    }

    public function c() {
        $this->append(new CommaType());
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
        if ($this->Parser === null) {
            throw new \Exception("no parser given");
        }

        // try to use cache
        $data = $this->validateCache($this->statement);
        if ($data !== false && isset($data['statement']) && isset($data['query'])) {
            $this->statement = $data['statement'];
            $Query = $data['query'];
        }

        if (!isset($Query)) {
            $this->Parser->setStatement($this->statement);
            $this->Parser->execute();
            $Query = new SqlQuery(
                $this->Parser->getSqlQuery(),
                $this->Parser->getParameters()
            );
        }

        $this->cacheItem($this->getHash($this->statement), $this->statement, $Query);
        $this->clear();
        return $Query;
    }

    private function validateCache(array $statement) {
        if (!is_object($this->Cache)) {
            return false;
        }
        $hash = $this->getHash($statement);
        if ($this->Cache->contains($hash)) {
            $data = $this->Cache->fetch($hash);
            return unserialize($data);
        }
        return false;
    }

    private function cacheItem($hash, array $statement, SqlQuery $Query) {
        if (is_object($this->Cache)) {
            $this->Cache->save($hash, serialize(array(
                'statement' => $statement,
                'query' => $Query
            )), 3600);
        }
    }

    private function getHash(array $statement) {
        return md5(serialize($statement));
    }

    /**
     * Clear the class
     */
    private function clear() {
        $this->statement = [];
    }
}
