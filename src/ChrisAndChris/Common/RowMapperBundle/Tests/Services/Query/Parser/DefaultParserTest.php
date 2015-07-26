<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\BraceType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\CloseType;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\SelectType;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name DefaultParserTest
 * @version   1
 * @since     v2.0.1
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class DefaultParserTest extends TestKernel {

    public function testSimpleExecute() {
        $parser = new DefaultParser();
        $parser->setStatement(
            [
                new SelectType(),
            ]
        );
        $parser->execute();
        $this->assertEquals('SELECT', $parser->getSqlQuery());
    }

    public function testOpenBraces() {
        $parser = new DefaultParser();
        $parser->setStatement(
            [
                new SelectType(),
                new BraceType(),
            ]
        );
        try {
            $parser->execute();
            $this->fail('Must fail due to open braces');
        } catch (MalformedQueryException $e) {
            // ignore
        }
    }

    public function testNotOpenedBraces() {
        $parser = new DefaultParser();
        $parser->setStatement(
            [
                new SelectType(),
                new CloseType(),
            ]
        );
        try {
            $parser->execute();
            $this->fail('Must fail due to never opened braces');
        } catch (MalformedQueryException $e) {
            // ignore
        }
    }
}
