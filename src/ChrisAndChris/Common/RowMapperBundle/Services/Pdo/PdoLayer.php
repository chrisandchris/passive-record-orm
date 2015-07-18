<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Pdo;

use PDO;
use Symfony\Component\Debug\Exception\FatalErrorException;

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
class PdoLayer extends \PDO {

    function __construct(
        $system, $host, $port = null, $name = null, $user = null,
        $password = null) {

        $system = self::getPdoSystem($system);
        $dsn = $this->getDsn($system, $host, $port, $name);
        try {
            parent::__construct(
                $dsn, $user, $password
            );

            // $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // force to use own pdo statement class
            $this->setPdoAttributes();
        } catch (\PDOException $e) {
            throw new FatalErrorException(
                "Unable to init PdoLayer: ".$e->getMessage()
            );
        }
    }

    /**
     * Get the pdo-internal system name
     *
     * @param string $system the system name
     * @return string
     */
    public static function getPdoSystem($system = 'pdo_mysql') {
        switch ($system) {
            case 'sqlite' :
            case 'pdo_sqlite' :
                return 'sqlite';
            case 'pdo_mysql' :
            case 'mysqli' :
            default :
                return 'mysql';
        }
    }

    /**
     * Create a dsn
     *
     * @param string $system the system to connect to (sqlite|mysql)
     * @param string $host   the host
     * @param string $port   the port
     * @param string $name   the database name
     * @return null|string
     */
    public static function getDsn($system, $host, $port, $name) {
        switch ($system) {
            case 'mysql' :
                return self::getMysqlDsn($host, $port, $name);
            case 'sqlite' :
                return self::getSqliteDsn($host);
            default :
                return null;
        }
    }

    private static function getMysqlDsn($host, $port, $name) {
        return 'mysql:dbname='.$name.';host='.$host.';port='.$port.
        ';charset=utf8';
    }

    private static function getSqliteDsn($host) {
        return 'sqlite:'.$host;
    }

    private function setPdoAttributes() {
        $this->setAttribute(
            PDO::ATTR_STATEMENT_CLASS, [
                'ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement',
            ]
        );
    }
}
