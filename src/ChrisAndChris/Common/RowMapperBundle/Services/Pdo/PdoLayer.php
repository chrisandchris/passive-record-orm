<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use PDO;

/**
 * This is the main database connection which is used for the application
 *
 * @name PdoLayer
 * @version   1.1.1
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class PdoLayer extends \PDO
{

    function __construct($system, $host, $port = null, $name = null, $user = null, $password = null)
    {
        $system = self::getPdoSystem($system);
        $dsn = $this->getDsn($system, $host, $port, $name, $user, $password);
        try {
            parent::__construct($dsn, $user, $password);

            // force to use own pdo statement class
            $this->setPdoAttributes();
        } catch (\PDOException $e) {
            throw new DatabaseException('Unable to init pdo layer', null, $e);
        }
    }

    /**
     * Get the pdo-internal system name
     *
     * @param string $system the system name
     * @return string
     */
    public function getPdoSystem($system = 'pdo_mysql')
    {
        switch ($system) {
            case 'sqlite' :
            case 'pdo_sqlite' :
                return 'sqlite';
            case 'pgsql' :
            case 'pg' :
            case 'postgres' :
                return 'pgsql';
            case 'pdo_mysql' :
            case 'mysqli' :
            default :
                return 'mysql';
        }
    }

    /**
     * Create a dsn
     *
     * @param string $system   the system to connect to (sqlite|mysql)
     * @param string $host     the host
     * @param string $port     the port
     * @param string $database the database name
     * @return null|string
     */
    public function getDsn($system, $host, $port, $database, $username, $password)
    {
        switch ($system) {
            case 'mysql' :
                if (false !== ($env = $this->getEnvStatus())) {
                    $database = $database . '_' . $env;
                }

                return $this->getMysqlDsn($host, $port, $database);
            case 'sqlite' :

                return $this->getSqliteDsn($host);
            case 'pgsql' :
                if (false !== $env = $this->getEnvStatus()) {
                    $database = $database . '_' . $env;
                }

                return $this->getPgDsn($host, $port, $database, $username, $password);
            default :
                return null;
        }
    }

    private function getEnvStatus()
    {
        $envValue = getenv('ENV_TEST_DB_NAME');
        if ($envValue === null) {
            return false;
        }

        return $envValue;
    }

    private function getMysqlDsn($host, $port, $database)
    {
        return implode(null, [
            'mysql:dbname=',
            $database,
            ';host=',
            $host,
            ';port=',
            $port,
            ';charset=utf8',
        ]);
    }

    private function getSqliteDsn($host)
    {
        return 'sqlite:' . $host;
    }

    private function getPgDsn($host, $port, $database, $username, $password)
    {
        return implode(null, [
            'pgsql:host=',
            $host,
            ';port=',
            $port,
            ';dbname=',
            $database,
            ';user=',
            $username,
            ';password=',
            $password,
        ]);
    }

    private function setPdoAttributes()
    {
        $this->setAttribute(
            PDO::ATTR_STATEMENT_CLASS, [
                'ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement',
            ]
        );
    }
}
