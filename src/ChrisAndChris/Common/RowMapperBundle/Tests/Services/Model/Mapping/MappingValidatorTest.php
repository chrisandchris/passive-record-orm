<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Field;
use ChrisAndChris\Common\RowMapperBundle\Entity\Mapping\Relation;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingValidator;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name MappingValidatorTest
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MappingValidatorTest extends TestKernel {

    public function testHasTable() {
        $validator = $this->getValidator();

        $validator->validateTables(
            [
                'right',
            ]
        );
    }

    /**
     * @return MappingValidator
     */
    private function getValidator() {
        $repo = new MappingRepository(__DIR__, 'common_rowmapper');
        $repo->setMapping(file_get_contents(__DIR__ . '/demo_mapping.json'));

        return new MappingValidator($repo);
    }

    public function testHasTableFailed() {
        $validator = $this->getValidator();

        try {
            $validator->validateTables(
                [
                    'no_such_table',
                ]
            );
            $this->fail('Must fail due to no such table');
        } catch (NoSuchTableException $exception) {
            // ignore
        }

        try {
            $validator->validateTables(
                [
                    'right',
                    'no_such_table',
                    'role_right',
                ]
            );
            $this->fail('Must fail due to no such table');
        } catch (NoSuchTableException $exception) {
            // ignore
        }
    }

    public function testValidateJoins() {
        $validator = $this->getValidator();

        $validator->validateJoins(
            'role_right', [
                new Relation('role_right', 'right', 'right_id', 'right_id'),
                new Relation('role_right', 'role', 'role_id', 'role_id'),
                new Relation('role', 'role_group', 'role_group_id', 'role_group_id'),
            ]
        );

        try {
            $validator->validateJoins(
                'role_right', [
                    new Relation('role_right', 'right', 'right_id', 'right_id'),
                    new Relation('role_right', 'missing table', 'right_id', 'missing field'),
                ]
            );
            $this->fail('Must fail due to no such table');
        } catch (NoSuchTableException $exception) {
            // ignore
        }
    }

    public function testValidateFields() {
        $validator = $this->getValidator();

        $validator->validateFields(
            'role_right', [
                new Field(null, 'role_id'),
                new Field(null, 'right_id'),
                new Field('role', 'role_id'),
                new Field('role_group', 'role_group_id'),
            ]
        );

        try {
            $validator->validateFields(
                'role_right', [
                    new Field(null, 'role_id'),
                    new Field(null, 'no such field'),
                ]
            );
            $this->fail('Must fail due to no such field');
        } catch (NoSuchColumnException $exception) {
            // ignore
        }

        try {
            $validator->validateFields(
                'role_right', [
                    new Field(null, 'role_id'),
                    new Field('table', 'no such field'),
                ]
            );
            $this->fail('Must fail due to no such field');
        } catch (NoSuchColumnException $exception) {
            // ignore
        }
    }
}
