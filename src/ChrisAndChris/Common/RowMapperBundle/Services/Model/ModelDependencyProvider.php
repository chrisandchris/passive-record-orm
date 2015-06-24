<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @name ModelDependencyProvider
 * @version    1.0.1
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
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
    /** @var ContainerInterface the container */
    private $Container;

    function __construct(PdoLayer $PDO, RowMapper $mapper, ErrorHandler $ErrorHandler,
                         LoggerInterface $Logger, Builder $Builder, ContainerInterface $Container = null) {
        $this->PDO = $PDO;
        $this->Mapper = $mapper;
        $this->ErrorHandler = $ErrorHandler;
        $this->Logger = $Logger;
        $this->Builder = $Builder;
        $this->Container = $Container;
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

    /**
     * Get a parameter
     *
     * @param $name
     * @return mixed|null null if container is not set
     */
    public function getParameter($name) {
        if ($this->Container === null) {
            return null;
        }

        return $this->Container->getParameter($name);
    }
}
