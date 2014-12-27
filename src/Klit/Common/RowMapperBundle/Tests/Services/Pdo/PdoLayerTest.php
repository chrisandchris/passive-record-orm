<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Pdo;

use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Tests\TestKernel;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * @name PdoLayerTest
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoLayerTest extends TestKernel {
    function testConstruct() {
        // we do not test the container load because we don't have configuration available
        // $PdoLayer = $this->container->get('common_rowmapper.pdoLayer');

        // connect to a local sqlite-DB
        $PdoLayer = new PdoLayer(null, 'pdo_sqlite', 'sqlite.db', '3306', 'foobar', null, null);
        $this->assertEquals('Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement',
            $PdoLayer->getAttribute(\PDO::ATTR_STATEMENT_CLASS)[0]
        );
    }

    function testConstructFail() {
        try {
            $PdoLayer = new PdoLayer(null, 'pdo_mysql', 'localhost', 3306, uniqid(), uniqid(), uniqid());
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
