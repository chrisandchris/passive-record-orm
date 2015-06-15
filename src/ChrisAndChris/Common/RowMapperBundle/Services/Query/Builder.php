<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\ValuesType;
use Doctrine\Common\Cache\Cache;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\AliasType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\AndType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\AnyType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\BraceType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\CloseType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\CommaType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\ComparisonType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\DeleteType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\EqualsType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\FieldlistType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\FieldType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\FunctionType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\GroupType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\InsertType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\IsNullType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\JoinType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\LimitType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\NullType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OffsetType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OnType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OrderByType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OrderType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OrType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\SelectType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TableType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TypeInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\UpdateType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\UsingType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\ValueType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\WhereType;

/**
 * @name Builder
 * @version   1.0.0
 * @since     v2.0.0
 * @package   CommonRowMapper
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
 */
class Builder {

    /** @var array the statement */
    private $statement = [];
    /** @var ParserInterface */
    private $Parser;
    /** @var Cache Doctrine cache interface */
    private $Cache;
    /** @var array an array, which handles the if/else-statements */
    private $stopPropagation = [];

    function __construct(ParserInterface $Parser, Cache $Cache = null) {
        $this->Cache = $Cache;
        $this->Parser = $Parser;
    }

    public function setParser(ParserInterface $Parser) {
        $this->Parser = $Parser;
    }

    private function append(TypeInterface $type) {
        // for speed, we first check only the last index
        // if the last index says we should append, we check all other indexes
        // any of the index must be false
        $maxIndex = $this->getHighestPropagationKey();
        if ($maxIndex !== null && $this->stopPropagation[$maxIndex] === true) {
            return;
        } else {
            // do check only if the latest says that we should append
            foreach ($this->stopPropagation as $status) {
                if ($status === true) {
                    return;
                }
            }
        }
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

    public function table($table, $alias = null) {
        $this->append(new TableType($table, $alias));

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

    public function alias($alias) {
        $this->append(new AliasType($alias));

        return $this;
    }

    public function close() {
        $this->append(new CloseType());

        return $this;
    }

    /**
     * Synonym for close()
     *
     * @return $this
     */
    public function end() {
        $this->close();

        return $this;
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
     *
     * @param $name
     * @return $this
     */
    public function f($name) {
        $this->append(new FunctionType($name));

        return $this;
    }

    public function any() {
        $this->append(new AnyType());

        return $this;
    }

    public function equals() {
        $this->append(new EqualsType());

        return $this;
    }

    public function compare($comparison) {
        $comparisons = [
            '<',
            '>',
            '<>',
            '=',
            '!=',
            '>=',
            '<='
        ];
        if (in_array($comparisons, $comparison)) {
            $this->append(new ComparisonType($comparison));
        }
        throw new \Exception("No such comparison known");
    }

    /**
     * Adds a new raw value to the statement<br />
     * The value gets encoded as parameter
     *
     * @param $value
     * @return $this
     */
    public function value($value) {
        $this->append(new ValueType($value));

        return $this;
    }

    /**
     * Adds a new VALUES()-Statement
     *
     * @return $this
     */
    public function values() {
        $this->append(new ValuesType());

        return $this;
    }

    public function null() {
        $this->append(new NullType());

        return $this;
    }

    public function isNull($isNull = true) {
        if ($isNull) {
            $this->append(new IsNullType());
        } else {
            $this->append(new IsNullType(false));
        }

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

    public function offset($offset = 0) {
        $this->append(new OffsetType($offset));

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
     *
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
     *
     * @return $this
     */
    public function order() {
        $this->append(new OrderType());

        return $this;
    }

    /**
     * If the condition is true, the following types will be added<br />
     * If not, until the next _end() or _else() nothing will be added to the query
     *
     * @param bool $condition the condition to validate
     * @return $this
     */
    public function _if($condition) {
        $condition = (bool)$condition;
        if ($condition !== true) {
            $this->stopPropagation[] = true;
        } else {
            $this->stopPropagation[] = false;
        }

        return $this;
    }

    /**
     * Else condition if the _if() condition failed
     *
     * @return $this
     * @throws MalformedQueryException
     */
    public function _else() {
        // simply swap the latest item propagation
        $maxIndex = $this->getHighestPropagationKey();
        if ($maxIndex === null) {
            throw new MalformedQueryException("No if statement previous to else. If required");
        }
        $this->stopPropagation[$maxIndex] = !($this->stopPropagation[$maxIndex]);

        return $this;
    }

    private function getHighestPropagationKey() {
        if (count($this->stopPropagation) == 0) {
            return null;
        }

        return max(array_keys($this->stopPropagation));
    }

    /**
     * Reset propagation state
     *
     * @return $this
     * @throws MalformedQueryException
     */
    public function _end() {
        $maxIndex = $this->getHighestPropagationKey();

        if ($maxIndex === null) {
            throw new MalformedQueryException("Unable to close condition statement. Opening required.");
        }

        // delete highest key
        unset($this->stopPropagation[$this->getHighestPropagationKey()]);

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
     * @param        $field
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
            $this->Cache->save($hash, serialize([
                'statement' => $statement,
                'query'     => $Query
            ]), 3600);
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
