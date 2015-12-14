<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @name RowMapperFactoryTest
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class RowMapperFactoryTest extends TestKernel {

    public function testFactory() {
        $factory = new RowMapperFactory(new EventDispatcher());
        $mapper = $factory->getMapper();

        $this->assertTrue($mapper instanceof RowMapper);
    }
}
