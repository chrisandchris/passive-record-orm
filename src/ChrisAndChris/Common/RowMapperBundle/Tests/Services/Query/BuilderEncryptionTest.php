<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Executors\StringBasedExecutor;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Services\DefaultEncryptionService;
use Defuse\Crypto\Crypto;

/**
 * @name BuilderEncryptionTest
 * @version   1.0.0
 * @since     v2.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class BuilderEncryptionTest extends AbstractBuilderTest {

    public function getEncryption(array $fields) {
        $encryptionService = new DefaultEncryptionService();
        $executor = new StringBasedExecutor(new Crypto());
        $executor->useKey((new Crypto())->createNewRandomKey());
        $encryptionService->useForField($fields, $executor);

        return $encryptionService;
    }

    public function test() {

    }
}
