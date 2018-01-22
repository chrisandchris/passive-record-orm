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
     * @return mixed
     */
    private function logIn() : float
    {
        if ($this->environment === 'prod' || $this->environment === 'dev') {
            $this->logger->info(sprintf(
                '[ORM] %s: Starting process',
                $this->getTraceMessage()
            ));
        }
        $start = microtime(true);

        return $start;
    }

    public function getTraceMessage(array $trace = null) : string
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

        // take the last call up from BusinessProcess::run()
        // this will be the custom process
        return sprintf(
            '%s::%s->%s()@%d',
            basename($item['file']),
            $shortName,
            $item['function'],
            $item['line']
        );
    }

    /**
     * @param $start
     */
    private function logOut($start)
    {
        if ($this->environment === 'prod' || $this->environment === 'dev') {
            $this->logger->info(sprintf(
                '[ORM] %s: Took %.2Fms: ',
                $this->getTraceMessage(),
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
        $this->logger->debug(sprintf(
            '[ORM] Transaction level is now <%d>',
            $this->transactionLevel
        ));

        if ($this->transactionLevel === 0 && $this->pdoLayer->inTransaction()) {
            $this->logger->info('[ORM] Rolling back changes');
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

        $this->logger->debug(sprintf(
            '[ORM] Transaction level is now <%d>',
            $this->transactionLevel
        ));
        if ($this->transactionLevel < 0) {
            $this->logger->warning('[ORM] Transaction level below 0');
            return;
        }

        if ($this->transactionLevel === 0) {
            $this->logger->debug('[ORM] Commiting changes');
            if (!$this->pdoLayer->commit()) {
                $this->logger->warning('[ORM] Unable to commit transaction');
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
