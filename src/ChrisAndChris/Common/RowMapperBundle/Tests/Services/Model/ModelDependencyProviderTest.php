<?php

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name ModelDependencyProviderTest
 * @version    1
 * @since      v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ModelDependencyProviderTest extends TestKernel
{

    public function testGetter()
    {
        $provider = $this->getProvider();
        $matches = [
            [$provider->getPdo(), '\PDO'],
            [
                $provider->getBuilder(),
                'ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder',
            ],
            [
                $provider->getErrorHandler(),
                'ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler',
            ],
        ];
        foreach ($matches as $match) {
            $this->assertTrue($match[0] instanceof $match[1], sprintf(
                'expected to be instance of %s, but was %s',
                $match[1],
                get_class($match[0])
            ));
        }
    }

    public function getProvider()
    {
        $pdo = $this->getMockBuilder('\PDO')
                    ->disableOriginalConstructor()
                    ->getMock();

        $pdoFactory =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoFactory')
                 ->disableOriginalConstructor()
                 ->getMock();

        $pdoFactory->method('getPdo')
                   ->willReturn($pdo);

        $mapperFactory =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory')
                 ->disableOriginalConstructor()
                 ->getMock();
        $mapper =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper')
                 ->disableOriginalConstructor()
                 ->getMock();
        $mapperFactory->method('getMapper')
                      ->willReturn($mapper);
        $errorHandler =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler')
                 ->disableOriginalConstructor()
                 ->getMock();
        $builderFactory =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory')
                 ->disableOriginalConstructor()
                 ->getMock();
        $builder =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder')
                 ->disableOriginalConstructor()
                 ->getMock();
        $builderFactory->method('createBuilder')
                       ->willReturn($builder);
        $container =
            $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')
                 ->disableOriginalConstructor()
                 ->getMock();
        $searchResultUtility =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities\SearchResultUtility')
                 ->disableOriginalConstructor()
                 ->getMock();
        $container->method('get')
                  ->will(
                      $this->returnValueMap([
                          [
                              'common_rowmapper.search.result_utility',
                              $searchResultUtility,
                          ],
                      ])
                  );
        $dispatcher =
            $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcher')
                 ->disableOriginalConstructor()
                 ->getMock();

        /** @var Builder $builder */
        /** @var ContainerInterface $container */
        /** @var \PDO $pdo */
        /** @var RowMapperFactory $mapperFactory */
        /** @var ErrorHandler $errorHandler */
        /** @var EventDispatcherInterface $dispatcher */
        /** @var BuilderFactory $builderFactory */
        return new ModelDependencyProvider(
            $pdoFactory,
            $mapperFactory,
            $errorHandler,
            $builderFactory,
            $container,
            $dispatcher);
    }
}
