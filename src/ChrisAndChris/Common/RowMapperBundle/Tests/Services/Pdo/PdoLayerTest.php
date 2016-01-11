<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

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
        $layer = $this->getLayer();

        $this->assertEquals(
            'ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement',
            $layer->getAttribute(\PDO::ATTR_STATEMENT_CLASS)[0]
        );
    }

    public function getLayer()
    {
        return new PdoLayer('sqlite', 'sqlite.db');;
    }

    function testConstructFail() {
        try {
            new PdoLayer('mysql', 'localhost', 3306, uniqid(), uniqid(), uniqid());
            $this->fail('Must fail due to wrong connection parameters');
        } catch (DatabaseException $e) {
            // that's good
        }
    }

    function testDefaultDsn() {
        $layer = $this->getLayer();
        $this->assertEquals(null, $layer->getDsn('unknown', 'zero', 0, 'zero', null, null));
    }

    function testGetPdoSystem() {
        $layer = $this->getLayer();
        $this->assertEquals('sqlite', $layer->getPdoSystem('sqlite'));
        $this->assertEquals('mysql', $layer->getPdoSystem('pdo_mysql'));
        $this->assertEquals('mysql', $layer->getPdoSystem('mysqli'));
        $this->assertEquals('mysql', $layer->getPdoSystem('noSuchSystemAvailable'));
    }
}
