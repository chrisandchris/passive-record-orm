<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ConcreteModelTest
 * @version    2
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ConcreteModelTest extends TestKernel
{

    public function testValidateOffset()
    {
        $this->assertEquals(0, $this->getModel()
                                    ->validateOffset(-5));
        $this->assertEquals(0, $this->getModel()
                                    ->validateOffset(0));
        $this->assertEquals(5, $this->getModel()
                                    ->validateOffset(5));
        $this->assertEquals(5, $this->getModel()
                                    ->validateOffset(5.254));
        $this->assertEquals(5, $this->getModel()
                                    ->validateOffset(5.9));
    }

    /**
     * @return ConcreteModel
     */
    private function getModel()
    {
        /** @var ModelDependencyProvider $provider */
        $provider = $this->getMockBuilder('ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider')
                         ->disableOriginalConstructor()
                         ->getMock();

        $model = new ConcreteModel($provider);

        return $model;
    }

    public function testValidateLimit()
    {
        $this->assertEquals(1, $this->getModel()
                                    ->validateLimit(-5));
        $this->assertEquals(1, $this->getModel()
                                    ->validateLimit(0));
        $this->assertEquals(1, $this->getModel()
                                    ->validateLimit(1));
        $this->assertEquals(1, $this->getModel()
                                    ->validateLimit(1.5));
        $this->assertEquals(99, $this->getModel()
                                     ->validateLimit(99.9));
        $this->assertEquals(50, $this->getModel()
                                     ->validateLimit(100, 50));
    }

    public function testSetRunningUser()
    {
        $model = $this->getModel();
        $model->setRunningUser('alpha');

        $model = $this->getModel();
        $this->assertEquals('alpha', $model->getRunningUser());
    }

    public function testPrepareOptions()
    {
        $model = $this->getModel();

        $options = [
            [
                'offset' => 10,
            ],
            [
                'offset'    => 10,
                'articleId' => 50,
            ],
            [
                'articleId' => 10,
            ],
            [
                'offset' => 50,
                'limit'  => 1000,
            ],
        ];
        foreach ($options as $option) {
            try {
                $model->prepareOptions(
                    [
                        'offset',
                        'limit',
                        'articleId',
                    ],
                    $option
                );
            } catch (InvalidOptionException $E) {
                $this->fail('Must not fail due to correct options');
            }
        }

        $options = [
            [
                'offset'     => 10,
                'nulloption' => false,
            ],
            [
                'offset'    => 10,
                'articleDd' => 50,
            ],
            [
                'idArticle' => 10,
            ],
            [
                'offset'   => 50,
                'limmmmit' => 1000,
            ],
        ];
        foreach ($options as $option) {
            try {
                $model->prepareOptions(
                    [
                        'offset',
                        'limit',
                        'articleId',
                    ],
                    $option
                );
                $this->fail('Must fail due to incorrect options');
            } catch (InvalidOptionException $E) {
            }
        }
    }
}
