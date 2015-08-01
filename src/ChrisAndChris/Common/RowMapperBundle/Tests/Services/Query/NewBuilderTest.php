<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name NewBuilderTest
 * @version   1
 * @since     v2.0.2
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class NewBuilderTest extends TestKernel {

    public function testSelect() {
        $builder = $this->getBuilder();

        $builder->select();
        $this->assertEquals(
            'SELECT', $builder->getSqlQuery()
                              ->getQuery()
        );
    }

    private function getBuilder() {
        $typeBag = new TypeBag();
        $snippetBag = new SnippetBag();

        return new Builder(new DefaultParser($snippetBag), $typeBag);
    }

    public function testAlias() {
        $builder = $this->getBuilder();

        $builder->alias('alias');
        $this->assertEquals(
            'as `alias`', $builder->getSqlQuery()
                                  ->getQuery()
        );
    }
}
