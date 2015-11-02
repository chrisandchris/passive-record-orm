<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ErrorHandler;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Model;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\ModelDependencyProvider;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\TypeBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name ModelTest
 * @version   1
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ModelTest extends TestKernel {

    public function testValidateOffset() {
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
     * @return Model
     */
    private function getModel() {
        $provider = new ModelDependencyProvider(
            new PdoLayer('sqlite', 'sqlite.db'),
            new RowMapper(),
            new ErrorHandler(),
            new Builder(new DefaultParser(new SnippetBag()), new TypeBag())
        );

        $Model = new EmptyModel($provider);

        return $Model;
    }

    public function testValidateLimit() {
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

    public function testSetRunningUser() {
        $Model = $this->getModel();
        $Model->setRunningUser('alpha');

        $Model = $this->getModel();
        $this->assertEquals('alpha', $Model->getRunningUser());
    }

    public function testPrepareOptions() {
        $Model = $this->getModel();

        $options = [
            [
                'offset' => 10
            ],
            [
                'offset' => 10,
                'articleId' => 50
            ],
            [
                'articleId' => 10
            ],
            [
                'offset' => 50,
                'limit' => 1000
            ]
        ];
        foreach ($options as $option) {
            try {
                $Model->prepareOptions(
                    [
                        'offset',
                        'limit',
                        'articleId'
                    ],
                    $option
                );
            } catch (InvalidOptionException $E) {
                $this->fail('Must not fail due to correct options');
            }
        }

        $options = [
            [
                'offset' => 10,
                'nulloption' => false
            ],
            [
                'offset' => 10,
                'articleDd' => 50
            ],
            [
                'idArticle' => 10
            ],
            [
                'offset' => 50,
                'limmmmit' => 1000
            ]
        ];
        foreach ($options as $option) {
            try {
                $Model->prepareOptions(
                    [
                        'offset',
                        'limit',
                        'articleId'
                    ],
                    $option
                );
                $this->fail('Must fail due to incorrect options');
            } catch (InvalidOptionException $E) {
            }
        }
    }
}

class EmptyModel extends Model {

    // empty
}
