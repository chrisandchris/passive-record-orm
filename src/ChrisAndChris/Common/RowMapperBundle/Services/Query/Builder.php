<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\SystemException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TypeNotFoundException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use Doctrine\Common\Cache\Cache;

/**
 * @name Builder
 * @version   1.0.3
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class Builder {

    /** @var array the statement */
    private $statement = [];
    /** @var ParserInterface */
    private $parser;
    /** @var Cache Doctrine cache interface */
    private $cache;
    /** @var array an array, which handles the if/else-statements */
    private $stopPropagation = [];
    /** @var bool indicator, if the current query uses closures */
    private $usedClosures = false;
    /** @var TypeBag */
    private $typeBag;

    function __construct(ParserInterface $parser, TypeBag $parameterBag, Cache $cache = null) {
        $this->cache = $cache;
        $this->parser = $parser;
        $this->typeBag = $parameterBag;
    }

    public function setParser(ParserInterface $parser) {
        $this->parser = $parser;
    }

    public function select() {
        $this->append('select');

        return $this;
    }

    private function append($typeName, array $params = []) {
        if ($this->allowAppend() && $this->typeBag->has($typeName)) {
            $endParams = [];
            $type = $this->typeBag->get($typeName);
            if (is_array($type['params'])) {
                foreach ($type['params'] as $param) {
                    if (!isset($type['required'])) {
                        $type['required'] = [];
                    }
                    if (in_array($param, $type['required']) &&
                        !isset($params[$param])
                    ) {
                        throw new MissingParameterException(
                            'Parameter "' . $param . '" for type "' .
                            $typeName .
                            '" is missing."'
                        );
                    }
                    if (isset($params[$param])) {
                        if ($params[$param] instanceof \Closure) {
                            $this->usedClosures = true;
                            $params[$param] = $params[$param]();
                        }
                        $endParams[$param] = $params[$param];
                    } else {
                        $endParams[$param] = null;
                    }
                }
            }
            $this->statement[] = [
                'type'   => $typeName,
                'params' => $endParams,
            ];
        } elseif (!$this->typeBag->has($typeName)) {
            throw new TypeNotFoundException(
                'No type "' . $typeName . '" found'
            );
        }
    }

    private function allowAppend() {
        // for speed, we first check only the last index
        // if the last index says we should append, we check all other indexes
        // any of the index must be false
        $maxIndex = $this->getHighestPropagationKey();
        if ($maxIndex !== null && $this->stopPropagation[$maxIndex] === true) {
            return false;
        } else {
            // do check only if the latest says that we should append
            foreach ($this->stopPropagation as $status) {
                if ($status === true) {
                    return false;
                }
            }
        }

        return true;
    }

    private function getHighestPropagationKey() {
        if (count($this->stopPropagation) == 0) {
            return null;
        }

        return max(array_keys($this->stopPropagation));
    }

    public function update($table) {
        $this->append('update', ['table' => $table]);

        return $this;
    }

    public function delete($table) {
        $this->append('delete', ['table' => $table]);

        return $this;
    }

    public function insert($table, $mode = null) {
        $this->append('insert', ['table' => $table, 'mode' => $mode]);

        return $this;
    }

    public function table($table, $alias = null) {
        $this->append('table', ['table' => $table, 'alias' => $alias]);

        return $this;
    }

    /**
     * Append a fieldlist. Accepts an array with following options<br />
     * <br />
     * <ul>
     *  <li>extended: key is real table/field name, value is alias</li>
     *  <li>simple: value is real table/field name, key does not matter</li>
     * </ul>
     * You have to separate table and field names by double-colon (":")
     *
     * @param array $fields
     * @return $this
     */
    public function fieldlist(array $fields) {
        $this->append('fieldlist', ['fields' => $fields]);

        return $this;
    }

    public function where() {
        $this->append('where');

        return $this;
    }

    public function alias($alias) {
        $this->append('alias', ['alias' => $alias]);

        return $this;
    }

    /**
     * Synonym for close()
     *
     * @return $this
     */
    public function end() {
        return $this->close();
    }

    public function close() {
        $this->append('close');

        return $this;
    }

    /**
     * Select a field<br />
     * Array usage of $identifier is deprecated, use only with double-colon<br
     * />
     * <br />
     * database:table:field parses to database.table.field
     *
     * @param string $identifier path of field or field name
     * @return $this
     */
    public function field($identifier) {
        $this->append('field', ['identifier' => $identifier]);

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
        $this->append('function', ['name' => $name]);

        return $this;
    }

    public function any() {
        $this->append('any');

        return $this;
    }

    public function equals() {
        $this->append('equals');

        return $this;
    }

    public function compare($comparison) {
        $this->append('comparison', ['comparison' => $comparison]);

        return $this;
    }

    /**
     * Adds a new raw value to the statement<br />
     * The value gets encoded as parameter<br />
     * If you give a closure, the return value of the closure is used
     *
     * @param mixed|\Closure $value
     * @return $this
     */
    public function value($value) {
        $this->append('value', ['value' => $value]);

        return $this;
    }

    /**
     * Adds a new VALUES()-Statement
     *
     * @return $this
     */
    public function values() {
        $this->append('values');

        return $this;
    }

    public function null() {
        $this->append('null');

        return $this;
    }

    /**
     * Add a new IN()-clause<br />
     * <br />
     * If is $in is an array, each contained value is a parameter,
     * else use builder to build query and close with close()
     *
     * @param null|array $in
     * @return $this
     */
    public function in($in = null) {
        $this->append('in', ['in' => $in]);

        return $this;
    }

    public function isNull($isNull = true) {
        if ($isNull) {
            $this->append('isnull', ['isnull' => true]);
        } else {
            $this->append('isnull', ['isNull' => false]);
        }

        return $this;
    }

    public function brace() {
        $this->append('brace');

        return $this;
    }

    public function limit($limit = 1) {
        $this->append('limit', ['limit' => $limit]);

        return $this;
    }

    public function offset($offset = 0) {
        $this->append('offset', ['offset' => $offset]);

        return $this;
    }

    public function join($table, $type = 'inner', $alias = null) {
        $this->append(
            'join', [
                'table' => $table,
                'type'  => $type,
                'alias' => $alias,
            ]
        );

        return $this;
    }

    public function using($field) {
        $this->append('using', ['field' => $field]);

        return $this;
    }

    public function on() {
        $this->append('on');

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
        $this->append('group');
        if ($field !== null) {
            $this->append('field', ['identifier' => $field]);
            $this->append('close');
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
        $this->append('order');

        return $this;
    }

    /**
     * Adds parameterized raw sql
     *
     * @param       $raw
     * @param array $params
     * @return $this
     */
    public function raw($raw, array $params = []) {
        $this->append('raw', ['raw' => $raw, 'params' => $params]);

        return $this;
    }

    /**
     * If the condition is true, the following types will be added<br />
     * If not, until the next _end() or _else() nothing will be added to the
     * query<br />
     * <br />
     * If you give a closure as $condition, the result of the function call is
     * used
     *
     * @param bool|\Closure $condition the condition to validate
     * @return $this
     */
    public function _if($condition) {
        if ($condition instanceof \Closure && $this->allowAppend()) {
            $this->usedClosures = true;
            $condition = $condition();
        }
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
        $this->stopPropagation[$maxIndex] =
            !($this->stopPropagation[$maxIndex]);

        return $this;
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
        $this->append('order');
        $idx = 0;
        foreach ($orders as $field => $direction) {
            if (is_numeric($field)) {
                $this->append(
                    'orderby', [
                        'field'     => $direction,
                        'direction' => 'desc',
                    ]
                );
            } else {
                $this->append(
                    'orderby', [
                        'field'     => $field,
                        'direction' => $direction,
                    ]
                );
            }
            if (++$idx < count($orders)) {
                $this->append('comma');
            }
        }
        $this->append('close');

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
        $this->append('orderby', ['field' => $field, 'direction' => $order]);

        return $this;
    }

    public function connect($relation = '&') {
        switch (strtolower($relation)) {
            case 'and' :
            case '&' :
            case '&&' :
            $this->append('and');

                return $this;
            case 'or' :
            case '|' :
            case '||' :
            $this->append('or');

                return $this;
        }
        throw new \Exception("unknown connection type: " . $relation);
    }

    public function c() {
        $this->append('comma');

        return $this;
    }

    /**
     * Appends a custom type
     *
     * @param string $type   the type name
     * @param array  $params the params
     * @return $this
     * @throws MissingParameterException
     * @throws TypeNotFoundException
     */
    public function custom($type, array $params = []) {
        $this->append($type, $params);

        return $this;
    }

    /**
     * Adds a new LIKE statement<br />
     * <br />
     * If you give a closure as $pattern, the result of the function call is
     * used
     *
     * @param mixed|\Closure $pattern
     * @return $this
     */
    public function like($pattern) {
        $this->append('like', ['pattern' => $pattern]);

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
     * @param ParserInterface $parser
     * @return SqlQuery
     * @throws MalformedQueryException if the query is (probably) malformed
     * @throws SystemException if no parser is available
     */
    public function getSqlQuery(ParserInterface $parser = null) {
        if ($this->parser === null && $parser === null) {
            throw new SystemException('No parser given');
        }

        if ($this->getHighestPropagationKey() !== null) {
            throw new MalformedQueryException('Probable bug: not every if ended with ::_end()');
        }

        // try to use cache
        $data = false;
        if ($this->isCacheAvailable()) {
            $data = $this->validateCache($this->statement);
        }
        if (isset($data['statement']) && isset($data['query'])) {
            $this->statement = $data['statement'];
            $query = $data['query'];
        }

        if ($parser === null) {
            $parser = $this->parser;
        }
        if (!isset($query)) {
            $parser->setStatement($this->statement);
            $parser->execute();
            $query = new SqlQuery(
                $parser->getSqlQuery(),
                $parser->getParameters()
            );
        }

        if ($this->isCacheAvailable()) {
            $this->cacheItem(
                $this->getHash($this->statement), $this->statement,
                $query
            );
        }
        $this->clear();

        return $query;
    }

    /**
     * Validates whether cache is available
     *
     * @return bool
     */
    private function isCacheAvailable() {
        if ($this->usedClosures) {
            return false;
        }

        return true;
    }

    private function validateCache(array $statement) {
        if (!is_object($this->cache)) {
            return false;
        }
        $hash = $this->getHash($statement);
        if ($this->cache->contains($hash)) {
            $data = $this->cache->fetch($hash);

            return unserialize($data);
        }

        return false;
    }

    private function getHash(array $statement) {
        return md5(serialize($statement));
    }

    private function cacheItem($hash, array $statement, SqlQuery $Query) {
        if (is_object($this->cache)) {
            $this->cache->save(
                $hash, serialize(
                [
                    'statement' => $statement,
                    'query'     => $Query,
                ]
            ), 3600
            );
        }
    }

    /**
     * Clear the class
     */
    private function clear() {
        $this->stopPropagation = [];
        $this->statement = [];
        $this->usedClosures = false;
    }
}
