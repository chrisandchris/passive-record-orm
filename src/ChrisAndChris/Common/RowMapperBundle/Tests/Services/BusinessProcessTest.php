<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services;

use ChrisAndChris\Common\RowMapperBundle\Services\BusinessProcess;
use ChrisAndChris\Common\RowMapperBundle\Tests\TestKernel;

/**
 *
 *
 * @name BusinessProcessTest
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 *
 * @covers    \ChrisAndChris\Common\RowMapperBundle\Services\BusinessProcess
 */
class BusinessProcessTest extends TestKernel
{

    public function testRun()
    {
        $mock =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer')
                 ->disableOriginalConstructor()
                 ->getMock();

        $mock->expects($this->once())
             ->method('beginTransaction')
             ->willReturn(true);

        $mock->expects($this->never())
             ->method('rollBack');

        $mock->expects($this->once())
             ->method('commit')
             ->willReturn(true);

        $mock->expects($this->at(0))
             ->method('inTransaction')
             ->willReturn(false);

        $mock->expects($this->at(1))
             ->method('inTransaction')
             ->willReturn(true);

        $mock->expects($this->at(2))
             ->method('inTransaction')
             ->willReturn(true);

        $mock->expects($this->at(3))
             ->method('inTransaction')
             ->willReturn(true);

        $mock->expects($this->at(4))
             ->method('inTransaction')
             ->willReturn(true);

        $process = new BusinessProcess(
            'test',
            $mock,
            $this->getLoggerMock(),
            $this->getDispatcherMock()
        );

        $result = $process->run(function () use ($process) {
            $result = $process->run(function () {
                return false;
            });
            $this->assertFalse($result);

            return true;
        });
        $this->assertTrue($result);

        unset($process);
    }

    private function getLoggerMock()
    {
        $mock = $this->getMockBuilder('\Monolog\Logger')
                     ->disableOriginalConstructor()
                     ->getMock();

        return $mock;
    }

    private function getDispatcherMock()
    {
        $mock =
            $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
                 ->disableOriginalConstructor()
                 ->getMock();

        return $mock;
    }

    public function testGetTraceMessage()
    {
        $trace = [
            [
                'file'     => 'foo/bar/BusinessProcess.php',
                'function' => 'run',
                'line'     => 1,
            ],
            [
                'file'     => 'foo/bar/UserProcess.php',
                'function' => 'getUsers',
                'line'     => 5,
            ],
        ];

        $process = $this->getProcess();

        $message = $process->getTraceMessage($trace);
        $this->assertEquals(
            'UserProcess.php::(unknown)->getUsers()@5',
            $message
        );
    }

    private function getProcess(array $failsAt = [], $failsToFetch = false)
    {
        return new BusinessProcess(
            'test',
            $this->getPdoLayerMock($failsAt),
            $this->getLoggerMock(),
            $this->getDispatcherMock()
        );
    }

    private function getPdoLayerMock(array $failsAt = [])
    {
        $mock =
            $this->getMockBuilder('\ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer')
                 ->disableOriginalConstructor()
                 ->getMock();

        $mock->method('commit')
             ->willReturn(in_array('commit', $failsAt) ? false : true);
        $mock->method('beginTransaction')
             ->willReturn(in_array('begin', $failsAt) ? false : true);
        $mock->method('rollBack')
             ->willReturn(in_array('rollback', $failsAt) ? false : true);
        $mock->method('exec')
             ->willReturn(in_array('exec', $failsAt) ? false : true);
        $mock->method('getAttribute')
             ->willReturn('pgsql');
        $mock->method('inTransaction')
             ->willReturn(true);

        return $mock;
    }

    public function testGetTraceMessage_onlyLatestProcess()
    {
        $trace = [
            [
                'file'     => 'foo/bar/BusinessProcess.php',
                'function' => 'run',
                'line'     => 1,
            ],
            [
                'file'     => 'foo/bar/UserProcess.php',
                'function' => 'getUsers',
                'line'     => 5,
            ],
            [
                'file'     => 'foo/bar/deep/folder/DeepProcess.php',
                'function' => 'getSomething',
                'line'     => 15,
            ],
        ];

        $process = $this->getProcess();

        $message = $process->getTraceMessage($trace);
        $this->assertEquals(
            'UserProcess.php::(unknown)->getUsers()@5',
            $message
        );
    }

    public function testGetTraceMessage_noValidTrace()
    {
        $trace = [
            [
                'file'     => 'foo/bar/BusinessProcess.php',
                'function' => 'run',
                'line'     => 1,
            ],
            [
                'file'     => 'foo/bar/SomeClass.php',
                'function' => 'someMethod',
                'line'     => 100,
            ],
        ];

        $process = $this->getProcess();

        $message = $process->getTraceMessage($trace);
        $this->assertEquals(
            'SomeClass.php::(unknown)->someMethod()@100',
            $message
        );
    }

    public function testCommit()
    {
        $process = $this->getProcess();

        $process->startTransaction();
        $process->commit();
    }
}
