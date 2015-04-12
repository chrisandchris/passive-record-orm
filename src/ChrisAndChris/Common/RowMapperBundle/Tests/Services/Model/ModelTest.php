<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Services\Logger\PdoLogger;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ModelTest
 * @version 1.0.0
 * @package 
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class
ModelTest extends TestKernel {

    /**
     * @return Model
     */
    private function getModel() {
        $Logger = new PdoLogger('sqlite', 'log.db');

        $DP = new ModelDependencyProvider(
            new PdoLayer('sqlite', 'sqlite.db'),
            new RowMapper(),
            new ErrorHandler(),
            new PdoLogger('sqlite', 'log.db'),
            new Builder(new DefaultParser())
        );

        $Model = new EmptyModel($DP);
        return $Model;
    }

    public function testValidateOffset() {
        $this->assertEquals(0, $this->getModel()->validateOffset(-5));
        $this->assertEquals(0, $this->getModel()->validateOffset(0));
        $this->assertEquals(5, $this->getModel()->validateOffset(5));
        $this->assertEquals(5, $this->getModel()->validateOffset(5.254));
        $this->assertEquals(5, $this->getModel()->validateOffset(5.9));
    }

    public function testValidateLimit() {
        $this->assertEquals(1, $this->getModel()->validateLimit(-5));
        $this->assertEquals(1, $this->getModel()->validateLimit(0));
        $this->assertEquals(1, $this->getModel()->validateLimit(1));
        $this->assertEquals(1, $this->getModel()->validateLimit(1.5));
        $this->assertEquals(99, $this->getModel()->validateLimit(99.9));
        $this->assertEquals(50, $this->getModel()->validateLimit(100, 50));
    }

    public function testSetRunningUser() {
        $Model = $this->getModel();
        $Model->setRunningUser('alpha');

        $Model = $this->getModel();
        $this->assertEquals('alpha', $Model->getRunningUser());
    }
}

class EmptyModel extends Model {
    // empty
}