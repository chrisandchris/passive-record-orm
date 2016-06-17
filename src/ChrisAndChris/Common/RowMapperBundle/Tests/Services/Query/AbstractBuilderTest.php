<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\MySqlBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name AbstractBuilderTest
 * @since   v2.0.3
 * @package RowMapperBundle
 */
abstract class AbstractBuilderTest extends TestKernel {

    protected function getBuilder() {
        $typeBag = new TypeBag();

        /** @var EventDispatcherInterface $ed */
        $ed = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
                   ->disableOriginalConstructor()
                   ->getMock();
        $event = new SnippetBagEvent();
        $event->add(new MySqlBag(), ['mysql']);
        $ed->method('dispatch')
           ->willReturn($event);
        $parser = new DefaultParser($ed, 'mysql');

        return new Builder($parser, $typeBag);
    }

    protected function equals($expected, Builder $Builder) {
        $this->assertEquals(
            $expected, $this->minify(
            $Builder->getSqlQuery()
                    ->getQuery()
        )
        );
    }

    protected function minify($query) {
        while (strstr($query, '  ') !== false) {
            $query = str_replace('  ', ' ', $query);
        }

        return $query;
    }
}
