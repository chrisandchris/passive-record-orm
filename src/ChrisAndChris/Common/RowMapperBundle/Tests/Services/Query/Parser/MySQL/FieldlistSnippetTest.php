<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL\FieldlistSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\FieldlistType;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name FieldlistSnippetTest
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author ChrisAndChris
 * @link   https://github.com/chrisandchris/symfony-rowmapper
 */
class FieldlistSnippetTest extends TestKernel {

    public function testGetFields() {
        $this->assertEquals('`field1`', $this->getFields(['field1']));
        $this->assertEquals('`tableA`.`field1`', $this->getFields(['tableA:field1']));
        $this->assertEquals('`field1`, `field2`', $this->getFields(['field1', 'field2']));
        $this->assertEquals('`tableA`.`field1`, `tableB`.`field2`', $this->getFields([
            'tableA:field1',
            'tableB:field2'
        ]));
    }

    public function getFields(array $input) {
        $FieldlistSnippet = new FieldlistSnippet();
        $FieldlistSnippet->setType(
            new FieldlistType($input)
        );

        return trim($FieldlistSnippet->getFields());
    }

    public function testGetFieldsWithAlias() {
        $this->assertEquals('`field1` as `alias`', $this->getFields(['field1' => 'alias']));
        $this->assertEquals('`tableA`.`field1` as `alias`', $this->getFields(['tableA:field1' => 'alias']));
        $this->assertEquals('`field1` as `alias1`, `field2` as `alias2`',
            $this->getFields(['field1' => 'alias1', 'field2' => 'alias2']));
        $this->assertEquals('`tableA`.`field1` as `alias1`, `tableB`.`field2` as `alias2`', $this->getFields([
            'tableA:field1' => 'alias1',
            'tableB:field2' => 'alias2'
        ]));
    }
}
