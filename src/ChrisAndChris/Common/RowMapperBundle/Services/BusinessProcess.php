<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Services;

use ChrisAndChris\Common\RowMapperBundle\Events\Process\ProcessEvent;
use ChrisAndChris\Common\RowMapperBundle\Events\Process\ProcessOutEvent;
use ChrisAndChris\Common\RowMapperBundle\Events\ProcessEvents;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Database\NoSuchRowFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\GeneralDatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\RollbackFailedException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\TransactionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\RowMapperException;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 *
 * @name BusinessProcess
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class BusinessProcess
{

    /**
     * @var PdoLayer
     */
    private $pdoLayer;
    /** @var int */
    private $transactionLevel = 0;
    /** @var LoggerInterface */
    private $logger;
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;
    /** @var string */
    private $environment;

    /**
     * BusinessProcess constructor.
     *
     * @param string                                                      $environment
     * @param PdoLayer                                                    $pdoLayer
     * @param LoggerInterface                                             $logger
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        string $environment,
        PdoLayer $pdoLayer,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->pdoLayer = $pdoLayer;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->environment = $environment;
    }

    /**
     * Runs a process
     * If the process does not throw an exception, a successful run is assumed
     *
     * @param \Closure            $process
     * @param string|null         $eventClass the event class to dispatch
     * @param mixed|\Closure|null $eventData  if not null, data to set to out
     *                                        event
     * @return mixed
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Database\NoSuchRowFoundException
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\GeneralDatabaseException
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\RollbackFailedException
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\TransactionException
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\RowMapperException
     */
    public function run(
        \Closure $process,
        $eventClass = null,
        $eventData = null
    ) {
        $this->startTransaction();

        try {
            $start = $this->logIn($eventClass);
            $result = $process();
            $this->logOut($start, $eventClass, $result, $eventData);

            if (!$this->pdoLayer->inTransaction()) {
                $this->rollback();
                throw new GeneralDatabaseException(
                    'No transaction running, check query.'
                );
            }
        } catch (NotFoundHttpException $exception) {
            // earlier versions returned NotFoundHttpException
            // which we must "upgrade" to NoSuchRowFoundException
            $this->logger->info(sprintf(
                '[ORM] Upgraded NotFoundHttpException at %s',
                $this->getTraceMessage()
            ));

            $this->commit();
            throw new NoSuchRowFoundException(
                $exception->getMessage(),
                $exception
            );
        } catch (NoSuchRowFoundException $exception) {
            $this->logger->info(sprintf(
                '[ORM] Passed through NoSuchRowFoundException at %s',
                $this->getTraceMessage()
            ));

            $this->commit();
            throw $exception;
        } catch (RowMapperException $exception) {
            $this->logger->error(
                sprintf(
                    '[ORM] Business process failed with exception: %s',
                    $exception->getMessage()
                )
            );
            try {
                $this->rollback();
            } catch (RollbackFailedException $exception) {
                $this->logger->error(sprintf(
                    '[ORM] Rollback failed: %s',
                    $exception->getMessage()
                ), $exception->getTrace());
            } catch (GeneralDatabaseException $exception) {
                $this->logger->error(sprintf(
                    '[ORM] General database error: %s',
                    $exception->getMessage()
                ), $exception->getTrace());
            }
            throw $exception;
        }

        $this->commit();

        return $result;
    }

    /**
     * Starts a transaction, or creates a savepoint if already one started
     *
     * @return void
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\TransactionException
     */
    public function startTransaction()
    {
        $this->logger->debug('[ORM] Starting transaction...');
        if ($this->transactionLevel === 0 ||
            !$this->pdoLayer->inTransaction()) {
            if (!$this->pdoLayer->beginTransaction()) {
                $this->logger->warning('[ORM] Unable to start transaction');
                throw new TransactionException('Unable to start transaction');
            }
        }
        $this->transactionLevel++;
        $this->logger->debug(sprintf(
            '[ORM] Transaction level is now <%d>',
            $this->transactionLevel
        ));
    }

    /**
     * @param mixed $eventClass
     * @return mixed
     */
    private function logIn($eventClass) : float
    {
        $trace = $this->getTraceInfo();

        $this->logger->info(sprintf(
            '[ORM] %s: Starting process',
            $this->formatTraceInfo($trace)
        ));
        $start = microtime(true);

        if ($eventClass !== null) {
            try {
                $event = new $eventClass(
                    $trace['shortName'],
                    $trace['function']
                );
            } catch (\Exception $exception) {
                $this->logger->warning(sprintf(
                    '[ORM] Unable to create instance from <%s>: %s',
                    $eventClass,
                    $exception->getMessage()
                ));
            }
        }
        if (!isset($event) || $event === null) {
            $this->logger->debug('[ORM] Falling back to ProcessEvent');
            $event = new ProcessEvent($trace['shortName'], $trace['function']);
        }

        $this->logger->info(sprintf(
            'Dispatching In Event using <%s>',
            get_class($event)
        ));
        $this->eventDispatcher->dispatch(
            ProcessEvents::ON_IN,
            $event
        );

        return $start;
    }

    /**
     * @param array $trace
     * @return array
     */
    public function getTraceInfo(array $trace = null) : array
    {
        if ($trace === null) {
            $trace = debug_backtrace(0);
        }
        $break = false;
        foreach ($trace as $index => $item) {
            if ($break) {
                break;
            }
            // as soon as we hit BusinessProcess::run(), stop
            if ($item['function'] == 'run') {
                $break = true;
            }
        };

        // make sure all keys exist
        $requiredKeys = ['file', 'function', 'line', 'class'];
        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $item)) {
                $item[$requiredKey] = '';
            }
        }

        // try to get short name of class
        $shortName = '(unknown)';
        try {
            $shortName = (new \ReflectionClass($item['class']))->getShortName();
        } catch (\ReflectionException $exception) {
            $this->logger->warning(sprintf(
                'Unable to get short name: <%s>',
                $exception->getMessage()
            ));
        }

        return [
            'file'      => $item['file'],
            'function'  => $item['function'],
            'line'      => $item['line'],
            'shortName' => $shortName,
        ];
    }

    public function formatTraceInfo(array $trace)
    {
        return sprintf(
            '%s::%s->%s()@%d',
            basename($trace['file']),
            $trace['shortName'],
            $trace['function'],
            $trace['line']
        );
    }

    /**
     * @param int                 $start      start time in microseconds
     * @param string|null         $eventClass the event class to dispatch
     * @param mixed|null          $result     the result of the process
     * @param \Closure|mixed|null $eventData  if not null, the effective data
     */
    private function logOut(
        $start,
        $eventClass = null,
        $result = null,
        $eventData = null
    ) {
        $trace = $this->getTraceInfo();

        if ($eventData instanceof \Closure) {
            $result = $eventData($result);
        }

        if ($eventClass !== null) {
            try {
                $event = new $eventClass(
                    $trace['shortName'],
                    $trace['function'],
                    $result
                );
            } catch (\Exception $exception) {
                $this->logger->warning(sprintf(
                    '[ORM] Unable to create instance from <%s>: %s',
                    $eventClass,
                    $exception->getMessage()
                ));
            }
        }
        if (!isset($event) || $event === null) {
            $this->logger->debug('[ORM] Falling back to ProcessOutEvent');
            $event = new ProcessOutEvent(
                $trace['shortName'],
                $trace['function'],
                $result
            );
        }

        $this->logger->info(sprintf(
            'Dispatching Out Event using <%s>',
            get_class($event)
        ));
        $this->eventDispatcher->dispatch(
            ProcessEvents::ON_OUT,
            $event
        );

        $this->logger->info(sprintf(
            '[ORM] %s: Took %.2Fms: ',
            $this->formatTraceInfo($trace),
            (microtime(true) - $start) * 1000
        ));
    }

    /**
     * Rolls back to the latest savepoint (or initial state)
     *
     * @return void
     */
    public function rollback()
    {
        $this->transactionLevel--;
        $this->logger->debug(sprintf(
            '[ORM] Transaction level is now <%d>',
            $this->transactionLevel
        ));

        if ($this->transactionLevel === 0 && $this->pdoLayer->inTransaction()) {
            $this->logger->info('[ORM] Rolling back changes');
            $this->pdoLayer->rollBack();
        }
    }

    public function getTraceMessage(array $trace = null) : string
    {
        $trace = $this->getTraceInfo($trace);

        return $this->formatTraceInfo($trace);
    }

    /**
     * @return void
     * @throws TransactionException
     */
    public function commit()
    {
        $this->transactionLevel--;

        $this->logger->debug(sprintf(
            '[ORM] Transaction level is now <%d>',
            $this->transactionLevel
        ));
        if ($this->transactionLevel < 0) {
            $this->logger->warning('[ORM] Transaction level below 0');

            return;
        }

        if ($this->transactionLevel === 0) {
            $this->logger->debug('[ORM] Committing changes');
            if (!$this->pdoLayer->commit()) {
                $this->logger->warning('[ORM] Unable to commit transaction');
                throw new TransactionException('Unable to commit transaction');
            }
        }
    }

    /**
     * @return bool
     */
    public function reset() : bool
    {
        if ($this->pdoLayer->inTransaction()) {
            $this->rollback();
        }

        return true;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }

    public function getEventDispatcher() : EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
}
