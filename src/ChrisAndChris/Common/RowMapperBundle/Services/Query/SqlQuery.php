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
class SqlQuery
{

    private $calcRowCapable = false;
    private $query;
    private $parameters;
    private $requiresResult = false;
    private $errorMessage;

    function __construct($query, $parameters = [], $calcRowCapable = false)
    {
        $this->query = $query;
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }
        $this->parameters = $parameters;
        $this->calcRowCapable = $calcRowCapable;
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function requiresResult($errorMessage = '')
    {
        $this->errorMessage = $errorMessage;
        $this->requiresResult = $this->requiresResult ? false : true;
    }

    public function getRequiresResultErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @inheritdoc
     */
    public function isResultRequired()
    {
        return $this->requiresResult;
    }

    public function isCalcRowCapable()
    {
        return $this->calcRowCapable;
    }
}
