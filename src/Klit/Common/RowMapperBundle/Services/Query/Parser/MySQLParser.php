<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser;

use Klit\Common\RowMapperBundle\Services\Query\Type\ParameterizedTypeInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name MySQLParser
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class MySQLParser implements ParserInterface {
    private $namespace = 'Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL\\';

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

    private $statement = null;
    private $query = '';
    private $suffix = 'Snippet';
    private $braces = [];
    private $parameters = [];

    /**
     * Set the query information
     *
     * @param array $statement
     */
    function setStatement(array $statement) {
        $this->statement = $statement;
    }

    /**
     * Get the parsed statement
     *
     * @return string
     */
    function getSqlQuery() {
        return $this->query;
    }

    /**
     * Get the parameters array
     *
     * @return array
     */
    function getParameters() {
        return $this->parameters;
    }


    private function parseCode($code, TypeInterface $type, SnippetInterface $snippet) {
        // check if it's a close
        if ($code == '/@close') {
            // put members down
            $this->query =
                $this->braces[max(array_keys($this->braces))]['query']
                . $this->braces[max(array_keys($this->braces))]['before']
                . $this->query
                . $this->braces[max(array_keys($this->braces))]['after']
            ;
            $code = '';
            unset($this->braces[max(array_keys($this->braces))]);
        }
        // collect methods
        $match = preg_match_all('/#([a-zA-Z]+)/', $code, $matches);
        if ($match > 0) {
            foreach ($matches as $match) {
                if (mb_strstr($match[0], '#') === false) {
                    if (method_exists($snippet, $match[0])) {
                        $code = str_replace('#' . $match[0], $snippet->{$match[0]}($type), $code);
                    }
                }
            }
        }
        // detect parameters
        $offset = 0;
        $idx = 0;
        while (false !== ($pos = mb_strpos($code, '?', $offset))) {
            $offset = $pos + 1;
            if (!($type instanceof ParameterizedTypeInterface)) {
                throw new \Exception("Type must be parameterized to use parameters");
            }
            /** @var ParameterizedTypeInterface $type */
            $this->addParameter($type->getParameter($idx++));
        }
        // collect method chaining
        $match = preg_match('/(.*)\/@brace\(([a-z]+)\)(.*)/s', $code, $matches);
        if ($match > 0) {
            /*
             * $match:
             * 0        complete match
             * 1        before
             * 2        brace name
             * 3        after
             */
            $this->braces[] = array(
                'query' => $this->query,
                'before' => $matches[1],
                'after' => $matches[3],
                'key' => $matches[2]
            );
            // empty query
            $this->query = '';
            // empty code
            $code = '';
        }
        return $code . "\n\t";
    }

    public function getSnippet($type) {
        /** @var TypeInterface $type */
        $class = $this->namespace . ucfirst($type->getTypeName()) . $this->suffix;
        if (!class_exists($class)) {
            throw new \Exception("Unable to parse this statement, class not found: " . $class);
        }
        /** @var SnippetInterface $Snippet */
        $Snippet = new $class;
        $Snippet->setType($type);
        return $Snippet;
    }

    public function execute() {
        /** @var TypeInterface $type */
        foreach ($this->statement as $type) {
            $Snippet = $this->getSnippet($type);
            $code = $Snippet->getCode();
            $this->query .= $this->parseCode($code, $type, $Snippet);
        }

        return null;

        $type = null; $table = null; $fields = null; $values = null; $limit = null; $order = null;
        $join = null; $using = null; $where = null; $on = null; $groupby = null;
        // Parse query
        foreach ($this->statement AS $value) {
            switch ($value['type']) {
                case 'type' :
                    $type = $this->parseType($value['value']);
                    break;
                case 'table' :
                    $table = $this->parseTable($value['value']);
                    break;
                case 'fields' :
                    $fields = $this->parseFields($value['value'], $type);
                    break;
                case 'values' :
                    $values = $this->parseValues($value['value'], $type);
                    break;
                case 'where' :
                    $where[] = $this->parseWhere($value['value']);
                    break;
                case 'order' :
                    $order[] = $this->parseOrder($value['value']);
                    break;
                case 'limit' :
                    $limit = $this->parseLimit($value['value']);
                    break;
                case 'join' :
                    $join[] = $this->parseJoin($value['value']);
                    break;
                case 'using' :
                    $using[] = $this->parseUsing($value['value']);
                    break;
                case 'on' :
                    $on[] = $this->parseOn($value['value']);
                    break;
                case 'groupby' :
                    $groupby = $this->parseGroupBy($value['value']);
                    break;
                default :
                    throw new \Exception('Unknown Type '.$value['type']);
            }
        }

        // Get where conditions
        if (is_array($where)) {
            $tmp = '';
            foreach ($where AS $value) {
                $tmp .= $value;
            }
            $where = $tmp;
            unset($tmp);
        }

        // build joins
        if (is_array($join) AND is_array($using)) {
            $join = array_combine($join, $using);
            $tmp = '';
            foreach ($join AS $key=>$value) {
                $tmp .= $key.' '.$value;
            }
            $join = $tmp;
            unset($tmp);
        }
        /**
         * @todo FIX ME PLEASE!
        if (is_array($join) AND is_array($on)) {
        $join = array_combine($join, $on);
        $tmp = '';
        foreach ($join AS $key=> $value) {
        $tmp .= $key . ' ' . $value;
        }
        $join = $tmp;
        unset($tmp);
        }
         * */
        // parse order
        if (is_array($order)) {
            $tmp = 'ORDER BY ';
            $order = implode(', ', $order);
            $order = $tmp . $order;
            unset($tmp);
        }

        if ($type == 'SELECT') {
            $neededMarker = array('fields', 'table');
            if (!$this->_validateMarker($neededMarker)) {
                throw new \Exception('Statement not complete');
            }

            // SELECT [fields]
            //      FROM [table]
            //      [[join] JOIN [table] USING([field])]
            //      [WHERE [conditions]]
            //      [ORDER BY [order]]
            //      [LIMIT [limit]]
            $query = "SELECT $fields FROM $table $join $where $groupby $order $limit ;";
        } elseif ($type == 'UPDATE') {
            $neededMarker = array('table', 'values', 'where');
            if (!$this->_validateMarker($neededMarker)) {
                throw new \Exception('Statement not complete');
            }

            // UPDATE [table]
            //      SET [[field] = [value] *]
            //      [WHERE [conditions]]
            //      [LIMIT [limit]]
            $query = "UPDATE $table SET $values $where $order $limit ;";
        } elseif ($type == 'DELETE') {
            $neededMarker = array('table', 'where', 'limit');
            if (!$this->_validateMarker($neededMarker)) {
                throw new \Exception('Statement not complete');
            }

            // DELETE FROM [table]
            //      [WHERE [conditions]]
            //      [ORDER BY [order]]
            //      [LIMIT [limit]]
            $query = "DELETE FROM $table $where $order $limit ;";
        } elseif ($type == 'INSERT') {
            $neededMarker = array('table', 'fields', 'values');
            if (!$this->_validateMarker($neededMarker)) {
                throw new \Exception('Statement not complete');
            }
            // INSERT INTO [table] ([fields])
            //      VALUES ([values])
            $query = "INSERT INTO $table ($fields) VALUES ($values) ;";
        }
        return $query;
    }

    /**
     * Parses the table
     *
     * @param string $table
     * @return string
     */
    protected function parseTable($table) {
        return '`'.$table.'`';
    }

    /**
     * Parses the fields
     *
     * @param array $fields
     * @param string $type
     * @return boolean|string
     */
    protected function parseFields($fields, $type) {
        /**
         * Possible values for $fields
         *
         * 1        You need to select simply a "1"
         * array    You have to parse a list of fields
         * null     You need to select all fields
         */
        if ($type == 'UPDATE') {
            $this->tmp_fields = $fields;
            return true;
        } else {
            $return = '';
            if (is_array($fields)) {
                foreach ($fields AS $value) {
                    if (strlen($return) == 0) {
                        $return = '`'.$value.'`';
                    } else {
                        $return .= ', '.'`'.$value.'`';
                    }
                }
            } elseif ($fields === null) {
                $return = '*';
            } else {
                $return = '1';
            }
            return $return;
        }
    }

    /**
     * Parses the values
     * If you have an update staement, fields will be integrated into query here
     *
     * @param string $values
     * @param string $type
     * @return string
     * @throws \Exception
     */
    protected function parseValues($values, $type) {
        if ($type == 'UPDATE') {
            $return = '';
            foreach ($this->tmp_fields AS $key=>$value) {
                if (!isset($this->info[$value]))
                    throw new \Exception('Unknown field');
                if (isset($values[$key])) {
                    if (strlen($return) == 0) {
                        $return = '`'.$value.'`'.' = '.$this->_notationByType(
                                $this->_ensureByType(
                                    $values[$key], $this->info[$value]['type']
                                ), $this->info[$value]['type']);
                    } else {
                        $return .= ', `'.$value.'`'.' = '.$this->_notationByType(
                                $this->_ensureByType(
                                    $values[$key], $this->info[$value]['type']
                                ), $this->info[$value]['type']);
                    }
                } else {
                    throw new \Exception('There is no value for a given field');
                }
            }
            return $return;
        } else {
            $return = '';
            if (is_array($values)) {
                foreach ($values AS $value) {
                    if (strlen($return) == 0) {
                        $return = "'".$value."'";
                    } else {
                        $return .= ", "."'".$value."'";
                    }
                }
            } elseif ($values === null) {
                $return = '*';
            } else {
                $return = '1';
            }
        }
        return $return;
    }

    /**
     * Parses a where-statement
     * where-statement will be saved as $this->tmp_where and will not be returned
     *
     * @param array $where
     * @return boolean
     * @throws \Exception
     */
    protected function parseWhere($where) {
        //                                       field     operator  condition relation  in()
        // $this->statement[]['where'][] = array($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]);

        // fetch where conditions
        if (strlen($where[3]) == 0 AND !is_array($where[4])) {
            // No relation
            return 'WHERE '.
            '`'.
            $where[0].
            '`'.
            ' '.
            $where[1].
            ' '.
            $this->_notationByType(
                $this->_ensureByType($where[2], $this->info[$where[0]]['type']),
                $this->info[$where[0]]['type']);
        } elseif (!is_array($where[4])) {
            // with relation
            return ' '.
            $where[3].
            ' '.
            '`'.
            $where[0].
            '`'.
            ' '.
            $where[1].
            ' '.
            $this->_notationByType(
                $this->_ensureByType($where[2], $this->info[$where[0]]['type']),
                $this->info[$where[0]]['type']);
        } else {
            // an in()
            if (strlen($where[3]) > 0) $in = ' '.$where[3];
            else $in = 'WHERE ';
            $in .= '`'.$where[0].'`';
            if ($where[1] == true) $in .= ' NOT ';
            $in .= ' IN(';
            $count = count($where[4]);
            foreach ($where[4] AS $value) {
                $in .= $this->_notationByType(
                    $this->_ensureByType($value, $this->info[$where[0]]['type']),
                    $this->info[$where[0]]['type']);
                if (--$count != 0) $in .= ', ';
            }
            $in .= ')';
            return $in;
        }
    }

    protected function parseGroupBy($groupBy) {
        return 'GROUP BY(`'.$groupBy.'`)';
    }

    /**
     * Parses the order-statement
     *
     * @param array $order
     * @return string
     */
    protected function parseOrder($order) {
        if ($order[1] == 'a' OR $order[1] == '<') $order[1] = 'ASC';
        if ($order[1] == 'd' OR $order[1] == '>') $order[1] = 'DESC';
        $return = '`'.$order[0].'` '.$order[1];
        return $return;
    }

    /**
     * Parses the limit-staement
     *
     * @param array|string $limit
     * @return string
     */
    protected function parseLimit($limit) {
        if (is_array($limit)) {
            return 'LIMIT '.$limit[0] .' OFFSET '.$limit[1];
        } else {
            return 'LIMIT '.$limit;
        }
    }

    /**
     * Parses the join-statement
     *
     * @param array $value
     * @return string
     */
    protected function parseJoin($value) {
        return (strtoupper($value['type']).' JOIN `'.$value['table'].'`');
    }

    /**
     * Parses the using-statement
     * @param string $using
     * @return string
     */
    protected function parseUsing($using) {
        return ('USING(`'.$using.'`)');
    }

    protected function parseOn($on) {
        return ('ON (`'.$on[0].'` = `'.$on[1].'`)');
    }

    protected function _ensureByType($value, $type) {
        try {
            $matches = array();
            if (!preg_match('$([a-zA-Z]+)\(([0-9]+)\)$', $type, $matches)) {
                if (!preg_match('$([a-zA-Z]+)$', $type, $matches)) {
                    return null;
                }
            }
            switch (strtolower($matches[1])) {
                case 'varchar' :
                    return $this->_ensureString($value);
                    break;
                case 'int' :
                case 'tinyint' :
                    return $this->_ensureInt($value);
                    break;
                case 'float' :
                    return $this->_ensureFloat($value);
                    break;
                case 'bool' :
                    return $this->_ensureBool($value);
                    break;
                case 'decimal' :
                    return $this->_ensureDecimal($value);
                    break;
                case 'datetime' :
                case 'date' :
                    return $this->_ensureDatetime($value);
                    break;
                default :
                    throw new \Exception('Unknown field type');
            }
        } catch (\Exception $e) {
            $e->quit($e->getMessage());
        }
    }

    /**
     * Makes a string secure
     *
     * @param string $string
     * @return string
     */
    protected function _ensureString($string) {
        return $this->Database->real_escape_string($string);
    }

    /**
     * Makes an intval secure
     *
     * @param int $int
     * @return int
     */
    protected function _ensureInt($int) {
        return intval($int);
    }

    /**
     * Makes a float secure
     *
     * @param float $float
     * @return float
     */
    protected function _ensureFloat($float) {
        return floatval($float);
    }

    /**
     * Makes a bool secure
     *
     * @param boolean $bool
     * @return boolean
     */
    protected function _ensureBool($bool) {
        if ($bool == 1 OR $bool == '1' OR $bool == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Makes a decimal value secure
     *
     * @param decimal $decimal
     * @return decimal
     */
    protected function _ensureDecimal($decimal) {
        return floatval($decimal);
    }

    /**
     * Makes a datetime secure
     *
     * @param datetime $date
     * @return datetime
     * @throws \Exception
     */
    protected function _ensureDatetime($date) {
        try {
            if (is_int(strtotime($date))) {
                return $date;
            } else {
                throw new \Exception('Invalid date/time format');
            }
        } catch (\Exception $e) {
            $e->quit($e->getMessage());
        }
    }

    /**
     * Notates $value to go into database
     * @param mixed $value
     * @param string $type
     * @return null|string
     */
    protected function _notationByType($value, $type) {
        $matches = array();
        if (!preg_match('$([a-zA-Z]+)\(([0-9]+)\)$', $type, $matches)) {
            if (!preg_match('$([a-zA-Z]+)$', $type, $matches)) {
                return null;
            }
        }
        switch (strtolower($matches[1])) {
            case 'varchar' :
                return '"'.$value.'"';
            case 'int' :
            case 'tinyint' :
                return $value;
            case 'float' :
                return $value;
            case 'bool' :
                if ($value) {
                    return 'TRUE';
                } else {
                    return 'FALSE';
                }
            case 'decimal' :
                return $value;
            case 'datetime' :
            case 'date' :
                return '"'.$value.'"';
            default :
                return null;
        }
    }

    protected function _validateMarker(array $needed) {
        foreach ($needed AS $value) {
            if (!in_array($value, $this->marker)) {
                return false;
            }
        }
        return true;
    }

    private function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }
}
