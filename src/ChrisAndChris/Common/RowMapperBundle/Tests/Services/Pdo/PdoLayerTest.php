<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * @name PdoLayerTest
 * @version   1
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class PdoLayerTest extends TestKernel {

    function testConstruct() {
        // we do not test the container load because we don't have configuration available
        // $PdoLayer = $this->container->get('common_rowmapper.pdoLayer');

        // connect to a local sqlite-DB
        $PdoLayer = new PdoLayer('sqlite', 'sqlite.db');
        $this->assertEquals('ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement',
            $PdoLayer->getAttribute(\PDO::ATTR_STATEMENT_CLASS)[0]
        );
    }

    function testConstructFail() {
        try {
            $PdoLayer = new PdoLayer('mysql', 'localhost', 3306, uniqid(), uniqid(), uniqid());
            $this->fail('Must fail due to wrong connection parameters');
        } catch (FatalErrorException $e) {
            // that's good
        }
    }

    function testDefaultDsn() {
        $this->assertEquals(null, PdoLayer::getDsn('unknown', 'zero', 0, 'zero'));
    }

    function testGetPdoSystem() {
        $this->assertEquals('sqlite', PdoLayer::getPdoSystem('pdo_sqlite'));
        $this->assertEquals('mysql', PdoLayer::getPdoSystem('pdo_mysql'));
        $this->assertEquals('mysql', PdoLayer::getPdoSystem('mysqli'));
        $this->assertEquals('mysql', PdoLayer::getPdoSystem('noSuchSystemAvailable'));
    }
}
