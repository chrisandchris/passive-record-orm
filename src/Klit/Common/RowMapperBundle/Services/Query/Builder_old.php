<?php
namespace Klit\Common\RowMapperBundle\Services\Query;
/**
 * @name Builder
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class Builder_old {
    /**
     * Contains the statement as array
     * @var array
     */
    protected $statement = array();
    /**
     * Contains the result object
     * @var object
     */
    protected $result = null;
    /**
     * Contains the fetched tableinfo
     * @var array
     */
    protected $info = array();
    /**
     * Contains the already called function
     * @var array
     */
    protected $marker;
    /**
     * Contains the actual table in the statement
     * @var string
     */
    protected $activeTable = null;

    /**
     * Contains the functions allowed to be called through __call()
     * @var array
     */
    protected $allowedCall = array(
        'select',
        'update',
        'delete',
        'insert',
        'table',
        'primary',
        'null',
        'fields',
        'values',
        'where',
        'in',
        'join',
        'using',
        'on',
        'order',
        'limit',
        'execute',
        'groupby'
    );
    /**
     * Contains allowed relations in the WHERE-statement
     * @var array
     */
    protected $allowedRelations = array('not', '!', 'or', '||', 'and', '&&', 'XOR');
    /**
     * Contains the allowed operators in the WHERE-statement
     * @var array
     */
    protected $allowedOperators = array('=', '<=>', '<>', '!=', '<=', '<', '>=', '>',
                                        'IS', 'IS NOT', 'IS NULL', 'IS NOT NULL');
    /**
     * Contains the allowed orders in the ORDER-statement
     * @var array
     */
    protected $allowedOrder = array('ASC', 'DESC', 'asc', 'desc', 'a', 'd', '<', '>');
    /**
     * Contains arrays of afterwards callable functions
     * @var array
     */
    protected $allowedAfterward = array(
        'select' =>
            array('table'),
        'update' =>
            array('table'),
        'delete' =>
            array('table'),
        'insert' =>
            array('table'),
        'table' =>
            array('join', 'values', 'fields', 'primary', 'null', 'where', 'in'),
        'primary' =>
            array('fields', 'join', 'where', 'using', 'order', 'limit', 'execute', 'groupby'),
        'null' =>
            array('join', 'where', 'using', 'order', 'limit', 'execute', 'in', 'groupby'),
        'fields' =>
            array('values', 'join', 'where', 'using', 'order', 'limit', 'execute', 'in', 'groupby'),
        'values' =>
            array('where', 'order', 'limit', 'execute', 'in'),
        'where' =>
            array('where', 'order', 'limit', 'execute', 'in', 'groupby'),
        'groupby' =>
            array('where', 'order', 'limit', 'execute', 'in'),
        'in' =>
            array('where', 'in', 'order', 'limit', 'execute'),
        'join' =>
            array('using', 'on'),
        'using' =>
            array('where', 'order', 'limit', 'execute', 'in', 'groupby', 'join'),
        'on' =>
            array('where', 'order', 'limit', 'execute', 'in', 'groupby', 'join'),
        'order' =>
            array('limit', 'execute', 'order'),
    );

    /**
     * Temporary: Field list
     * @var mixed
     */
    protected $tmp_fields;

    /**
     * Initializes the Query class
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $db
     * @return (null)
     * @throws \Core\Mexception
     */
    public function __construct() {
        $this->Database = \Lib\Database\MySQL::getInstance();
        return ;
    }

    public function __destruct() {
        return ;
    }

    /**
     * Handles all access to this class
     *
     * @param function name $name
     * @param list of arguments $argv
     * @return \Model
     * @throws \Core\Mexception
     */
    protected function _validateCall($name) {
        if (in_array($name, $this->allowedCall, true)) {
            if (count($this->marker) > 0) {
                if (!in_array($name, $this->allowedAfterward[end($this->marker)])) {
                    throw new \Core\Mexception('Function '.$name.' is not allowed to be called here');
                }
            }
            $this->_setMarker($name);
            return true;
        }
        throw new \Core\Mexception('Function '.$name.' is not allowed to access');
    }

    /**
     *
     *
     * ************************************************
     * ************* SQL METHODS           ************
     * ************************************************
     *
     *
     */

    /**
     * Adds an element to the Statement-Array
     *
     * @param string $type
     * @param mixed $value
     * @return boolean
     */
    protected function _addElement($type, $value) {
        $this->statement[] = array('type'=>$type, 'value'=>$value);
        return true;
    }

    /**
     * Fetchs the tableinfo into $this->info
     *
     * @param string $table
     * @return boolean
     * @throws \Core\Mexception
     */
    protected function _fetchTableinfo($table) {
        $result = $this->Database->query('SHOW COLUMNS FROM `'.$this->_ensureString($table).'`;');
        if (!is_object($result)) {
            throw new \Core\Mexception('Fetch tableinfo of table '.$table.' failed');
        }
        while ($row = $result->fetch_assoc()) {
            if ($row['Null'] == 'YES') {
                $row['Null'] = true;
            } else {
                $row['Null'] = false;
            }
            $this->info[$row['Field']] = array('type' => $row['Type'], 'null' => $row['Null']);
        }
        return true;
    }

    /**
     * Fetchs a field name to the active table
     *
     * @param string $field
     * @return string
     * @throws \Core\Mexception
     */
    protected function _fetchField($field) {
        if (isset($this->info[$this->activeTable.'_'.$field])) {
            return $this->activeTable.'_'.$field;
        }
        throw new \Core\Mexception('Unknown field');
    }

    /**
     * Checks wheter $field is a valid field of the active table
     *
     * @param string $field
     * @return boolean
     */
    protected function _isField($field) {
        if (isset($this->info[$field])) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * ************************************************
     * ************* MARKER METHODS        ************
     * ************************************************
     *
     *
     */

    /**
     * Resets the object to be ready to create a new statement
     *
     * @return boolean
     */
    protected function _reset() {
        $this->marker = array();
        $this->result = null;
        $this->statement = array();
        return true;
    }

    /**
     * Sets a marker on $function
     *
     * @param string $function
     * @return boolean
     */
    protected function _setMarker($function) {
        $this->marker[] = $function;
        return true;
    }

    /**
     * Checks wheter the marker of $function is set or not
     *
     * @param string $function
     * @return boolean
     */
    protected function _callMarker($function) {
        if (in_array($function, $this->marker)) {
            return true;
        }
        return false;
    }

    /**
     * Validates wheter all marker in $needed are called
     *
     * @param array $needed
     * @return boolean
     */
    protected function _validateMarker(array $needed) {
        foreach ($needed AS $value) {
            if (!in_array($value, $this->marker)) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     *
     * ************************************************
     * ************* SQL METHODS           ************
     * ************************************************
     *
     *
     */

    /**
     * Get the object result or in case of failure returns boolean
     *
     * @return boolean
     */
    public function getResult() {
        if (is_object($this->result)) {
            return $this->result;
        } elseif ($this->Database->errno == 0) {
            return true;
        }
        return false;
    }

    /**
     * Get the error number of the last query or in case of success return false
     *
     * @return boolean
     */
    public function getError() {
        if ($this->Database->errno != 0) {
            return $this->Database->errno;
        }
        return false;
    }

    /**
     *
     *
     * ************************************************
     * ************* TRANSACTION METHODS   ************
     * ************************************************
     *
     *
     */

    /**
     * Opens a new transaction
     *
     * @return boolean
     */
    public function beginTransaction() {
        $this->Database->query('START TRANSACTION;');
        if ($this->Database->errno) {
            return false;
        }
        return true;
    }

    /**
     * Commits the transaction
     *
     * @return boolean
     */
    public function commit() {
        $this->Database->query('COMMIT;');
        if ($this->Database->errno != 0) {
            return false;
        }
        return true;
    }

    /**
     * Rollback to $savepoint or beginning
     * @param string $savepoint
     * @return boolean
     */
    public function rollback($savepoint = null) {
        if ($savepoint == null) {
            $this->Database->query('ROLLBACK;');
        } else {
            if (!preg_match('/([\w]+)/', $savepoint)) {
                return false;
            }
            $this->Database->query('ROLLBACK TO '.$savepoint.';');
            if ($this->Database->errno != 0) {
                return false;
            }
        }
        if ($this->Database->errno != 0) {
            return false;
        }
        return true;
    }

    /**
     * Create savepoint
     * @param string $name
     * @return boolean
     */
    public function savepoint($name) {
        if (!preg_match('/([\w]+)/', $name)) {
            return false;
        }
        $this->Database->query('SAVEPOINT '.$name);
        if ($this->Database->errno != 0) {
            return false;
        }
        return true;

    }

    /**
     *
     *
     * ************************************************
     * ************* STATEMENT METHOD      ************
     * ************************************************
     *
     *
     */

    /**
     * Initializes an Select-Query
     *
     * @return boolean
     */
    public function select() {
        $this->_validateCall('select');
        $this->_reset();
        $this->_addElement('type', 'select');
        return $this;
    }

    /**
     * Initializes an Update-Query
     *
     * @return boolean
     */
    public function update() {
        $this->_validateCall('update');
        $this->_reset();
        $this->_addElement('type', 'update');
        return $this;
    }

    /**
     * Initializes an Delete-Query
     *
     * @return boolean
     */
    public function delete() {
        $this->_validateCall('delete');
        $this->_reset();
        $this->_addElement('type', 'delete');
        return $this;
    }

    /**
     * Initializes an Insert-Query
     *
     * @return boolean
     */
    public function insert() {
        $this->_validateCall('insert');
        $this->_reset();
        $this->_addElement('type', 'insert');
        return $this;
    }

    /**
     * Initializes an join
     *
     * @param array $argv
     * @return boolean
     * @throws \Core\Mexception
     */
    public function join($table, $type = 'inner') {
        $this->_validateCall('join');
        if ($type != 'left' AND $type != 'right' AND $type != 'inner')
            throw new \Core\Mexception('Unknown join condition');
        $this->activeTable = $table;
        $this->_fetchTableinfo($table);
        $this->_addElement('join',  array('table'=>$table, 'type'=>$type));
        return $this;
    }

    /**
     * Adds an using() after the join
     *
     * @param array $argv
     * @return boolean
     * @throws \Core\Mexception
     */
    public function using($using) {
        $this->_validateCall('using');
        if (!isset($this->info[$using])) {
            throw new \Core\Mexception('Unknown column');
        }
        $this->_addElement('using', $using);
        return $this;
    }

    public function on($left, $right) {
        $this->_validateCall('on');
        if (!isset($this->info[$left]) OR !isset($this->info[$right])) {
            throw new \Core\Mexception('Unknown column');
        }
        $this->_addElement('on', array($left, $right));
        return $this;
    }

    /**
     * Adds a table to the actual query
     *
     * @param array $argv
     * @return boolean
     */
    public function table($table) {
        $this->_validateCall('table');
        $this->_addElement('table', $table);
        $this->activeTable = $table;
        $this->_fetchTableinfo($table);
        return $this;
    }

    /**
     * Simple way to select with a given primaray-key
     * Adds in one the field and the where condition onto the query
     *
     * @param array $argv
     * @return boolean
     */
    public function primary($onId) {
        $this->_validateCall('primary');
        $this->fields($this->_fetchField('id'));
        $this->where($this->_fetchField('id'), '=', $onId);
        $this->_setMarker('fields');
        $this->_setMarker('where');
        return $this;
    }

    /**
     * Selects simply a 1, no other fields (check wheter a row exists or not)
     * Maybe alias of calling fields(array('1'))
     *
     * @param array $argv
     */
    public function null() {
        $this->_validateCall('null');
        $this->_setMarker('fields');
        $this->_addElement('fields',  1);
        return $this;
    }

    /**
     * Selects the given fields
     * If $argv[0] is null, there will be all fields selected
     *
     * @param array $argv
     * @return boolean
     */
    public function fields($fields) {
        $this->_validateCall('fields');
        if (is_array($fields) AND count($fields) > 0) {
            $this->_addElement('fields', $fields);
        } elseif ($fields === null) {
            $this->_addElement('fields', null);
        } else {
            $this->_addElement('fields', array($fields));
        }
        return $this;
    }

    /**
     * Initializes a VALUE-Statement
     *
     * @param array $argv
     * @return boolean
     */
    public function values($values) {
        $this->_validateCall('values');
        if (is_array($values) > 0 AND count($values) > 0) {
            $this->_addElement('values', $values);
        } else {
            $this->_addElement('values', array($values));
        }
        return $this;
    }

    /**
     * Adds a where condition
     * Give parameters in following order (not as array)
     *  1. Field of table
     *  2. Operator (e.g. =, <=, ...)
     *  3. Condition (the value the field has to be)
     *  4. Relation related to the previous element
     *
     * @param array $argv
     * @return boolean
     */
    public function where($onField, $operator = null, $comparisonValue = null, $relationToBefore = null) {
        $this->_validateCall('where');

        if ($onField === null) {
            $this->allowedAfterwardOnly = 'in';
            return $this;
        }

        //                                       field     operator  comp.vl.  relation
        // $this->statement[]['where'][] = array($argv[0], $argv[1], $argv[2], $argv[3]);

        if (!in_array($operator, $this->allowedOperators))   return false;
        if (!$this->_isField($onField))                      return false;

        // set a relation (like and, or, ...)
        if ($relationToBefore !== null) {
            if (!in_array(strtolower($relationToBefore), $this->allowedRelations)) {
                return false;
            }
        }
        $this->_addElement('where', array($onField, $operator, $comparisonValue, $relationToBefore, null));
        return $this;
    }

    public function in($onField, array $arrayOfIn, $negate = false, $relationToBefore = null) {
        $this->_validateCall('in');
        if (!$this->_isField($onField)) return false;

        $this->_addElement('where', array($onField, $negate, null, $relationToBefore, $arrayOfIn));
        return $this;
    }

    public function groupby($groupby) {
        $this->_validateCall('groupby');
        if (!$this->_isField($groupby)) return false;

        $this->_addElement('groupby', $groupby);
        return $this;
    }

    /**
     * Adds an Order-Statement
     * Give parameters in following order (not as array)
     *  1. Field of table
     *  2. Order (e.g. asc, desc, <, ...)
     *
     * @param array $argv
     * @return boolean
     * @throws \Core\Mexception
     */
    public function order($onField, $order = 'asc') {
        $this->_validateCall('order');
        if (in_array($order, $this->allowedOrder) AND isset($this->info[$onField])) {
            $this->_addElement('order', array($onField, $order));
        } else {
            throw new \Core\Mexception('Unknown order type/Unknown column');
        }
        return $this;
    }

    /**
     * Adds a limit condition
     * You could give following parameters:
     *  - Simple int for selecting only (int) rows
     *  - Two parameters as LIMIT (int),(int)
     *
     * @param array $argv
     * @return boolean
     */
    public function limit($limit, $offset = null) {
        $this->_validateCall('limit');
        if (!is_int($limit) OR ($offset !== null AND !is_int($offset))) return false;
        if ($offset !== null) {
            $this->_addElement('limit', array(intval($limit), intval($offset)));
            return $this;
        } else {
            $this->_addElement('limit', intval($limit));
            return $this;
        }
    }

    /**
     *
     *
     * ************************************************
     * ************* PARSE METHODS         ************
     * ************************************************
     *
     *
     */
}
