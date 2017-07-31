<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapper\ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Relation;

use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\MySqlBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Relation\RelationQueryBuilder;
use ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo\InvoiceDemoEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 *
 * @name RelationQueryBuilderTest
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 * @covers    ChrisAndChris\Common\RowMapperBundle\Services\Query\Relation\RelationQueryBuilder
 */
class RelationQueryBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testBuild()
    {
        $relationQueryBuilder = new RelationQueryBuilder();

        $builder = $relationQueryBuilder->build(
            $this->getBuilder(),
            InvoiceDemoEntity::class
        );

        $query = $builder->getSqlQuery();
        $this->assertEquals(
            'SELECT `invoice_id` , `customer_id` FROM `invoice`',
            $query->getQuery()
        );
    }

    protected function getBuilder()
    {
        $typeBag = new TypeBag();

        /** @var EventDispatcherInterface $ed */
        $ed =
            $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
                 ->disableOriginalConstructor()
                 ->getMock();
        $event = new SnippetBagEvent();
        $event->add(new MySqlBag(), ['mysql']);
        $ed->method('dispatch')
           ->willReturn($event);
        $parser = new DefaultParser($ed, 'mysql');

        return new Builder($parser, $typeBag);
    }
}
