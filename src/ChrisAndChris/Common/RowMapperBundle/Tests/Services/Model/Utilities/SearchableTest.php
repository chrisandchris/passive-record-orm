<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Entity\Search\FilterCondition;
use ChrisAndChris\Common\RowMapperBundle\Entity\Search\SearchContainer;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingValidator;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities\SearchQueryBuilder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name SearchableTest
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class SearchableTest extends TestKernel {

    public function testSearchByPattern() {
        $searchable = $this->getModel();

        $search = new SearchContainer('role_right', 'term');

        $search = $searchable->buildSearchContainer($search);
        $this->assertTrue($search instanceof SearchContainer);

        $query = $searchable->buildSearchQuery(
            $search, function () {
            return 1;
        });
        $this->assertTrue($query instanceof SqlQuery);
    }

    /**
     * @return SearchQueryBuilder
     */
    private function getModel() {
        $repo = new MappingRepository(__DIR__, '../Mapping', 'demo_mapping.json');
        $validator = new MappingValidator($repo);

        return new SearchQueryBuilder(new BuilderFactory(new DefaultParser(new SnippetBag()), new TypeBag()), $repo,
            $validator);
    }

    public function testSearchByPatternWithPreviousSearch() {
        $searchable = $this->getModel();

        $search = new SearchContainer('role_right', 'term', 1);
        $search->addFilterCondition(new FilterCondition('role', 'role_id', 5));

        $search = $searchable->buildSearchContainer($search);
        $this->assertTrue($search instanceof SearchContainer);

        $query = $searchable->buildSearchQuery(
            $search, function () {
            return 1;
        }
        );
        $this->assertTrue($query instanceof SqlQuery);
    }

    public function testSearchByPatternWithFilterConditions() {
        $searchable = $this->getModel();

        $search = new SearchContainer('role_right', 'term', 1);
        $search->addFilterCondition(new FilterCondition('role', 'role_id', 5));
        $search->addFilterCondition(new FilterCondition('role', 'role_id_2', 5));

        $search = $searchable->buildSearchContainer($search);
        $this->assertTrue($search instanceof SearchContainer);

        $query = $searchable->buildSearchQuery(
            $search, function () {
            return 1;
        }
        );
        $this->assertTrue($query instanceof SqlQuery);
    }
}
