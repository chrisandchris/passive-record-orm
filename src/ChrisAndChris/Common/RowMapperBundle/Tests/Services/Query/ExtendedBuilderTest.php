<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TypeNotFoundException;

/**
 * Does more complex tests
 *
 * @name ExtendedBuilderTest
 * @version   1
 * @since     v2.0.2
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ExtendedBuilderTest extends AbstractBuilderTest {

    public function testSelect() {
        $builder = $this->getBuilder();

        $builder->select();
        $this->assertEquals(
            'SELECT', $builder->getSqlQuery()
                              ->getQuery()
        );
    }

    public function testAlias() {
        $builder = $this->getBuilder();

        $builder->alias('alias');
        $this->assertEquals(
            'as `alias`', $builder->getSqlQuery()
                                  ->getQuery()
        );
    }

    public function testIf() {
        $builder = $this->getBuilder();

        $builder->_if(true)
                ->select()
                ->_end()
                ->value(1);
        $this->equals('SELECT ?', $builder);

        $builder = $this->getBuilder();
        $builder->_if(false)
                ->select()
                ->_end()
                ->value(1);
        $this->equals('?', $builder);

        $builder = $this->getBuilder();
        $builder->_if(
            function () {
                return true;
            }
        )
                ->select()
                ->_end();

        $this->equals('SELECT', $builder);

        $builder = $this->getBuilder();
        $builder->_if(
            function () {
                return false;
            }
        )
                ->select()
                ->_end();

        $this->equals('', $builder);

        $builder = $this->getBuilder();
        try {
            $builder->_end();
            $this->fail('Must fail due to never opened if');
        } catch (MalformedQueryException $e) {
            // ignore
        }
    }

    public function testElse() {
        $builder = $this->getBuilder();

        $builder->_if(true)
                ->select()
                ->_else()
                ->value(1)
                ->_end();

        $this->equals('SELECT', $builder);

        $builder = $this->getBuilder();

        $builder->_if(false)
                ->value(1)
                ->_else()
                ->select()
                ->_end();

        $this->equals('SELECT', $builder);

        $builder = $this->getBuilder();
        try {
            $builder->_else();
            $this->fail('Must fail due not never opened if');
        } catch (MalformedQueryException $e) {
            // ignore
        }
    }

    public function testNestedIf() {
        $builder = $this->getBuilder();

        // @formatter:off
        $builder->_if(true)
                ->raw('1')
                    ->_if(true)
                        ->raw('2')
                        ->_if(true)
                            ->raw('3')
                            ->_if(false)
                                ->raw('4')
                            ->_end()
                        ->_end()
                    ->_end()
                ->_end();
        // @formatter:on

        $this->equals('1 2 3', $builder);

        $builder = $this->getBuilder();
        // @formatter:off
        $builder->_if(true)
                ->raw('1')
                    ->_if(true)
                        ->raw('2')
                        ->_if(false)
                            ->raw('3')
                            ->_if(true)
                                ->raw('4')
                            ->_else()
                                ->raw('5')
                            ->_end()
                        ->_else()
                            ->raw('6')
                        ->_end()
                    ->_else()
                        ->raw('7')
                    ->_end()
                ->_else()
                    ->raw('8')
                ->_end();
        // @formatter:on

        $this->equals('1 2 6', $builder);
    }

    public function testTypeNotFound() {
        $builder = $this->getBuilder();

        try {
            $builder->custom('unknowntype');
            $this->fail('Must fail due to unknown type');
        } catch (TypeNotFoundException $e) {
            // ignore
        }
    }

    public function testMissingParams() {
        $builder = $this->getBuilder();

        try {
            $builder->custom('value');
            $this->fail('Must fail due to missing parameter');
        } catch (MissingParameterException $e) {
            // ignore
        }

        $builder = $this->getBuilder();

        try {
            $builder->value(null);
        } catch (MissingParameterException $e) {
            $this->fail('Must not fail due to existing (but null) parameter');
        }
    }

    public function testClosure() {
        $builder = $this->getBuilder();

        $builder->value(
            function () {
                return 1;
            }
        );

        $query = $builder->getSqlQuery();
        $this->assertEquals('?', $query->getQuery());
        $this->assertEquals(1, count($query->getParameters()));
        $this->assertEquals(1, $query->getParameters()[0]);
    }

    public function testParamReplacement() {
        $builder = $this->getBuilder();

        $builder->table('table');
        $this->equals('FROM `table`', $builder);
    }

    public function testValuesStatement() {
        $builder = $this->getBuilder();
        $fieldValues = [
            [
                'Jordan',
                'John',
                'Alameda',
                'San Jose',
            ],
        ];

        $builder->values($fieldValues);
        $query = $builder->getSqlQuery()
                         ->getQuery();
        $this->assertEquals('VALUES (   ? , ? , ? , ?  )', $query);

        $builder = $this->getBuilder();

        $fieldValues = [
            [
                'Jordan',
                'John',
                'Alameda',
                'San Jose',
            ],
            [
                'Simon',
                'Peter',
                'Alameda',
                'San Jose',
            ],
        ];

        $builder->values($fieldValues);
        $query = $builder->getSqlQuery()
                         ->getQuery();
        $this->assertEquals('VALUES (   ? , ? , ? , ?  )  , (   ? , ? , ? , ?  )', $query);
    }

    public function testValuesInvalidInput() {
        $builder = $this->getBuilder();
        $fieldValues = [
            null,
        ];

        try {
            $builder->values($fieldValues);
            $this->fail('Must fail due to invalid input [no array]');
        } catch (MalformedQueryException $e) {
        }

        $fieldValues = [
            [

            ],
        ];

        try {
            $builder->values($fieldValues);
            $this->fail('Must fail due to invalid input [array to small]');
        } catch (MalformedQueryException $e) {
        }
    }

    public function testAppendMultiple() {
        $builder = $this->getBuilder();
        $array = [0];
        $builder->each(
            $array,
            function () {
                $builder = $this->getBuilder();
                $builder->field('field')
                        ->field('1');

                return $builder;
            }
        );
        $query = $builder->getSqlQuery()
                         ->getQuery();
        $this->assertEquals('`field` `1`', $query);

        $builder = $this->getBuilder();
        $array = [0];
        $builder->each(
            $array,
            function () {
                return [
                    [
                        'type'   => 'field',
                        'params' => [
                            'identifier' => 'field1',
                        ],
                    ],
                    [
                        'type'   => 'field',
                        'params' => [
                            'identifier' => 'field2',
                        ],
                    ],
                ];
            }
        );
        $query = $builder->getSqlQuery()
                         ->getQuery();
        $this->assertEquals('`field1` `field2`', $query);
    }

    public function testAppendMultipleWrongInput() {
        $builder = $this->getBuilder();
        $array = [0];
        try {
            $builder->each(
                $array,
                function () {
                    return [
                        [
                            'type' => 'field',
                        ],
                        [
                            'type'   => 'field',
                            'params' => [
                                'identifier' => 'field2',
                            ],
                        ],
                    ];
                }
            );
            $this->fail('Must fail due to invalid input');
        } catch (MalformedQueryException $e) {
        }

        $builder = $this->getBuilder();
        $array = [0];
        try {
            $builder->each(
                $array,
                function () {
                    return [
                        [
                            'type' => 'field',
                        ],
                    ];
                }
            );
            $this->fail('Must fail due to invalid input');
        } catch (MalformedQueryException $e) {
        }

        $builder = $this->getBuilder();
        $array = [0];
        try {
            $builder->each(
                $array,
                function () {
                    return null;
                }
            );
            $this->fail('Must fail due to invalid input');
        } catch (MalformedQueryException $e) {
        }
    }
}
