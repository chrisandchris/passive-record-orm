<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Executors;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\EncryptionExecutorInterface;
use Defuse\Crypto\Crypto;

/**
 * @name StringBasedExecutor
 * @version
 * @since
 * @package
 * @subpackage
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
 */
class StringBasedExecutor implements EncryptionExecutorInterface {

    /** @var Crypto */
    private $crypto;
    private $key;

    /**
     * @param Crypto $crypto
     */
    public function __construct(Crypto $crypto) {
        $this->crypto = $crypto;
    }

    /**
     * @inheritDoc
     */
    public function decrypt($encryptedValue) {
        return $this->crypto->decrypt($encryptedValue, $this->key);
    }

    /**
     * @inheritDoc
     */
    public function encrypt($decryptedValue) {
        return $this->crypto->encrypt($decryptedValue, $this->key);
    }

    /**
     * @inheritDoc
     */
    public function useKey($key) {
        $this->key = $key;
    }

}
