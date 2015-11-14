<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @name RowMapperFactoryTest
 * @version
 * @since
 * @package
 * @subpackage
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
 */
class RowMapperFactoryTest extends TestKernel {

    public function testFactory() {
        $factory = new RowMapperFactory(new EventDispatcher());
        $mapper = $factory->getMapper();

        $this->assertTrue($mapper instanceof RowMapper);
    }
}
