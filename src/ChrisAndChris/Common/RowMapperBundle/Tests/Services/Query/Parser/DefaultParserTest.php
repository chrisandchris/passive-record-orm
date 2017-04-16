<?php
namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\SnippetBagEvent;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MalformedQueryException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\MissingParameterException;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\DefaultParser;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets\MySqlBag;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        /** @var EventDispatcherInterface $ed */
        $ed = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
                   ->disableOriginalConstructor()
                   ->getMock();

        $event = new SnippetBagEvent();
        $event->add(new MySqlBag(), ['mysql']);

        $ed->method('dispatch')
           ->willReturn($event);

        return new DefaultParser($ed, 'mysql');
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

    public function testNullParameters() {
        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type' => 'value',
                ],
            ]
        );
        try {
            $parser->execute();
            $this->fail('Must fail due to missing parameter');
        } catch (MissingParameterException $e) {
            // ignore
        }

        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type'   => 'value',
                    'params' => ['value' => 13],
                ],
            ]
        );
        // must not throw any exception
        $parser->execute();

        $parser = $this->getParser();
        $parser->setStatement(
            [
                [
                    'type'   => 'value',
                    'params' => ['value' => null],
                ],
            ]
        );
        // must not throw any exception
        $parser->execute();
    }
}
