<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ModelMock
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
abstract class ModelMock extends TestKernel
{

    /**
     * @return Model
     */
    public function getModel()
    {
        $model = $this->getMockForAbstractClass('ChrisAndChris\Common\RowMapperBundle\Services\Model\Model',
            [
                $this->getDependencyMock(),
            ]
        );

        return $model;
    }

    private function getDependencyMock()
    {
        $mock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider')
                     ->disableOriginalConstructor()
                     ->getMock()
                     ->method('getPdo')
                     ->willReturnCallback(function () {
                         $this->getPdoMock();
                     })
                     ->method('getMapper')
                     ->willReturnCallback(function () {
                         $this->getMapperMock();
                     });

        return $mock;
    }

    private function getPdoMock()
    {
        $mock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer')
                     ->disableOriginalConstructor()
                     ->getMock();

        return $mock;
    }

    private function getMapperMock()
    {
        $mock = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper')
                     ->disableOriginalConstructor()
                     ->getMock();

        return $mock;
    }
}
