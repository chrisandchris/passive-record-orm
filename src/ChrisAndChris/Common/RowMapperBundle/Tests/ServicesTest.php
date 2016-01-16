<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests;

/**
 * @name ServicesTest
 * @version   1
 * @since     v1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ServicesTest extends TestKernel
{

    public function testServices()
    {
        $container = $this->container;

        $services = [
            'common_rowmapper.dependencyprovider'    => 'ModelDependencyProvider',
            'common_rowmapper.pdolayer'              => 'PdoLayer',
            'common_rowmapper.rowmapper'             => 'RowMapper',
            'common_rowmapper.rowmapperfactory'      => 'RowMapperFactory',
            'common_rowmapper.errorhandler'          => 'ErrorHandler',
            'common_rowmapper.querybuilder'          => 'Builder',
            // 'common_rowmapper.querybuilderfactory'   => 'BuilderFactory',
            'common_rowmapper.defaultparser'         => 'DefaultParser',
            'common_rowmapper.typebag'               => 'TypeBag',
            'common_rowmapper.snippetbag'            => 'SnippetBag',
            'common_rowmapper.utility.mapper'        => 'DatabaseMapper',
            'common_rowmapper.utility.cache_writer'  => 'CacheWriter',
            'common_rowmapper.mapping.repository'    => 'MappingRepository',
            'common_rowmapper.search.result_utility' => 'SearchResultUtility',
            'common_rowmapper.search.query_builder'  => 'SearchQueryBuilder',
            'common_rowmapper.mapping.validator'     => 'MappingValidator',
            'common_rowmapper.model'                 => 'ConcreteModel',
        ];

        foreach ($services as $service => $className) {
            $class = new \ReflectionClass($container->get($service));
            $this->assertEquals($className, $class->getShortName());
        }
    }
}
