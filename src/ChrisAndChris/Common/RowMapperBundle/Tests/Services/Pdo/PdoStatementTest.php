<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Pdo;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name PdoStatementTest
 * @version 1
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
