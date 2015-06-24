<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name PdoStatementTest
 * @version   1
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class PdoStatementTest extends TestKernel {

    public function testGetMeta() {
        $Statement = new PdoStatement();
        $meta = $Statement->getMeta();
        $this->assertTrue(is_array($meta));
        $this->assertTrue(array_key_exists('query', $meta));
        $this->assertTrue(array_key_exists('params', $meta));
    }
}
