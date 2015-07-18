<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\ClassNotFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TypeInterfaceException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\ParameterizedTypeInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name DefaultParser
 * @version   1.1.1
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class DefaultParser implements ParserInterface {

    /**
     * The namespace where the snippets are located
     *
     * @var string
     */
    private $namespace;
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
    function __construct(
        $namespace = 'ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL\\') {
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
     * Run the parser
     *
     * @throws MalformedQueryException
     */
    public function execute() {
        $this->clear();
        /** @var TypeInterface $type */
        foreach ($this->statement as $type) {
            $snippet = $this->getSnippet($type);
            $code = $snippet->getCode();
            $this->query .= $this->parseCode($code, $type, $snippet);
        }

        if (count($this->braces) != 0) {
            throw new MalformedQueryException("There are still open braces.");
        }
    }

    /**
     * Clear and prepare builder for next query
     */
    private function clear() {
        $this->parameters = [];
        $this->query = '';
        $this->braces = [];
    }

    /**
     * Gets an instance of a snippet
     *
     * @param string $type the snippet name
     * @return SnippetInterface the snippet instance
     * @throws ClassNotFoundException
     */
    private function getSnippet($type) {
        /** @var TypeInterface $type */
        $class = $this->namespace.ucfirst($type->getTypeName()).$this->suffix;
        if (!class_exists($class)) {
            throw new ClassNotFoundException(
                "Unable to parse this statement, class not found: ".$class
            );
        }
        /** @var SnippetInterface $snippet */
        $snippet = new $class;
        $snippet->setType($type);

        return $snippet;
    }

    /**
     * Parses the code
     *
     * @param string           $code    the snippet code to parse
     * @param TypeInterface    $type    the type interface to use
     * @param SnippetInterface $snippet the snippet interface to use
     * @return string the generated query
     * @throws TypeInterfaceException
     */
    private function parseCode(
        $code,
        TypeInterface $type,
        SnippetInterface $snippet) {

        // check if it's a close
        if ($code == '/@close') {
            // put members down
            $code = $this->minimizeBrace();
        }
        $code = $this->runMethods($code, $type, $snippet);
        $this->checkForParameters($code, $type);
        $code = $this->checkForMethodChaining($code);

        return $code.' ';
    }

    /**
     * Closes a brace
     *
     * @return string
     * @throws MalformedQueryException
     */
    private function minimizeBrace() {
        if (count($this->braces) === 0) {
            throw new MalformedQueryException(
                'You must open braces before closing them.'
            );
        }
        $this->query =
            $this->braces[max(array_keys($this->braces))]['query']
            .$this->braces[max(array_keys($this->braces))]['before']
            .$this->query
            .$this->braces[max(array_keys($this->braces))]['after'];
        $code = '';
        unset($this->braces[max(array_keys($this->braces))]);

        return $code;
    }

    /**
     * Runs methods within the code
     *
     * @param                  $code
     * @param TypeInterface    $type
     * @param SnippetInterface $snippet
     * @return string
     */
    private function runMethods(
        $code,
        TypeInterface $type,
        SnippetInterface $snippet) {

        // collect methods
        $match = preg_match_all('/#([a-zA-Z]+)/', $code, $matches);
        if ($match > 0) {
            foreach ($matches as $match) {
                foreach ($match as $method) {
                    if (mb_strstr($method, '#') === false) {
                        if (method_exists($snippet, $method) ||
                            is_callable([$snippet, '__call'])
                        ) {
                            $code = str_replace(
                                '#'.$method,
                                $snippet->{$method}($type),
                                $code
                            );
                        }
                    }
                }
            }

            return $code;
        }

        return $code;
    }

    /**
     * Checks for parameters used in the code
     *
     * @param               $code
     * @param TypeInterface $type
     * @throws TypeInterfaceException
     */
    private function checkForParameters($code, TypeInterface $type) {
        // detect parameters
        $offset = 0;
        $idx = 0;
        while (false !== ($pos = mb_strpos($code, '?', $offset))) {
            $offset = $pos + 1;
            if (!($type instanceof ParameterizedTypeInterface)) {
                throw new TypeInterfaceException(
                    "Type must be parameterized to use parameters, use ParameterizedTypeInterface"
                );
            }
            /** @var ParameterizedTypeInterface $type */
            $this->addParameter($type->getParameter($idx++));
        }
    }

    /**
     * Add a used parameter
     *
     * @param $parameter
     */
    private function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }

    /**
     * Checks for method chains (braces)
     *
     * @param $code
     * @return string
     */
    private function checkForMethodChaining($code) {
        // collect method chaining
        $matches = [];
        $match = preg_match('/(.*)\/@brace\(([a-z]+)\)(.*)/s', $code, $matches);
        if ($match > 0) {
            /*
             * $match:
             * 0        complete match
             * 1        before
             * 2        brace name
             * 3        after
             */
            $this->braces[] = [
                'query'  => $this->query,
                'before' => $matches[1],
                'after'  => $matches[3],
                'key' => $matches[2],
            ];
            // empty query
            $this->query = '';
            // empty code
            $code = '';

            return $code;
        }

        return $code;
    }
}
