<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\SnippetBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 * @name DefaultParserTest
 * @version   2
 * @since     v2.0.1
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class DefaultParserTest extends TestKernel {

    public function testSimpleExecute() {
        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type'   => 'select',
                    'params' => [],
                ],
            ]
        );
        $parser->execute();
        $this->assertEquals('SELECT', $parser->getSqlQuery());
    }

    private function getParser() {
        return new DefaultParser(new SnippetBag());
    }

    public function testOpenBraces() {
        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type'   => 'select',
                    'params' => [],
                ],
                [
                    'type'   => 'brace',
                    'params' => [],
                ],
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
        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type'   => 'select',
                    'params' => [],
                ],
                [
                    'type'   => 'close',
                    'params' => [],
                ],
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
