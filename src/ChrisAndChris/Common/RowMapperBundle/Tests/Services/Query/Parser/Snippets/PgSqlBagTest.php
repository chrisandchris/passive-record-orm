<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\PgSqlBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name PgSqlBagTest
 * @version    1
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class PgBagTest extends TestKernel
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
}
