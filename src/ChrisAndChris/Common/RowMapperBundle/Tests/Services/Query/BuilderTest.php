<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;

/**
 * Does simple query tests
 *
 * @name BuilderTest
 * @version   2
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class BuilderTest extends AbstractBuilderTest {

    function testSimpleQuery() {
        // @formatter:off
        $builder = $this->getBuilder();
        $builder->select()
            ->fieldlist([
                'field1' => 'aliasName',
                'field2'
            ])
            ->table('foobar')
            ->join('nexttable')
                ->using('usingField')
            ->join('newtable', 'left')
                ->on()
                    ->field(['lefttable', 'leftfield'])
                    ->equals()->value('1')
                ->close()
            ->where()
                ->brace()
                    ->select()
                    ->fieldlist([
                        'subfield1'
                    ])
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
            ->orderBy([
                'field1',
                'field2'
            ])
            ->limit(10)
        ;
        // @formatter:on

        $query = $builder->getSqlQuery();

        $this->assertTrue($query instanceof SqlQuery);
        $this->assertEquals(4, count($query->getParameters()));
    }

    public function testSelect() {
        $builder = $this->getBuilder();
        $builder->select();

        $this->equals('SELECT', $builder);
    }

    public function testUpdate() {
        $builder = $this->getBuilder();
        $builder->update('table1');
        $this->equals('UPDATE `table1` SET', $builder);
    }

    public function testInsert() {
        $builder = $this->getBuilder();
        $builder->insert('table1');
        $this->equals('INSERT INTO `table1`', $builder);
    }

    public function testInClause() {
        $builder = $this->getBuilder();
        $builder->in(['1', '2', '3']);
        $this->equals('IN (?, ?, ?)', $builder);

        $builder = $this->getBuilder();
        // @formatter:off
        $builder->in()
            ->select()
            ->value(1)
        ->close();
        $this->equals('IN ( SELECT ? )', $builder);
        // @formatter:on
    }

    public function testDelete() {
        $builder = $this->getBuilder();
        $builder->delete('table1');
        $this->equals('DELETE FROM `table1`', $builder);
    }

    public function testTable() {
        $builder = $this->getBuilder();
        $builder->table('table');

        $this->equals('FROM `table`', $builder);
    }

    public function testFieldlist() {
        $builder = $this->getBuilder();
        $builder->fieldlist(
            [
                'field1',
            ]
        );
        $this->equals('`field1`', $builder);

        $builder = $this->getBuilder();
        $builder->fieldlist(
            [
                'field1',
                'field2',
            ]
        );
        $this->equals('`field1`, `field2`', $builder);

        $builder = $this->getBuilder();
        $builder->fieldlist(
            [
                'field1' => 'alias1',
            ]
        );
        $this->equals('`field1` as `alias1`', $builder);

        $builder = $this->getBuilder();
        $builder->fieldlist(
            [
                'field1' => 'alias1',
                'field2' => 'alias2',
            ]
        );
        $this->equals('`field1` as `alias1`, `field2` as `alias2`', $builder);

        $builder = $this->getBuilder();
        $builder->fieldlist(
            [
                'field1' => 'alias1',
                'field2',
            ]
        );
        $this->equals('`field1` as `alias1`, `field2`', $builder);
    }

    public function testWhere() {
        $builder = $this->getBuilder();
        $builder->where()
                ->field('field1')
                ->equals()
                ->value('1')
                ->close();
        // be careful, two whitespaces after WHERE
        $this->equals('WHERE `field1` = ?', $builder);

        $builder = $this->getBuilder();
        $builder->where()
                ->field('field1')
                ->equals()
                ->value('1')
                ->connect()
                ->field('field2')
                ->equals()
                ->field('field3')
                ->close();

        $this->equals('WHERE `field1` = ? AND `field2` = `field3`', $builder);
    }

    public function testField() {
        $builder = $this->getBuilder();
        $builder->field('field1');
        $this->equals('`field1`', $builder);

        $builder = $this->getBuilder();
        $builder->field(['table', 'field']);
        $this->equals('`table`.`field`', $builder);

        $builder = $this->getBuilder();
        $builder->field(['database', 'table', 'field']);
        $this->equals('`database`.`table`.`field`', $builder);
    }

    public function testEquals() {
        $builder = $this->getBuilder();
        $builder->equals();
        $this->equals('=', $builder);
    }

    public function testValue() {
        $builder = $this->getBuilder();
        $builder->value('value1');
        $query = $builder->getSqlQuery();

        $this->assertEquals('?', $query->getQuery());
        $this->assertEquals('value1', $query->getParameters()[0]);
        $this->assertEquals(1, count($query->getParameters()));

        // builder is empty after parsing
        $query = $builder->getSqlQuery();
        $this->assertEquals(0, strlen($query->getQuery()));
        $this->assertEquals(0, count($query->getParameters()));
    }

    public function testBrace() {
        $builder = $this->getBuilder();
        $builder->brace()
                ->close();
        $this->equals('( )', $builder);
    }

    public function testLimit() {
        $builder = $this->getBuilder();
        $builder->limit(1);
        $this->equals('LIMIT 1', $builder);

        $builder = $this->getBuilder();
        $builder->limit(123);
        $this->equals('LIMIT 123', $builder);

        $builder = $this->getBuilder();
        $builder->limit(-1);
        $this->equals('LIMIT 1', $builder);
    }

    public function testJoin() {
        $builder = $this->getBuilder();
        $builder->join('table1');
        $this->equals('INNER JOIN `table1`', $builder);

        $builder = $this->getBuilder();
        $builder->join('table1', 'left');
        $this->equals('LEFT JOIN `table1`', $builder);

        $builder = $this->getBuilder();
        $builder->join('table1', 'right');
        $this->equals('RIGHT JOIN `table1`', $builder);
    }

    public function testUsing() {
        $builder = $this->getBuilder();
        $builder->using('field1');
        $this->equals('USING(`field1`)', $builder);
    }

    public function testOn() {
        $builder = $this->getBuilder();
        $builder->on()
                ->field('field1')
                ->equals()
                ->field('field2')
                ->close();
        $this->equals('ON ( `field1` = `field2` )', $builder);

        $builder = $this->getBuilder();
        $builder->on()
                ->field(['t1', 'field1'])
                ->equals()
                ->field(['t2', 'field2'])
                ->close();
        $this->equals('ON ( `t1`.`field1` = `t2`.`field2` )', $builder);
    }

    public function testGroupBy() {
        $builder = $this->getBuilder();
        $builder->groupBy('field1');
        $this->equals('GROUP BY `field1`', $builder);

        $builder = $this->getBuilder();
        $builder->groupBy()
                ->field('field1')
                ->c()
                ->field('field2')
                ->close();
        $this->equals('GROUP BY `field1` , `field2`', $builder);
    }

    public function testOrder() {
        $builder = $this->getBuilder();
        $builder->order()
                ->by('field1')
                ->close();
        $this->equals('ORDER BY `field1` DESC', $builder);

        $builder = $this->getBuilder();
        $builder->order()
                ->by('field1', 'asc')
                ->close();
        $this->equals('ORDER BY `field1` ASC', $builder);

        $builder = $this->getBuilder();
        $builder->order()
                ->by('field1', 'asc')
                ->c()
                ->by('field2')
                ->close();
        $this->equals('ORDER BY `field1` ASC , `field2` DESC', $builder);
    }

    public function testOderBy() {
        $builder = $this->getBuilder();
        $builder->orderBy(
            [
                'field1',
            ]
        );
        $this->equals('ORDER BY `field1` DESC', $builder);

        $builder = $this->getBuilder();
        $builder->orderBy(
            [
                'field1' => 'asc',
            ]
        );
        $this->equals('ORDER BY `field1` ASC', $builder);

        $builder = $this->getBuilder();
        $builder->orderBy(
            [
                'field1' => 'asc',
                'field2',
            ]
        );
        $this->equals('ORDER BY `field1` ASC , `field2` DESC', $builder);
    }

    public function testConnect() {
        $builder = $this->getBuilder();
        $builder->connect('&');
        $this->equals('AND', $builder);

        $builder = $this->getBuilder();
        $builder->connect('&&');
        $this->equals('AND', $builder);

        $builder = $this->getBuilder();
        $builder->connect('aNd');
        $this->equals('AND', $builder);

        $builder = $this->getBuilder();
        $builder->connect('|');
        $this->equals('OR', $builder);

        $builder = $this->getBuilder();
        $builder->connect('||');
        $this->equals('OR', $builder);

        $builder = $this->getBuilder();
        $builder->connect('oR');
        $this->equals('OR', $builder);

        try {
            $builder = $this->getBuilder();
            $builder->connect('123');
            $this->fail('Must fail due to unknown connection type');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function testC() {
        $builder = $this->getBuilder();
        $builder->c();
        $this->equals(',', $builder);
    }

    public function testGetSqlQuery() {
        $builder = $this->getBuilder();
        $this->assertTrue($builder->getSqlQuery() instanceof SqlQuery);
    }

    public function testComparison() {
        $tests = [
            '<=',
            '<',
            '>=',
            '>',
            '<>',
            '!=',
            '=',
        ];
        foreach ($tests as $test) {
            $builder = $this->getBuilder();
            $builder->compare($test)
                    ->getSqlQuery();
        }

        $tests = [
            '>>',
            '<<',
            '1',
            'a',
            '()',
        ];
        foreach ($tests as $test) {
            try {
                $builder = $this->getBuilder();
                $builder->compare($test)
                        ->getSqlQuery();
                $this->fail('Must fail due to unknown comparison type');
            } catch (MalformedQueryException $e) {
                // ignore
            }
        }
    }

    public function testAsLong() {
        $builder = $this->getBuilder();

        $limit = 5;
        $builder->asLong(
            function () use (&$limit) {
                return --$limit >= 0;
            },
            function () {
                $builder = $this->getBuilder();
                $builder->field('field');

                return $builder;
            }
        );

        $this->assertEquals(5, count($builder->getStatement()));
        $query = $builder->getSqlQuery();
        $this->assertEquals('`field` `field` `field` `field` `field`', $query->getQuery());
    }

    public function testEach() {
        $builder = $this->getBuilder();

        $array = [
            'field',
            'field',
            'field',
        ];

        $builder->each(
            $array,
            function ($item, $isNotLast) {
                $builder = $this->getBuilder();
                $builder->field($item)
                        ->_if($isNotLast)
                        ->c()
                        ->_end();

                return $builder;
            }
        );

        $this->assertEquals(5, count($builder->getStatement()));
        $query = $builder->getSqlQuery();
        $this->assertEquals('`field` , `field` , `field`', $query->getQuery());
    }

    public function testValues() {
        $builder = $this->getBuilder();

        $builder->values();
        $query = $builder->getSqlQuery()
                         ->getQuery();
        $this->assertEquals('VALUES', $query);
    }
}
