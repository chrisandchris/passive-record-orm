<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption;

/**
 * @name CryptoInterface
 * @version    1.0.0
 * @lastChange v2.1.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
interface CryptoInterface {

    /**
     * Encrypt a given value
     *
     * @param string $decryptedValue
     * @param string $key
     * @param string $vector
     * @return string
     */
    public function encrypt($decryptedValue, $key, $vector);

    /**
     * Decrypt a given value
     *
     * @param string $encryptedValue
     * @param string $key
     * @param string $vector
     * @return string
     * @internal param string $iv
     */
    public function decrypt($encryptedValue, $key, $vector);
}
