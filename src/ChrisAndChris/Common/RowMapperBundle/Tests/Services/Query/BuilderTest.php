<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQLParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name BuilderTest
 * @version 1.0.0
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class BuilderTest extends TestKernel {
    function testSimpleQuery() {
        $Builder = new Builder(new DefaultParser());
        $Builder->select()
            ->fieldlist(array(
                'field1' => 'aliasName',
                'field2'
            ))
            ->table('foobar')
            ->join('nexttable')
                ->using('usingField')
            ->join('newtable', 'left')
                ->on()
                    ->field(array('lefttable', 'leftfield'))
                    ->equals()->value('1')
                ->close()
            ->where()
                ->brace()
                    ->select()
                    ->fieldlist(array(
                        'subfield1'
                    ))
                    ->table('subtable')
                    ->limit(1)
                ->close()
                ->equals()->value(13)
                ->connect('&')->field('field1')
                ->equals()->value('value1')
                ->connect('&')
                ->brace()
                    ->field('field2')
                    ->equals()->value(-1)
                    ->connect('|')->field('field2')
                    ->equals()->field('field3')
                ->close()
            ->close()
            ->orderBy(array(
                'field1',
                'field2'
            ))
            ->limit(10)
        ;

        $query = $Builder->getSqlQuery();

        $this->assertTrue($query instanceof SqlQuery);
        $this->assertEquals(4, count($query->getParameters()));
    }

    /**
     * @return Builder
     */
    private function getBuilder() {
        $B = new Builder(new DefaultParser());
        return $B;
    }

    private function equals($expected, Builder $Builder) {
        $this->assertEquals($expected, $Builder->getSqlQuery()->getQuery());
    }
    public function testSelect() {
        $Builder = $this->getBuilder();
        $Builder->select();

        $this->equals('SELECT', $Builder);
    }

    public function testUpdate() {
        $B = $this->getBuilder();
        $B->update('table1');
        $this->equals('UPDATE `table1` SET', $B);
    }

    public function testInsert() {
        $B = $this->getBuilder();
        $B->insert('table1');
        $this->equals('INSERT INTO `table1`', $B);
    }

    public function testDelete() {
        $B = $this->getBuilder();
        $B->delete('table1');
        $this->equals('DELETE FROM `table1`', $B);
    }
    public function testTable() {
        $Builder = $this->getBuilder();
        $Builder->table('table');

        $this->equals('FROM `table`', $Builder);
    }

    public function testFieldlist() {
        $B = $this->getBuilder();
        $B->fieldlist(array(
            'field1'
        ));
        $this->equals('`field1`', $B);

        $B = $this->getBuilder();
        $B->fieldlist(array(
            'field1', 'field2'
        ));
        $this->equals('`field1`, `field2`', $B);

        $B = $this->getBuilder();
        $B->fieldlist(array(
            'field1' => 'alias1'
        ));
        $this->equals('`field1` as `alias1`', $B);

        $B = $this->getBuilder();
        $B->fieldlist(array(
            'field1' => 'alias1',
            'field2' => 'alias2'
        ));
        $this->equals('`field1` as `alias1`, `field2` as `alias2`', $B);

        $B = $this->getBuilder();
        $B->fieldlist(array(
            'field1' => 'alias1',
            'field2'
        ));
        $this->equals('`field1` as `alias1`, `field2`', $B);
    }

    public function testWhere() {
        $B = $this->getBuilder();
        $B->where()
            ->field('field1')->equals()->value('1')
        ->close();
        // be careful, two whitespaces after WHERE
        $this->equals('WHERE  `field1` = ?', $B);

        $B = $this->getBuilder();
        $B->where()
            ->field('field1')->equals()->value('1')
            ->connect()
            ->field('field2')->equals()->field('field3')
        ->close();

        $this->equals('WHERE  `field1` = ? AND `field2` = `field3`', $B);
    }

    public function testField() {
        $B = $this->getBuilder();
        $B->field('field1');
        $this->equals('`field1`', $B);

        $B = $this->getBuilder();
        $B->field(array('table', 'field'));
        $this->equals('`table`.`field`', $B);

        $B = $this->getBuilder();
        $B->field(array('database', 'table', 'field'));
        $this->equals('`database`.`table`.`field`', $B);
    }

    public function testEquals() {
        $B = $this->getBuilder();
        $B->equals();
        $this->equals('=', $B);
    }

    public function testValue() {
        $B = $this->getBuilder();
        $B->value('value1');
        $Query = $B->getSqlQuery();

        $this->assertEquals('?', $Query->getQuery());
        $this->assertEquals('value1', $Query->getParameters()[0]);
        $this->assertEquals(1, count($Query->getParameters()));

        // builder is empty after parsing
        $Query = $B->getSqlQuery();
        $this->assertEquals(0, strlen($Query->getQuery()));
        $this->assertEquals(0, count($Query->getParameters()));

    }

    public function testBrace() {
        $B = $this->getBuilder();
        $B->brace()->close();
        // got three whitespaces if nothing in it
        $this->equals('(   )', $B);
    }

    public function testLimit() {
        $B = $this->getBuilder();
        $B->limit(1);
        $this->equals('LIMIT 1', $B);

        $B = $this->getBuilder();
        $B->limit(123);
        $this->equals('LIMIT 123', $B);

        $B = $this->getBuilder();
        $B->limit(-1);
        $this->equals('LIMIT 1', $B);
    }

    public function testJoin() {
        $B = $this->getBuilder();
        $B->join('table1');
        $this->equals('INNER JOIN `table1`', $B);

        $B = $this->getBuilder();
        $B->join('table1', 'left');
        $this->equals('LEFT JOIN `table1`', $B);

        $B = $this->getBuilder();
        $B->join('table1', 'right');
        $this->equals('RIGHT JOIN `table1`', $B);
    }

    public function testUsing() {
        $B = $this->getBuilder();
        $B->using('field1');
        $this->equals('USING(`field1`)', $B);
    }

    public function testOn() {
        $B = $this->getBuilder();
        $B->on()->field('field1')->equals()->field('field2')->close();
        $this->equals('ON (  `field1` = `field2`  )', $B);

        $B = $this->getBuilder();
        $B->on()->field(array('t1', 'field1'))->equals()->field(array('t2', 'field2'))->close();
        $this->equals('ON (  `t1`.`field1` = `t2`.`field2`  )', $B);
    }

    public function testGroupBy() {
        $B = $this->getBuilder();
        $B->groupBy('field1');
        $this->equals('GROUP BY  `field1`', $B);

        $B = $this->getBuilder();
        $B->groupBy()->field('field1')->c()->field('field2')->close();
        $this->equals('GROUP BY  `field1` , `field2`', $B);
    }

    public function testOrder() {
        $B = $this->getBuilder();
        $B->order()->by('field1')->close();
        $this->equals('ORDER BY  `field1` DESC', $B);

        $B = $this->getBuilder();
        $B->order()->by('field1', 'asc')->close();
        $this->equals('ORDER BY  `field1` ASC', $B);

        $B = $this->getBuilder();
        $B->order()->by('field1', 'asc')->c()->by('field2')->close();
        $this->equals('ORDER BY  `field1` ASC , `field2` DESC', $B);
    }

    public function testOderBy() {
        $B = $this->getBuilder();
        $B->orderBy(array(
            'field1'
        ));
        $this->equals('ORDER BY  `field1` DESC', $B);

        $B = $this->getBuilder();
        $B->orderBy(array(
            'field1' => 'asc'
        ));
        $this->equals('ORDER BY  `field1` ASC', $B);

        $B = $this->getBuilder();
        $B->orderBy(array(
            'field1' => 'asc',
            'field2'
        ));
        $this->equals('ORDER BY  `field1` ASC , `field2` DESC', $B);
    }

    public function testConnect() {
        $B = $this->getBuilder();
        $B->connect('&');
        $this->equals('AND', $B);

        $B = $this->getBuilder();
        $B->connect('&&');
        $this->equals('AND', $B);

        $B = $this->getBuilder();
        $B->connect('aNd');
        $this->equals('AND', $B);

        $B = $this->getBuilder();
        $B->connect('|');
        $this->equals('OR', $B);

        $B = $this->getBuilder();
        $B->connect('||');
        $this->equals('OR', $B);

        $B = $this->getBuilder();
        $B->connect('oR');
        $this->equals('OR', $B);

        try {
            $B = $this->getBuilder();
            $B->connect('123');
            $this->fail('Must fail due to unknown connection type');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function testC() {
        $B = $this->getBuilder();
        $B->c();
        $this->equals(',', $B);
    }

    public function testGetSqlQuery() {
        $Builder = $this->getBuilder();
        $this->assertTrue($Builder->getSqlQuery() instanceof SqlQuery);
    }
}
