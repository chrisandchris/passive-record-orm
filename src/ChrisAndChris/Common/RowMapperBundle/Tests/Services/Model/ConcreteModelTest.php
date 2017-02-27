<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\NotCapableException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ConcreteModelTest
 * @version   1.0.0
 * @since     2.1.3
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ConcreteModelTest extends TestKernel
{

    public function testCalcRows_notCapable()
    {
        $model = $this->getModel();

        $queryMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery')
                          ->disableOriginalConstructor()
                          ->getMock();

        $queryMock->method('isCalcRowCapable')
                  ->willReturn(false);

        $queryMock->method('getParameters')
                  ->willReturn([]);

        $model->runSimple($queryMock);

        try {
            $model->getFoundRowCount();
            $this->fail('Must fail due to missing calc row capability');
        } catch (NotCapableException $e) {
            // ignore
        }
    }

    public function getModel($calcRowCapable = false, SqlQuery $queryMock = null)
    {
        $dependencyMock =
            $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider')
                 ->disableOriginalConstructor()
                 ->getMock();

        $mapperMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper')
                           ->disableOriginalConstructor()
                           ->getMock();

        $mapperMock->method('mapFromResult')
                   ->willReturn([]);

        $dependencyMock->method('getMapper')
                       ->willReturn($mapperMock);

        $pdoMock = $this->getMockBuilder('\PDO')
                        ->disableOriginalConstructor()
                        ->getMock();

        $statementMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement')
                              ->disableOriginalConstructor()
                              ->getMock();

        $statementMock->method('isCalcRowCapable')
                      ->willReturn($calcRowCapable);

        $pdoMock->method('prepare')
                ->willReturn($statementMock);

        $dependencyMock->method('getPdo')
                       ->willReturn($pdoMock);

        $errorHandlerMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler')
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $errorHandlerMock->method('handle')
                         ->willReturn(false);

        $dependencyMock->method('getErrorHandler')
                       ->willReturn($errorHandlerMock);

        $builderMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder')
                            ->disableOriginalConstructor()
                            ->getMock();

        $methods = ['select', 'f', 'close'];
        foreach ($methods as $method) {
            $builderMock->method($method)
                        ->willReturn($builderMock);
        }

        $builderMock->method('getSqlQuery')
                    ->willReturn($queryMock);

        $dependencyMock->method('getBuilder')
                       ->willReturn($builderMock);

        return new ConcreteModel(
            $dependencyMock
        );
    }

    public function testCalcRows_capable()
    {
        $queryMock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery')
                          ->disableOriginalConstructor()
                          ->getMock();

        $queryMock->method('isCalcRowCapable')
                  ->willReturn(true);

        $queryMock->method('getParameters')
                  ->willReturn([]);

        $this->assertTrue(
            $queryMock->isCalcRowCapable()
        );

        $model = $this->getModel(true, $queryMock);

        $model->runSimple($queryMock);
        $model->getFoundRowCount();
    }
}
