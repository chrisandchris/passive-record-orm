<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ConcreteModel;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ConcreteModelTest
 * @version    3
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ConcreteModelTest extends TestKernel
{

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
            } catch (InvalidOptionException $exception) {
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
            } catch (InvalidOptionException $exception) {
            }
        }
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

    public function testIsOnlyOption()
    {
        $model = $this->getModel();

        $this->assertTrue(
            $model->isOnlyOption([
                'option1' => 1,
            ], 'option1')
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => null,
            ], 'option1')
        );
        $this->assertTrue(
            $model->isOnlyOption([
                'option1' => 1,
                'option2' => null,
            ], 'option1')
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => null,
                'option2' => null,
            ], 'option1')
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => null,
                'option2' => 123,
            ], 'option1')
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => null,
                'option2' => 123,
            ], 'option1', ['option2'])
        );
        $this->assertTrue(
            $model->isOnlyOption([
                'option1' => 133,
                'option2' => 123,
            ], 'option1', ['option2'])
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => 133,
                'option2' => 123,
                'option3' => 123,
            ], 'option1', ['option2'])
        );
        $this->assertFalse(
            $model->isOnlyOption([
                'option1' => null,
                'option2' => 123,
                'option3' => 123,
            ], 'option1', ['option2'])
        );
    }
}
