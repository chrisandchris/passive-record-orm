<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;

/**
 * @name ModelDependencyProvider
 * @version 1.0.0
 * @since v1.1.0
 * @package KlitCommon
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ModelDependencyProvider {
    /** @var PdoLayer the pdo class */
    private $PDO;
    /** @var RowMapper the row mapper */
    private $Mapper;
    /** @var ErrorHandler */
    private $ErrorHandler;
    /** @var LoggerInterface the logger used to log statements */
    private $Logger;


    function __construct(PdoLayer $PDO, RowMapper $mapper, ErrorHandler $ErrorHandler, LoggerInterface $Logger) {
        $this->PDO = $PDO;
        $this->Mapper = $mapper;
        $this->ErrorHandler = $ErrorHandler;
        $this->Logger = $Logger;
    }

    /**
     * @return PdoLayer
     */
    public function getPDO() {
        return $this->PDO;
    }

    /**
     * @return RowMapper
     */
    public function getMapper() {
        return $this->Mapper;
    }

    /**
     * @return ErrorHandler
     */
    public function getErrorHandler() {
        return $this->ErrorHandler;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->Logger;
    }
}
