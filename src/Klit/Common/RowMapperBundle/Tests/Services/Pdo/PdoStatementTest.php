<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Pdo;

use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLogger;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use Klit\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name PdoStatementTest
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoStatementTest extends TestKernel {
    /** @var PdoLayer */
    private $PdoLayer;
    /** @var PdoLogger */
    private $Logger;

    public function getStatement($query, $initLogger = false) {
        if (!$initLogger) {
            $this->PdoLayer = new PdoLayer(null, 'sqlite', 'sqlite.db', null, null, null, null);
        } else {
            $this->Logger = new PdoLogger(null, 'sqlite', 'log.db', null, null, null, null, null);
            $this->PdoLayer = new PdoLayer($this->Logger, 'sqlite', 'sqlite.db', null, null, null, null);
            $this->createLogTable($this->Logger);
        }
        return $this->PdoLayer->prepare($query);
    }

    private function createLogTable(PdoLogger $Logger) {
        $Statement = $Logger->prepare('CREATE TABLE IF NOT EXISTS log (
          log_id INTEGER PRIMARY KEY AUTOINCREMENT ,
          user_id INTEGER,
          log_requestid VARCHAR(255),
          log_type VARCHAR(255),
          log_date DATETIME,
          log_querymeta TEST,
          log_exectime FLOAT
        ) ;');
        if (!$Statement->execute()) {
            $this->fail('Unable to create log table: ' . $Statement->errorInfo());
        }
    }

    public function testSetUser() {
        PdoStatement::setUser(1);
        $this->assertEquals(1, PdoStatement::getUser());
        PdoStatement::setUser(2);
        $this->assertEquals(1, PdoStatement::getUser());
    }

    public function testExecuteSimple() {
        $Statement = $this->getStatement('SELECT 1');
        $Statement->execute();
    }

    public function testExecuteValue() {
        $Statement = $this->getStatement('SELECT :param');
        $Statement->bindValue('param', 1, \PDO::PARAM_INT);
        $Statement->execute();
    }

    public function testExecuteColumn() {
        $value = 1;
        $Statement = $this->getStatement('SELECT :param AS param');
        $Statement->bindColumn('param', $value, \PDO::PARAM_INT);
        $Statement->execute();
    }

    public function testExecuteParam() {
        $value = 1;
        $Statement = $this->getStatement('SELECT :param');
        $Statement->bindParam('param', $value, \PDO::PARAM_INT);
        $Statement->execute();
    }

    public function testWriteLog() {
        $Statement = $this->getStatement('SELECT 1', true);

        $DeleteStatement = $this->Logger->prepare('DELETE FROM log WHERE 1');
        if (!$DeleteStatement->execute()) {
            $this->fail('Unable to delete log rows: ' . $DeleteStatement->errorInfo());
        }

        $Statement->execute();

        $CountStatement = $this->Logger->prepare('SELECT COUNT(*) FROM log');
        if ($CountStatement->execute()) {
            $this->assertEquals(1, $CountStatement->fetch(\PDO::FETCH_NUM)[0]);
        } else {
            $this->fail('Unable to fetch log row count');
        }
    }

    public function testMultipleValues() {
        $Statement = $this->getStatement('SELECT :param, :param2');
        $Statement->bindValue('param', 1, \PDO::PARAM_INT);
        $Statement->bindValue('param2', 2, \PDO::PARAM_INT);
        $Statement->execute();
    }

    public function testNotLoggable() {
        $Statement = $this->getStatement('SELECT 1 -- @@DNL', true);

        $DeleteStatement = $this->Logger->prepare('DELETE FROM log WHERE 1');
        if (!$DeleteStatement->execute()) {
            $this->fail('Unable to delete log rows: ' . $DeleteStatement->errorInfo());
        }

        $Statement->execute();

        $CountStatement = $this->Logger->prepare('SELECT COUNT(*) FROM log');
        if ($CountStatement->execute()) {
            $this->assertEquals(0, $CountStatement->fetch(\PDO::FETCH_NUM)[0]);
        } else {
            $this->fail('Unable to fetch log row count');
        }
    }
}
