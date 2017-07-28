<?php

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\TypeCaster;

/**
 * @name TypeCasterTest
 * @version    1.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 *
 * @covers     ChrisAndChris\Common\RowMapperBundle\Services\Mapper\TypeCaster
 */
class TypeCasterTest extends \PHPUnit_Framework_TestCase
{

    public function test_cast()
    {
        $caster = new TypeCaster();

        $this->assertSame(
            123,
            $caster->cast('int', '123')
        );
    }

    public function test_toInt()
    {
        $caster = new TypeCaster();

        $this->assertSame(
            123,
            $caster->castInt('123')
        );
    }

    public function test_toJson()
    {
        $caster = new TypeCaster();

        $this->assertSame(
            ['foobar' => ['item']],
            $caster->castJson('{"foobar":["item"]}')
        );
    }

    public function test_dateTime()
    {
        $caster = new TypeCaster();

        $this->assertEquals(
            (new \DateTime('2017-01-01 12:00:05'))->format('Y-m-d H:i:s'),
            $caster->castDate('2017-01-01 12:00:05')
                   ->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            (new \DateTime('2017-01-01 00:00:00'))->format('Y-m-d H:i:s'),
            $caster->castDate('2017-01-01')
                   ->format('Y-m-d H:i:s')
        );
    }
}
