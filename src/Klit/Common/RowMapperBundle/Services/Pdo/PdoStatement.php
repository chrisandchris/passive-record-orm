<?php
namespace Klit\Common\RowMapperBundle\Services\Pdo;

use PDO;

/**
 * Extends the official pdo statement to provide statement logging
 *
 * @name PdoStatement
 * @version 1.1.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoStatement extends \PDOStatement {
    /** @var array the list of set parameters */
    private $params = array();
    private $mustHaveResult = false;

    /**
     * Set to true if this statement must have a rowCount greater than zero
     *
     * @param boolean $mustHaveResult
     */
    public function setMustHaveResult($mustHaveResult = true) {
        $this->mustHaveResult = (bool)$mustHaveResult;
    }

    /**
     * @return boolean
     */
    public function isMustHaveResult() {
        return $this->mustHaveResult;
    }

    /**
     * @inheritdoc
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {
        $this->params[$parameter] = $value;
        return parent::bindValue($parameter, $value, $data_type);
    }

    /**
     * @inheritdoc
     */
    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {
        $this->params[$parameter] = $variable;
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    /**
     * @inheritdoc
     */
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null) {
        $this->params[$column] = $param;
        return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    /**
     * Get meta data of the query
     *
     * @return string
     */
    public function getMeta() {
        return array('query' => $this->queryString, 'params' => $this->params);
    }
}
