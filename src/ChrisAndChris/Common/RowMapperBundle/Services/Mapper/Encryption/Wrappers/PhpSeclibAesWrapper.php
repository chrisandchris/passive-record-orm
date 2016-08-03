<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\Wrappers;

use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption\CryptoInterface;
use phpseclib\Crypt\AES;

/**
 * @name PhpSeclibAesWrapper
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class PhpSeclibAesWrapper implements CryptoInterface {

    /** @var AES */
    private $aes;

    /**
     * PhpSeclibAesWrapper constructor.
     *
     * @param AES $aes
     */
    public function __construct(AES $aes) {
        $this->aes = $aes;
    }

    /**
     * @inheritDoc
     */
    public function encrypt($decryptedValue, $key, $iv) {
        $this->aes->setKey($key);
        $this->aes->setIV($iv);

        return $this->aes->encrypt($decryptedValue);
    }

    /**
     * @inheritDoc
     */
    public function decrypt($encryptedValue, $key, $iv) {
        $this->aes->setKey($key);
        $this->aes->setIV($iv);

        return $this->aes->decrypt($encryptedValue);
    }


}

