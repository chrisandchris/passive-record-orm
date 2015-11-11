<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Mapper\Encryption;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionExecutorInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionServiceInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Executors\StringBasedExecutor;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Services\DefaultEncryptionService;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Defuse\Crypto\Crypto;

/**
 * @name EncryptedQueryTest
 * @version   1.0.0
 * @since     v2.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChirs
 * @link      https://github.com/chrisandchris
 */
class EncryptedQueryTest extends TestKernel {

    public function testEncryptedQuerySingleFieldBased() {
        $encryptionService = new DefaultEncryptionService();
        $executor = new StringBasedExecutor(new Crypto());
        $executor->useKey((new Crypto())->createNewRandomKey());

        $encryptionService->useForField('name', $executor);
        $encryptionService->useForField('street', $executor);

        $entity = new DemoEntity();
        $entity->name = 'Mr. Jones';
        $entity->street = '1st Street';
        $entity->doNotEncrypt = 'San Francisco is cool';

        /** @var DemoEntity $entityEncrypted */
        $entityEncrypted = $encryptionService->encrypt($entity);
        $this->assertNotEquals('Mr. Jones', $entityEncrypted->name);
        $this->assertNotEquals('1st Street', $entityEncrypted->street);
        $this->assertEquals('San Francisco is cool', $entityEncrypted->doNotEncrypt);

        /** @var DemoEntity $entityDecrypted */
        $entityDecrypted = $encryptionService->decrypt($entity);
        $this->assertEquals('Mr. Jones', $entityDecrypted->name);
        $this->assertEquals('1st Street', $entityDecrypted->street);
        $this->assertEquals('San Francisco is cool', $entityDecrypted->doNotEncrypt);
    }

    public function testEncryptedQueryMultipleFieldBased() {
        /** @var EncryptionServiceInterface $encryptionService */
        $encryptionService = new DefaultEncryptionService();
        /** @var EncryptionExecutorInterface $executor */
        $executor = new StringBasedExecutor(new Crypto());
        $executor->useKey((new Crypto())->createNewRandomKey());

        $encryptionService->useForField(['name', 'street'], $executor);

        $entity = new DemoEntity();
        $entity->name = 'Mr. Jones';
        $entity->street = '1st Street';
        $entity->doNotEncrypt = 'San Francisco is cool';

        /** @var DemoEntity $entityEncrypted */
        $entityEncrypted = $encryptionService->encrypt($entity);
        $this->assertNotEquals('Mr. Jones', $entityEncrypted->name);
        $this->assertNotEquals('1st Street', $entityEncrypted->street);
        $this->assertEquals('San Francisco is cool', $entityEncrypted->doNotEncrypt);

        /** @var DemoEntity $entityDecrypted */
        $entityDecrypted = $encryptionService->decrypt($entity);
        $this->assertEquals('Mr. Jones', $entityDecrypted->name);
        $this->assertEquals('1st Street', $entityDecrypted->street);
        $this->assertEquals('San Francisco is cool', $entityDecrypted->doNotEncrypt);
    }

    public function testEncryptedQueryRowBased() {
        /** @var EncryptionServiceInterface $encryptionService */
        $encryptionService = new DefaultEncryptionService();
        /** @var EncryptionExecutorInterface $executor */
        $executor = new StringBasedExecutor(new Crypto());
        $executor->useKey((new Crypto())->createNewRandomKey());

        $encryptionService->useForRow($executor, ['doNotEncrypt']);

        $entity = new DemoEntity();
        $entity->name = 'Mr. Jones';
        $entity->street = '1st Street';
        $entity->doNotEncrypt = 'San Francisco is cool';

        /** @var DemoEntity $entityEncrypted */
        $entityEncrypted = $encryptionService->encrypt($entity);
        $this->assertNotEquals('Mr. Jones', $entityEncrypted->name);
        $this->assertNotEquals('1st Street', $entityEncrypted->street);
        $this->assertEquals('San Francisco is cool', $entityEncrypted->doNotEncrypt);

        /** @var DemoEntity $entityDecrypted */
        $entityDecrypted = $encryptionService->decrypt($entity);
        $this->assertEquals('Mr. Jones', $entityDecrypted->name);
        $this->assertEquals('1st Street', $entityDecrypted->street);
        $this->assertEquals('San Francisco is cool', $entityDecrypted->doNotEncrypt);
    }
}

class DemoEntity implements Entity {

    /** @var string */
    public $name;
    /** @var string */
    public $street;
    /** @var string */
    public $doNotEncrypt;
}
