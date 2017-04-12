<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Entity\Search\SearchContainer;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingValidator;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities\SearchQueryBuilder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name SearchQueryBuilderTest
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchQueryBuilderTest extends TestKernel
{

    public function testSimple()
    {
        $builder = $this->getQueryBuilder();

        $container = new SearchContainer('right', 'term%');
        $container = $builder->buildSearchContainer($container);

        $this->assertNull($container->getSearchId());
        $this->assertEquals('term%', $container->getTerm());
        $this->assertEquals(0, count($container->getFilterConditions()));
        $this->assertEquals(1, count($container->getJoinedTables()));
        $this->assertEquals('right', $container->getRootTable());
        $this->assertEquals('right', $container->targetTable);
        $this->assertEquals('right_id', $container->primaryKey);
    }

    private function getQueryBuilder($mapping = 'demo_mapping.json')
    {
        $repository = new MappingRepository(__DIR__ . '/../Mapping', '.', $mapping);
        $builder = new SearchQueryBuilder(
            new BuilderFactory(new DefaultParser(new SnippetBag()), new TypeBag()),
            $repository,
            new MappingValidator($repository)
        );

        return $builder;
    }

    public function testWithRelations()
    {
        $builder = $this->getQueryBuilder();

        $container = new SearchContainer('role_right', 'term%');
        $container = $builder->buildSearchContainer($container);

        $this->assertNull($container->getSearchId());
        $this->assertEquals('term%', $container->getTerm());
        $this->assertEquals(0, count($container->getFilterConditions()));
        $this->assertEquals(2, count($container->getJoinedTables()));
        $this->assertEquals('role_right', $container->getRootTable());
        $this->assertEquals('role_right', $container->targetTable);
        $this->assertEquals('role_id', $container->primaryKey);
    }

    public function testTableAlias()
    {
        $builder = $this->getQueryBuilder('demo_mapping_for_alias.json');

        $container = new SearchContainer('right', 'term%');
        $container = $builder->buildSearchContainer($container);

        $this->assertNull($container->getSearchId());
        $this->assertEquals('term%', $container->getTerm());
        $this->assertEquals(0, count($container->getFilterConditions()));
        $this->assertEquals(2, count($container->getJoinedTables()));
        $this->assertEquals('right', $container->getRootTable());
        $this->assertEquals('right', $container->targetTable);
        $this->assertEquals('right_id', $container->primaryKey);

        $query = $builder->buildSearchQuery($container, function () {
            return 1;
        });
    }
}
