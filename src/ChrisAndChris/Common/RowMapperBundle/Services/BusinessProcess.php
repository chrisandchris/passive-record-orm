<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Services;

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
 * @version   1.0.0
 * @since     1.0.0
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
    /**
     * @var string
     */
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
     * @param \Closure $process
     * @return mixed
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Process\TransactionException
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\Database\NoSuchRowFoundException
     */
    public function run(\Closure $process)
    {
        $this->startTransaction();

        try {
            $start = $this->logIn();
            $result = $process();
            $this->logOut($start);

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
                'Upgraded NotFoundHttpException at %s',
                $this->getTraceMessage(debug_backtrace(
                    DEBUG_BACKTRACE_PROVIDE_OBJECT,
                    3
                ))
            ));

            $this->commit();
            throw new NoSuchRowFoundException(
                $exception->getMessage(),
                $exception
            );
        } catch (NoSuchRowFoundException $exception) {
            $this->logger->info(sprintf(
                'Passed through NoSuchRowFoundException at %s',
                $this->getTraceMessage(debug_backtrace(
                    DEBUG_BACKTRACE_PROVIDE_OBJECT,
                    3
                ))
            ));

            $this->commit();
            throw $exception;
        } catch (RowMapperException $exception) {
            $this->logger->error(
                sprintf(
                    'business process failed with exception: %s',
                    $exception->getMessage()
                )
            );
            try {
                $this->rollback();
            } catch (RollbackFailedException $exception) {
                $this->logger->error(sprintf(
                    'Rollback failed: %s',
                    $exception->getMessage()
                ), $exception->getTrace());
            } catch (GeneralDatabaseException $exception) {
                $this->logger->error(sprintf(
                    'General database error: %s',
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
        if ($this->transactionLevel === 0 ||
            !$this->pdoLayer->inTransaction()) {
            if (!$this->pdoLayer->beginTransaction()) {
                throw new TransactionException('Unable to start transaction');
            }
        }
        $this->transactionLevel++;
    }

    /**
     * @return mixed
     */
    private function logIn() : float
    {
        if ($this->environment === 'prod' || $this->environment === 'dev') {
            $this->logger->info(sprintf(
                '%s: Starting process',
                $this->getTraceMessage(
                    debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)
                )
            ));
        }
        $start = microtime(true);

        return $start;
    }

    public function getTraceMessage(array $trace) : string
    {
        foreach ($trace as $item) {
            if (strstr(basename($item['file']), 'Process') &&
                basename($item['file']) !== 'BusinessProcess.php') {
                $curr = $item;
            }
            break;
        }

        // just guess into the wild...
        if (!isset($curr)) {
            $curr = $trace[1];
        }

        return sprintf(
            '%s:%s@%d',
            explode('.', basename($curr['file']), 2)[0],
            $curr['function'],
            $curr['line']
        );
    }

    /**
     * @param $start
     */
    private function logOut($start)
    {
        if ($this->environment === 'prod' || $this->environment === 'dev') {

            $this->logger->info(sprintf(
                '%s: Took %.2Fms: ',
                $this->getTraceMessage(
                    debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)
                ),
                (microtime(true) - $start) * 1000
            ));
        }
    }

    /**
     * Rolls back to the latest savepoint (or initial state)
     *
     * @return void
     */
    public function rollback()
    {
        $this->transactionLevel--;

        if ($this->transactionLevel === 0 && $this->pdoLayer->inTransaction()) {
            $this->pdoLayer->rollBack();
        }
    }

    /**
     * @return void
     * @throws TransactionException
     */
    public function commit()
    {
        $this->transactionLevel--;

        if ($this->transactionLevel < 0) {
            return;
        }

        if ($this->transactionLevel === 0) {
            if (!$this->pdoLayer->commit()) {
                throw new TransactionException('Unable to commit transaction');
            }
        }
    }

    /**
     * @return bool
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\TransactionException
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
