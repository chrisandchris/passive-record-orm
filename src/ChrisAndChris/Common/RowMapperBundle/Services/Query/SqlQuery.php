<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

/**
 * @name SqlStatement
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class SqlQuery {

    private $query;
    private $parameters;

    function __construct($query, $parameters) {
        $this->query = $query;
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getParameters() {
        return $this->parameters;
    }
}
