<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;
use Klit\Common\RowMapperBundle\Services\Query\Builder;

/**
 * @name ModelDependencyProvider
 * @version 1.0.0
 * @since v2.0.0
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
    /** @var Builder the query builder */
    private $Builder;

    function __construct(PdoLayer $PDO, RowMapper $mapper, ErrorHandler $ErrorHandler, LoggerInterface $Logger, Builder $Builder) {
        $this->PDO = $PDO;
        $this->Mapper = $mapper;
        $this->ErrorHandler = $ErrorHandler;
        $this->Logger = $Logger;
        $this->Builder = $Builder;
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

    /**
     * @return Builder
     */
    public function getBuilder() {
        return $this->Builder;
    }
}
