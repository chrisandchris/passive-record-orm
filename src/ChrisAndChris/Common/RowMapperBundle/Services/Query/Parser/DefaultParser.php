<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;

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
    /** @var SnippetBag */
    private $snippetBag;

    /**
     * Initialize class
     *
     * @param TypeBag    $parameterBag
     * @param SnippetBag $snippetBag
     */
    function __construct(SnippetBag $snippetBag) {
        $this->snippetBag = $snippetBag;
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
        if (!is_array($this->query)) {
            $this->query = [];
        }

        return trim(implode(' ', $this->query));
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
        foreach ($this->statement as $type) {
            $snippet = $this->getSnippet($type['type']);
            $this->query[] = $this->parseCode($type, $snippet);
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
     * @return \Closure
     */
    private function getSnippet($type) {
        return $this->snippetBag->get($type);
    }

    /**
     * Parses the code
     *
     * @param array    $type    the type interface to use
     * @param \Closure $snippet the snippet interface to use
     * @return string the generated query
     * @throws MalformedQueryException
     */
    private function parseCode($type, \Closure $snippet) {
        $result = $snippet($type['params']);

        if (!isset($result['code'])) {
            throw new MalformedQueryException(
                'Invalid result of snippet named "' . $type['type'] . '"'
            );
        }
        if (!isset($result['params'])) {
            $result['params'] = [];
        }

        if ($result['code'] == '/@close') {
            $result['code'] = $this->minimizeBrace();
        }

        $this->checkForParameters($type, $result['code'], $result['params']);
        $result['code'] = $this->checkForMethodChaining($result['code']);

        return $result['code'];
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

        $maxKey = max(array_keys($this->braces));
        $merges = [
            $this->braces[$maxKey]['query'],
            $this->braces[$maxKey]['before'],
            $this->query,
            $this->braces[$maxKey]['after'],
        ];

        $this->query = [];
        foreach ($merges as $merge) {
            if (is_array($merge)) {
                foreach ($merge as $entry) {
                    $this->query[] = $entry;
                }
            } else {
                $this->query[] = $merge;
            }
        }
        $code = '';
        unset($this->braces[max(array_keys($this->braces))]);

        return $code;
    }

    /**
     * Checks for parameters used in the code
     *
     * @param string $type
     * @param string $code
     * @param        $params
     * @throws MissingParameterException
     */
    private function checkForParameters($type, $code, $params) {
        // detect parameters
        $offset = 0;
        $idx = 0;
        if (!is_array($params)) {
            $this->addParameter($params);

            return;
        }
        while (false !== ($pos = mb_strpos($code, '?', $offset))) {
            $offset = $pos + 1;
            if (!isset($params[$idx++])) {
                throw new MissingParameterException(
                    'Missing parameter of type "' . $type . '"'
                );
            }
            $this->addParameter($params[$idx]);
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
