<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model\Utilities;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapperFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Utilities\Listable;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\BuilderFactory;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @name ListableTest
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ListableTest extends TestKernel {

    public function testList() {
        $listable = $this->getModel();

        $listable->showList(
            'foobar', [
            'foo',
            'bar',
        ], [
                'column_a' => 'filter value 1',
            ]
        );
    }

    private function getModel() {
        $provider = new ModelDependencyProvider(
            new PdoLayer('sqlite', 'sqlite.db'),
            new RowMapperFactory(new EventDispatcher()),
            new ErrorHandler(),
            new BuilderFactory(new DefaultParser(new SnippetBag()), new TypeBag())
        );

        $model = new Listable($provider);

        return $model;
    }
}
