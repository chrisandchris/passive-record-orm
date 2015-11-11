<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper\Encryption;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;

/**
 * @name EncryptionServiceInterface
 * @version   1.0.0
 * @since     v2.1.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
interface EncryptionServiceInterface {

    /**
     * Returns true if the encryption service is responsible for this entity
     * class
     *
     * @param Entity $entity
     * @return bool
     */
    public function isResponsible(Entity $entity);

    /**
     * Make this encryption service responsible for this entity class
     *
     * @param Entity $entity
     */
    public function makeResponsible(Entity $entity);

    /**
     * Use the executor for any field in a row, disallow fields using $fields
     *
     * @param EncryptionExecutorInterface $executor
     * @param array                       $disallowFields
     * @return mixed
     */
    public function useForRow(EncryptionExecutorInterface $executor, array $disallowFields = null);

    /**
     * Assign an executor to a specific field (or multiple fields)
     *
     * @param string|array                $field
     * @param EncryptionExecutorInterface $executor
     * @return mixed
     */
    public function useForField($field, EncryptionExecutorInterface $executor);

    /**
     * Run the encryption
     *
     * @param Entity $entity
     * @return Entity
     */
    public function encrypt(Entity &$entity);

    /**
     * Run the decryption
     *
     * @param Entity $entity
     * @return Entity
     */
    public function decrypt(Entity &$entity);
}
