<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities\SearchResultUtility;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name ModelDependencyProvider
 * @version    2.1.0
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ModelDependencyProvider {

    /** @var PdoLayer the pdo class */
    private $pdo;
    /** @var RowMapper the row mapper factory */
    private $mapperFactory;
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var ContainerInterface the container */
    private $container;
    /** @var BuilderFactory the builder factory */
    private $builderFactory;
    /** @var EventDispatcherInterface the event dispatcher */
    private $eventDispatcher;
    /**
     * @var \ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoFactory
     */
    private $pdoFactory;

    function __construct(
        PdoFactory $pdoFactory,
        RowMapperFactory $mapperFactory,
        ErrorHandler $errorHandler,
        BuilderFactory $builderFactory,
        ContainerInterface $container = null,
        EventDispatcherInterface $eventDispatcher = null) {

        $this->mapperFactory = $mapperFactory;
        $this->errorHandler = $errorHandler;
        $this->builderFactory = $builderFactory;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->pdoFactory = $pdoFactory;
    }

    /**
     * @param string|null $type either r for read-only and w for write
     * @return \ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer
     */
    public function getPdo(string $type = null)
    {
        return $this->pdoFactory->getPdo($type);
    }

    /**
     * @return RowMapper
     */
    public function getMapper() {
        return $this->mapperFactory->getMapper();
    }

    /**
     * @return ErrorHandler
     */
    public function getErrorHandler() {
        return $this->errorHandler;
    }

    /**
     * @return Builder
     */
    public function getBuilder() {
        return $this->builderFactory->createBuilder();
    }

    /**
     * @return SearchResultUtility
     */
    public function getSearchResultUtility()
    {
        return $this->container->get('common_rowmapper.search.result_utility');
    }

    /**
     * Creates a new database event
     *
     * @param string $event      the event name
     * @param string $type       the query type (select, update, ...)
     * @param string $table      the primary affected table
     * @param mixed  $primaryKey the primary key affected
     * @return DatabaseEvent
     * @throws InvalidOptionException
     */
    public function createEvent(
        $event, $type, $table = null, $primaryKey = null) {

        if ($this->eventDispatcher === null) {
            throw new InvalidOptionException('No event dispatcher available to run event');
        }

        $eventData = new DatabaseEvent($type, $table, $primaryKey);

        return $this->dispatchEvent($event, $eventData);
    }

    private function dispatchEvent($eventName, Event $event) {
        return $this->eventDispatcher->dispatch($eventName, $event);
    }

    public function addListener($eventName, \Closure $callable, $priority = 0) {
        return $this->eventDispatcher->addListener($eventName, $callable, $priority);
    }

    /**
     * Get a parameter
     *
     * @param $name
     * @return mixed|null null if container is not set
     * @throws InvalidOptionException
     */
    public function getParameter($name) {
        if ($this->container === null) {
            throw new InvalidOptionException('No container available to fetch parameter');
        }

        return $this->container->getParameter($name);
    }
}
