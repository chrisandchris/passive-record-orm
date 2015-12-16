<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Model\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchColumnException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\NoSuchTableException;
use ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\MappingRepository;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name MappingRepositoryTest
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class MappingRepositoryTest extends TestKernel {

    public function testHasColumns() {
        $mapping = $this->getRepository();

        $mapping->hasColumns('right', ['right_id']);
        $mapping->hasColumns('right', ['right_id', 'right_path']);
        $mapping->hasColumns('role', ['role_id']);

        try {
            $mapping->hasColumns('right', ['no such column']);
            $this->fail('Must fail due to no such column');
        } catch (NoSuchColumnException $exception) {
            // ignore
        }
    }

    public function getRepository() {
        $mapping = new MappingRepository(__DIR__, 'common_rowmapper');
        $mapping->setMapping(__DIR__ . '/demo_mapping.json');

        return $mapping;
    }

    public function testHasTable() {
        $mapping = $this->getRepository();

        $mapping->hasTable('right');
        $mapping->hasTable('role');

        try {
            $mapping->hasTable('no such table');
            $this->fail('Must fail due to no such table');
        } catch (NoSuchTableException $exception) {
            // ignore
        }
    }

    public function testCircularRelations() {
        $mapping = $this->getRepository();

        $relations = $mapping->getRecursiveRelations('right');
        $this->assertEquals(0, count($relations));

        $relations = $mapping->getRecursiveRelations('role_right');
        $this->assertEquals(3, count($relations));
    }

    public function testGetTable() {
        $mapping = $this->getRepository();

        $table = $mapping->getRawTable('right');
        $this->assertTrue(is_array($table['fields']));
        $this->assertTrue(is_array($table['relations']));

        try {
            $mapping->getRawTable('no such table');
            $this->fail('Must fail due to no such table');
        } catch (NoSuchTableException $exception) {
            // ignore
        }
    }
}
