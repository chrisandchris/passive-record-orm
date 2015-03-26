<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser;

use Klit\Common\RowMapperBundle\Services\Query\Type\ParameterizedTypeInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name DefaultParser
 * @version 1.0.0-dev
 * @since v2.0.0
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class DefaultParser implements ParserInterface {
    /**
     * The namespace where the snippets are located
     *
     * @var string
     */
    private $namespace ;
    /**
     * The statement array
     *
     * @var array
     */
    private $statement;
    /**
     * The generated query
     *
     * @var string
     */
    private $query = '';
    /**
     * The suffix of the snippet classes
     *
     * @var string
     */
    private $suffix = 'Snippet';
    /**
     * An array of open braces
     *
     * @var array
     */
    private $braces = [];
    /**
     * An ordered array of parameters used in the query
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Initialize class
     *
     * @param string $namespace the namespace of the parser snippets
     */
    function __construct($namespace = 'Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL\\') {
        $this->namespace = $namespace;
    }


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

    /**
     * Parses the code
     *
     * @param string $code the snippet code to parse
     * @param TypeInterface $type the type interface to use
     * @param SnippetInterface $snippet the snippet interface to use
     * @return string the generated query
     * @throws \Exception
     */
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

    /**
     * Gets an instance of a snippet
     *
     * @param string $type the snippet name
     * @return SnippetInterface the snippet instnace
     * @throws \Exception
     */
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

    /**
     * Run the parser
     *
     * @throws \Exception
     */
    public function execute() {
        $this->clear();
        /** @var TypeInterface $type */
        foreach ($this->statement as $type) {
            $Snippet = $this->getSnippet($type);
            $code = $Snippet->getCode();
            $this->query .= $this->parseCode($code, $type, $Snippet);
        }
    }

    /**
     * Add a used parameter
     * @param $parameter
     */
    private function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }

    /**
     * Clear and prepare builder for next query
     */
    private function clear() {
        $this->parameters = [];
        $this->query = '';
        $this->braces = [];
    }
}
