<?php
namespace Klit\Common\RowMapperBundle\Tests\Services\Query;

use Klit\Common\RowMapperBundle\Services\Query\Builder;
use Klit\Common\RowMapperBundle\Services\Query\Parser\MySQLParser;
use Klit\Common\RowMapperBundle\Services\Query\SqlQuery;
use Klit\Common\RowMapperBundle\Tests\TestKernel;

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
        $Builder = new Builder();
        $Builder->setParser(new MySQLParser());
        $Builder->select()
            ->fieldlist(array(
                'field1',
                'field2'
            ))
            ->table('foobar')
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
            ->limit(10)
        ;

        $query = $Builder->getSqlQuery();

        var_dump($query);

        $this->assertTrue($query instanceof SqlQuery);
        $this->assertEquals(3, count($query->getParameters()));
    }
}
