<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\PgSqlBag;

/**
 * @name PgSqlBagTest
 * @version    1
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 *
 * @covers     \ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\PgSqlBag
 */
class PgSqlBagTest extends GeneralBagTest
{

    public function testEventSubscriber()
    {
        $bag = new PgSqlBag();

        $events = $bag->getSubscribedEvents();
        $this->assertTrue(is_array($events));
        foreach ($events as $event => $methods) {
            $this->assertTrue(is_string($event));
            $this->assertTrue(is_array($methods));
            $this->assertTrue(method_exists($bag, $methods[0]));
        }
    }

    public function testBag_cast()
    {
        $bag = new PgSqlBag();

        $cast = $bag->get('cast');

        $this->assertEquals(
            '::int',
            $cast(['cast' => '::int'])['code']
        );
        $this->assertEquals(
            '::int[]',
            $cast(['cast' => '::int[]'])['code']
        );
        $this->assertEquals(
            '::int []',
            $cast(['cast' => '::int []'])['code']
        );
        $this->assertEquals(
            '::int []',
            $cast(['cast' => '::int []'])['code']
        );
        $this->assertEquals(
            ':: int',
            $cast(['cast' => ':: int'])['code']
        );
        $this->assertEquals(
            ':: varchar(255)',
            $cast(['cast' => ':: varchar(255)'])['code']
        );
        $this->assertEquals(
            ':: varchar(255)[]',
            $cast(['cast' => ':: varchar(255)[]'])['code']
        );
        $this->assertEquals(
            ':: varchar(255) []',
            $cast(['cast' => ':: varchar(255) []'])['code']
        );

        try {
            $cast(['cast' => 'int']);
            $this->fail('Must fail due to invalid cast string');
        } catch (MalformedQueryException $e) {
            // ignore
        }
        try {
            $cast(['cast' => 'int[]']);
            $this->fail('Must fail due to invalid cast string');
        } catch (MalformedQueryException $e) {
            // ignore
        }
        try {
            $cast(['cast' => ':::int']);
            $this->fail('Must fail due to invalid cast string');
        } catch (MalformedQueryException $e) {
            // ignore
        }
        try {
            $cast(['cast' => '::int[']);
            $this->fail('Must fail due to invalid cast string');
        } catch (MalformedQueryException $e) {
            // ignore
        }
        try {
            $cast(['cast' => '::int[ ]']);
            $this->fail('Must fail due to invalid cast string');
        } catch (MalformedQueryException $e) {
            // ignore
        }
    }
}
