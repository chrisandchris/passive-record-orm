<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Executors;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\CryptoInterface;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionExecutorInterface;

/**
 * @name StringBasedExecutor
 * @version    1.0.0
 * @lastChange v2.1.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class StringBasedExecutor implements EncryptionExecutorInterface {

    /** @var CryptoInterface */
    private $crypto;
    /** @var string encryption key */
    private $key;
    /** @var string initialization vector */
    private $iv;

    /**
     * @param CryptoInterface $crypto
     */
    public function __construct(CryptoInterface $crypto) {
        $this->crypto = $crypto;
    }

    /**
     * @inheritDoc
     */
    public function decrypt($encryptedValue) {
        return $this->crypto->decrypt($encryptedValue, $this->key, $this->iv);
    }

    /**
     * @inheritDoc
     */
    public function encrypt($decryptedValue) {
        return $this->crypto->encrypt($decryptedValue, $this->key, $this->iv);
    }

    /**
     * @inheritDoc
     */
    public function useKey($key, $iv) {
        $this->iv = $iv;
        $this->key = $key;
    }
}
