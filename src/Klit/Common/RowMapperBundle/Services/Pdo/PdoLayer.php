<?php
namespace Klit\Common\RowMapperBundle\Services\Pdo;

use PDO;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * This is the main database connection which is used for the application
 *
 * @name PdoLayer.php
 * @version 1.1.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoLayer extends \PDO {
    private $Logger;

    function __construct(PdoLogger $PdoLogger = null, $system, $host, $port, $name, $user, $password) {
        if ($system == 'pdo_mysql') {
            $system = 'mysql';
        }
        $dsn = $system . ':dbname=' . $name . ';host=' . $host . ';port=' . $port . ';charset=utf8';
        try {
            parent::__construct(
                $dsn, $user, $password
            );

            $this->Logger = $PdoLogger;

            // $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // force to use own pdo statement class
            $this->setPdoAttributes();
        } catch(Exception $e) {
            throw new FatalErrorException("Unable to init PdoLayer: " . $e->getMessage());
        }
    }

    private function setPdoAttributes() {
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array(
            'Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement', array($this->Logger)
        ));
    }
}
