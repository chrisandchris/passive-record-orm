<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption;

/**
 * @name EncryptionExecutorInterface
 * @version   1.0.0
 * @since     v2.1.0
 * @package   RowMapperbundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
interface EncryptionExecutorInterface {

    /**
     * Decrypt the given value
     *
     * @param string $encryptedValue the encrypted value
     * @return string a decrypted value
     */
    public function decrypt($encryptedValue);

    /**
     * Encrypt the given value
     *
     * @param string $decryptedValue decrypted value
     * @return string an encrypted value
     */
    public function encrypt($decryptedValue);

    /**
     * Set a key used for encryption and decryption
     *
     * @param string|\Closure $key they key to use to encrypt/decrypt
     */
    public function useKey($key);
}
