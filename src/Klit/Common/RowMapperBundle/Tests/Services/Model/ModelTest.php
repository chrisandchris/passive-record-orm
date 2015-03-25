<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Model;

use Klit\Common\RowMapperBundle\Services\Logger\PdoLogger;
use Klit\Common\RowMapperBundle\Services\Model\ErrorHandler;
use Klit\Common\RowMapperBundle\Services\Model\Model;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;
use Klit\Common\RowMapperBundle\Tests\TestKernel;

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
    public function getModel() {
        $Logger = new PdoLogger('sqlite', 'log.db');

        $Model = new EmptyModel(
            new PdoLayer('sqlite', 'sqlite.db'),
            new RowMapper(),
            new ErrorHandler(),
            $Logger
        );
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
}

class EmptyModel extends Model {
    // empty
}