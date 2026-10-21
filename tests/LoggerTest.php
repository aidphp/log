<?php

declare(strict_types=1);

namespace Aidphp\Tests\Log;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Aidphp\Log\Logger;
use Psr\Log\LoggerInterface;
use Aidphp\Log\WriterInterface;
use DateTimeImmutable;
use Psr\Log\LogLevel;
use InvalidArgumentException;

class LoggerTest extends TestCase
{
    protected $log;

    protected function getWriter()
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects($this->any())
            ->method('write')
            ->willReturnCallback(function ($record) {
                $this->log = $record;
        });

        return $writer;
    }

    protected function getLogger(?WriterInterface $writer = null, array $filters = [])
    {
        return new Logger($writer ?: $this->createMock(WriterInterface::class), $filters);
    }

    public function testNewInstance()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->getLogger());
    }

    #[DataProvider('getMessages')]
    public function testLog($level, $message)
    {
        $writer = $this->getWriter();
        $logger = $this->getLogger($writer);

        $logger->{$level}($message);
        $this->assertInstanceOf(DateTimeImmutable::class, $this->log['datetime']);
        $this->assertSame($this->log['level'], $level);
        $this->assertSame($this->log['message'], $message);
        $this->assertSame($this->log['context'], []);

        $logger->log($level, $message);
        $this->assertInstanceOf(DateTimeImmutable::class, $this->log['datetime']);
        $this->assertSame($this->log['level'], $level);
        $this->assertSame($this->log['message'], $message);
        $this->assertSame($this->log['context'], []);
    }

    public static function getMessages()
    {
        return [
            LogLevel::EMERGENCY => [LogLevel::EMERGENCY, 'message of level emergency'],
            LogLevel::ALERT     => [LogLevel::ALERT, 'message of level alert'],
            LogLevel::CRITICAL  => [LogLevel::CRITICAL, 'message of level critical'],
            LogLevel::ERROR     => [LogLevel::ERROR, 'message of level error'],
            LogLevel::WARNING   => [LogLevel::WARNING, 'message of level warning'],
            LogLevel::NOTICE    => [LogLevel::NOTICE, 'message of level notice'],
            LogLevel::INFO      => [LogLevel::INFO, 'message of level info'],
            LogLevel::DEBUG     => [LogLevel::DEBUG, 'message of level debug'],
        ];
    }

    public function testLogWithFilter()
    {
        $writer = $this->getWriter();
        $filter = function ($record) {
            $record['test'] = 'Test';
            return $record;
        };

        $logger = $this->getLogger($writer, [$filter]);
        $logger->log(LogLevel::INFO, 'Test Message');

        $this->assertSame($this->log['test'], 'Test');
    }

    public function testLogWithInvalidLevel()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Level "invalid level" is not defined');

        $this->getLogger()->log('invalid level', 'Foo');
    }
}