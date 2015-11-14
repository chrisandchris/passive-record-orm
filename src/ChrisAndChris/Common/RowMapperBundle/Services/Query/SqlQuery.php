<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

/**
 * @name SqlStatement
 * @version   1.0.1
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class SqlQuery {

    private $query;
    private $parameters;
    private $requiresResult = false;

    function __construct($query, $parameters = []) {
        $this->query = $query;
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function requiresResult() {
        $this->requiresResult = $this->requiresResult ? false : true;
    }

    /**
     * @inheritdoc
     */
    public function isResultRequired() {
        return $this->requiresResult;
    }
}
