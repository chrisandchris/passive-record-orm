<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

/**
 * @name SqlQuery
 * @version    1.0.2
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SqlQuery {

    private $query;
    private $parameters;
    private $requiresResult = false;

    function __construct($query, $parameters = []) {
        $this->query = $query;
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }
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
