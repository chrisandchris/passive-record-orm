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
        return trim($this->query);
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
                foreach ($match as $method) {
                    if (mb_strstr($method, '#') === false) {
                        if (method_exists($snippet, $method) || is_callable(array($snippet, '__call'))) {
                            $code = str_replace('#' . $method, $snippet->{$method}($type), $code);
                        }
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
        return $code . ' ';
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
        $this->clear();
        /** @var TypeInterface $type */
        foreach ($this->statement as $type) {
            $Snippet = $this->getSnippet($type);
            $code = $Snippet->getCode();
            $this->query .= $this->parseCode($code, $type, $Snippet);
        }
    }

    private function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }

    private function clear() {
        $this->parameters = [];
        $this->query = '';
        $this->braces = [];
    }
}
