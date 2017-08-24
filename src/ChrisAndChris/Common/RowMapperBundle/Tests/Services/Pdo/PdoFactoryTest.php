<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapper\ChrisAndChris\Common\RowMapperBundle\Tests\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoFactory;

/**
 * The PdoFactory returns connections from a pool based on read or write
 * preference
 *
 * @name PdoFactory
 * @version   1.1.1
 * @since     v2.4
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 *
 * @covers    ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoFactory
 */
class PdoFactoryTests extends \PHPUnit_Framework_TestCase
{

    public function testGetPdo()
    {
        $pdo =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer')
                 ->disableOriginalConstructor()
                 ->getMock();

        $factory = new PdoFactory($pdo, [
            'read'  => [
                ['sqlite', 'sqlite_1.db', null, null, null, null],
            ],
            'write' => [
                ['sqlite', 'sqlite_2.db', null, null, null, null],
            ],
        ]);

        $this->assertEquals(
            2,
            $factory->getWritePoolCount()
        );
        $this->assertEquals(
            1,
            $factory->getReadPoolCount()
        );
    }
}
