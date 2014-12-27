<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Model;

use Klit\Common\RowMapperBundle\Services\Model\Model;
use Klit\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ModelTest
 * @version 1.0.0
 * @package 
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ModelTest extends TestKernel {

    public function getModel() {
        return new DummyModel();
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

class DummyModel extends Model {

    function __construct() {
    }
}
