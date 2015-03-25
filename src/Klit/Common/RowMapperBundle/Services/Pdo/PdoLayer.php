<?php
namespace Klit\Common\RowMapperBundle\Services\Pdo;

use PDO;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * This is the main database connection which is used for the application
 *
 * @name PdoLayer.php
 * @version 1.1.1
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoLayer extends \PDO {
    function __construct($system, $host, $port = null, $name = null, $user = null, $password = null) {
        $system = self::getPdoSystem($system);
        $dsn = $this->getDsn($system, $host, $port, $name);
        try {
            parent::__construct(
                $dsn, $user, $password
            );

            // $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // force to use own pdo statement class
            $this->setPdoAttributes();
        } catch(\PDOException $e) {
            throw new FatalErrorException("Unable to init PdoLayer: " . $e->getMessage());
        }
    }

    private function setPdoAttributes() {
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array(
            'Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement',
        ));
    }

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
        return 'mysql:dbname=' . $name . ';host=' . $host . ';port=' . $port . ';charset=utf8';
    }

    private static function getSqliteDsn($host) {
        return 'sqlite:' . $host;
    }
}
