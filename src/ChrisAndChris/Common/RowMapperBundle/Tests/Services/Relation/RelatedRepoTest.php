<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation;

use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity;
use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\ResultSet;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\MySqlBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Relation\RelationQueryBuilder;
use ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo\OperationDemoEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 *
 * @name RelatedRepoTest
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 * @covers    \ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\DemoRelatedRepo
 */
class RelatedRepoTest extends \PHPUnit_Framework_TestCase
{

    public function testFetch()
    {
        $repo = new DemoRelatedRepo($this->getBuilder());

        $instance = $repo->fetchSingle(1);

        $this->assertTrue(
            $instance instanceof RelatedEntity
        );
        $relatedSet = $instance->get(OperationDemoEntity::class);
        $this->assertTrue(
            $relatedSet instanceof ResultSet
        );
        $this->assertTrue(
            $relatedSet->current() instanceof OperationDemoEntity
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

        $builder = new Builder($parser, $typeBag);
        $builder->setRelationQueryBuilder(new RelationQueryBuilder());

        return $builder;
    }

    public function testBuild_entityMethod()
    {
        $builder = new DemoRelatedRepo($this->getBuilder());

        $query = $builder->demoFetch();

        $this->assertEquals(
            '`invoice_id` , `customer_id` `operation`.`operation_id` , `operation`.`name` FROM `invoice`  INNER JOIN `operation` USING(`operation_id`) WHERE   `invoice_id` = ?',
            $query->getQuery()
        );
    }
}
