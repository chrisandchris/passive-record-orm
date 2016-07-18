<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\SecurityBreachException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\SystemException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TypeNotFoundException;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionExecutorInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\ParserInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;

/**
 * @name Builder
 * @version    1.1.0
 * @lastChange v2.1.0
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class Builder {

    /** @var array the statement */
    private $statement = [];
    /** @var ParserInterface */
    private $parser;
    /** @var array an array, which handles the if/else-statements */
    private $stopPropagation = [];
    /** @var bool indicator, if the current query uses closures */
    private $usedClosures = false;
    /** @var TypeBag */
    private $typeBag;
    /** @var EncryptionExecutorInterface the encryption service used */
    private $encryptionExecutor;

    function __construct(ParserInterface $parser, TypeBag $parameterBag) {
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
                        !array_key_exists($param, $params)
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

    public function alias($alias) {
        $this->append('alias', ['alias' => $alias]);

        return $this;
    }

    public function any() {
        $this->append('any');

        return $this;
    }

    public function update($table) {
        $this->append('update', ['table' => $table]);

        return $this;
    }

    /**
     * Simplifies updating of columns
     *
     * @param array $updates the updates to append
     * @return $this
     * @throws MalformedQueryException
     */
    public function updates(array $updates) {
        if (count($updates) < 1) {
            throw new MalformedQueryException(
                sprintf('Must update at least one field, %s given', count($updates))
            );
        }
        $insertCounter = 0;
        foreach ($updates as $update) {
            if (!is_array($updates)) {
                throw new MalformedQueryException(
                    sprintf('Value of $values must be array, %s given', gettype($update))
                );
            }
            if (count($update) != 2) {
                throw new MalformedQueryException(
                    sprintf('Update value must have 2 indexes, %d given', count($update))
                );
            }
            $keys = array_keys($update);
            $this->field($update[$keys[0]])
                 ->equals()
                 ->value($update[$keys[1]]);

            if (++$insertCounter < count($updates)) {
                $this->c();
            }
        }

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

    public function equals() {
        $this->append('equals');

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

    public function c() {
        $this->append('comma');

        return $this;
    }

    /**
     * @param $castTo
     * @return $this
     */
    public function cast($castTo)
    {
        $this->append('cast', ['cast' => $castTo]);

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
     * @param array $fields            the fields to add
     * @param bool  $encloseWithBraces if set to true, enclose with braces
     * @return $this
     */
    public function fieldlist(array $fields, $encloseWithBraces = false) {
        if ($encloseWithBraces) {
            $this->brace();
        }
        $this->append('fieldlist', ['fields' => $fields]);
        if ($encloseWithBraces) {
            $this->close();
        }

        return $this;
    }

    public function brace() {
        $this->append('brace');

        return $this;
    }

    public function close() {
        $this->append('close');

        return $this;
    }

    public function where() {
        $this->append('where');

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

    public function compare($comparison) {
        $this->append('comparison', ['comparison' => $comparison]);

        return $this;
    }

    /**
     * Adds a new VALUES()-Statement
     *
     * @param array|null $values the values to append
     * @return $this
     * @throws MalformedQueryException
     */
    public function values(array $values = null) {
        if (count($values) > 0) {
            $this->append('values');

            $insertCounter = 0;
            foreach ($values as $insert) {
                if (!is_array($insert) || count($insert) < 1) {
                    throw new MalformedQueryException(
                        sprintf('Value of $values must be array, %s given', gettype($insert))
                    );
                }
                $this->brace();
                $fieldCounter = 0;
                foreach ($insert as $value) {
                    $this->value($value);

                    if (++$fieldCounter < count($insert)) {
                        $this->c();
                    }
                }
                $this->close();

                if (++$insertCounter < count($values)) {
                    $this->c();
                }
            }

            return $this;
        }
        $this->append('values');

        return $this;
    }

    /**
     * Add a new raw value to the statement<br>
     * The value gets encrypted if an encryption service is set
     *
     * @param mixed|\Closure $value
     * @return $this
     * @throws SecurityBreachException if no encryption service is set
     */
    public function encryptedValue($value) {
        $this->append('value', ['value' => $this->encrypt($value)]);

        return $this;
    }

    /**
     * Encrypts the given input
     *
     * @param mixed|\Closure $value the value to encrypt
     * @return string the encrypted value
     * @throws SecurityBreachException if no executor is set
     */
    private function encrypt($value) {
        if ($this->encryptionExecutor === null) {
            throw new SecurityBreachException('No encryption executor is set');
        }
        if ($value instanceof \Closure) {
            $value = $value();
        }

        return $this->encryptionExecutor->encrypt($value);
    }

    /**
     * Append a NULL
     *
     * @return $this
     */
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

    /**
     * Add comparison to "IS NULL" if $isNull is true<br />
     * or to "IS NOT NULL" if $isNull is false
     *
     * @param bool $isNull
     * @return $this
     */
    public function isNull($isNull = true) {
        if ($isNull) {
            $this->append('isnull', ['isnull' => true]);
        } else {
            $this->append('isnull', ['isNull' => false]);
        }

        return $this;
    }

    /**
     * Limit the length of the result set
     *
     * @param int $limit the maximal amount of rows
     * @return $this
     */
    public function limit($limit = 1) {
        $this->append('limit', ['limit' => $limit]);

        return $this;
    }

    /**
     * Set an offset for the query
     *
     * @param int $offset the offset
     * @return $this
     */
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

    public function union($mode = '') {
        $this->append(
            'union', [
                'mode' => $mode,
            ]
        );
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
     * Append the result of $callable() as long as $validator() equals to
     * true<br>
     * <br>
     * $callable() may return a list of types as array or an instance of the
     * Builder class
     *
     * @param \Closure $validator the validator to use
     * @param \Closure $callable  the callable to execute on each turn
     * @throws MalformedQueryException
     */
    public function asLong(\Closure $validator, \Closure $callable) {
        while ($validator() === true) {
            $this->appendMultiple($callable());
        }
    }

    /**
     * Append multiple types using the internal ::append() method
     *
     * @param array|Builder $types     the types to append
     * @throws MalformedQueryException if the parameters are in a not support
     *                                 format
     * @throws MissingParameterException if parameters of types are missing
     * @throws TypeNotFoundException if a type is not found
     */
    private function appendMultiple($types) {
        if ($types instanceof Builder) {
            $types = $types->getStatement();
        } else {
            if (!is_array($types)) {
                throw new MalformedQueryException(
                    'When adding multiple types, you must give an array or an instance of Builder'
                );
            }
        }
        foreach ($types as $type) {
            if (!isset($type['type']) || !isseT($type['params'])) {
                throw new MalformedQueryException('Type not fully configured, missing type name or params, have');
            }
            $this->append($type['type'], $type['params']);
        }
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
     * Combines an existing builder by appending it to the end of this builder
     *
     * @param Builder|\Closure $builder
     * @return $this
     * @throws MalformedQueryException
     */
    public function combine($builder)
    {
        if (!$this->allowAppend()) {
            return $this;
        }

        if ($builder instanceof Builder) {
            $this->appendMultiple($builder);
        } else {
            if ($builder instanceof \Closure) {
                $this->appendMultiple(
                    $builder()
                );
            } else {
                throw new MalformedQueryException(sprintf(
                    'Combine expects builder or closure, "%s" given',
                    gettype($builder)
                ));
            }
        }

        return $this;
    }

    public function each(array $items, \Closure $callable) {
        $count = 0;
        foreach ($items as $item) {
            $count++;
            $this->appendMultiple($callable($item, $count < count($items)));
        }

        return $this;
    }

    /**
     * Get the SqlQuery object
     *
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
        $this->clear();

        return $query;
    }

    /**
     * Clear the class
     */
    private function clear() {
        $this->stopPropagation = [];
        $this->statement = [];
        $this->usedClosures = false;
    }

    /**
     * Use this encryption service if encryption is required for a field
     *
     * @param EncryptionExecutorInterface $executorService
     * @return $this
     */
    public function useEncryptionService(EncryptionExecutorInterface $executorService) {
        $this->encryptionExecutor = $executorService;

        return $this;
    }
}
