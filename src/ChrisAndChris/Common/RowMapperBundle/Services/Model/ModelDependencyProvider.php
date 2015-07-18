<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name ModelDependencyProvider
 * @version    2.0.0
 * @since      v2.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ModelDependencyProvider {

    /** @var PdoLayer the pdo class */
    private $pdo;
    /** @var RowMapper the row mapper */
    private $mapper;
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var LoggerInterface the logger used to log statements */
    private $logger;
    /** @var Builder the query builder */
    private $builder;
    /** @var ContainerInterface the container */
    private $container;

    function __construct(
        PdoLayer $pdo,
        RowMapper $mapper,
        ErrorHandler $errorHandler,
        LoggerInterface $logger,
        Builder $builder,
        ContainerInterface $container = null,
        EventDispatcherInterface $eventDispatcher = null) {

        $this->pdo = $pdo;
        $this->mapper = $mapper;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
        $this->builder = $builder;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return PdoLayer
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * @return RowMapper
     */
    protected function getMapper() {
        return $this->mapper;
    }

    /**
     * @return ErrorHandler
     */
    protected function getErrorHandler() {
        return $this->errorHandler;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger() {
        return $this->logger;
    }

    /**
     * @return Builder
     */
    protected function getBuilder() {
        return $this->builder;
    }

    /**
     * Get a parameter
     *
     * @param $name
     * @return mixed|null null if container is not set
     * @throws InvalidOptionException
     */
    protected function getParameter($name) {
        if ($this->container === null) {
            throw new InvalidOptionException('No container available to fetch parameter');
        }

        return $this->container->getParameter($name);
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
    protected function createEvent(
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
}
