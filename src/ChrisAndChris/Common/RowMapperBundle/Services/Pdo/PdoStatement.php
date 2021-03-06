<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

use PDO;

/**
 * Extends the official pdo statement to provide statement logging
 *
 * @name PdoStatement
 * @version   1.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class PdoStatement extends \PDOStatement
{

    /** @var array the list of set parameters */
    private $params = [];
    /** @var bool true if a result is required */
    private $requiresResult = false;
    /** @var bool true if SQL_CALC_FOUND_ROWS is used */
    private $calcRowCapable = false;
    private $errorMessage;

    /**
     * @return boolean
     */
    public function isResultRequired()
    {
        return $this->requiresResult;
    }

    /**
     * Set to true if this statement must have a rowCount greater than zero
     *
     * @param boolean $requiresResult
     * @param string  $errorMessage
     */
    public function requiresResult($requiresResult = true, $errorMessage = '')
    {
        $this->errorMessage = $errorMessage;
        $this->requiresResult = (bool)$requiresResult;
    }

    public function getRequiresResultErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @inheritdoc
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $this->params[$parameter] = $value;

        return parent::bindValue($parameter, $value, $data_type);
    }

    /**
     * @inheritdoc
     */
    public function bindParam(
        $parameter,
        &$variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        $this->params[$parameter] = $variable;

        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    /**
     * @inheritdoc
     */
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        $this->params[$column] = $param;

        return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    /**
     * Get meta data of the query
     *
     * @return string
     */
    public function getMeta()
    {
        return ['query' => $this->queryString, 'params' => $this->params];
    }

    /**
     * @return bool
     */
    public function isCalcRowCapable()
    {
        return $this->calcRowCapable;
    }

    /**
     * @param bool $calcRowCapable
     */
    public function setCalcRowCapable($calcRowCapable = true)
    {
        $this->calcRowCapable = (bool)$calcRowCapable;
    }
}
