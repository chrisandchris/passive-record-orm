<?php

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\BagInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\MySqlBag;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\PgSqlBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * This test class contains global tests, that every bag must fulfill
 *
 * @name GeneralBagTest
 * @version    1.0.0
 * @since      v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class GeneralBagTest extends TestKernel
{

    public function testBags()
    {
        /*
         * Tests bags for desired behaviour, they must return a string to fulfil the test
         */
        /** @var BagInterface $bag */
        foreach ($this->getBagsToTest() as $bag) {
            foreach ($this->getTests() as $test) {
                try {
                    $f = $bag->get($test[0]);
                    $result = $f($test[1]);
                    $this->assertTrue(
                        is_array($result),
                        sprintf(
                            'must return array to fulfill test, but did %s',
                            gettype($result)
                        )
                    );
                    $this->assertTrue(array_key_exists('code', $result), 'must have key "code"');
                    $this->assertTrue(array_key_exists('params', $result), 'must have key "params"');
                } catch (\Exception $exception) {
                    if ((isset($test[2]) && !($exception instanceof $test[2])) || !isset($test[2])) {
                        throw $exception;
                    }
                }
            }
        }
    }

    public function getBagsToTest()
    {
        return [
            new MySqlBag(),
            new PgSqlBag(),
        ];
    }

    public function getTests()
    {
        return [
            [ // must understand array as table param
              'join',
              [
                  'table' => ['schema', 'table'],
                  'alias' => null,
                  'type'  => 'inner',
              ],
            ],
        ];
    }
}
