<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name AbstractBuilderTest
 * @since   v2.0.3
 * @package RowMapperBundle
 */
abstract class AbstractBuilderTest extends TestKernel {

    protected function getBuilder() {
        $typeBag = new TypeBag();
        $snippetBag = new SnippetBag();

        return new Builder(new DefaultParser($snippetBag), $typeBag);
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
