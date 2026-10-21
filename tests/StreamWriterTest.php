<?php

declare(strict_types=1);

namespace Aidphp\Tests\Log;

use PHPUnit\Framework\TestCase;
use Aidphp\Log\StreamWriter;
use Aidphp\Log\WriterInterface;
use Aidphp\Log\FormatterInterface;
use InvalidArgumentException;
use UnexpectedValueException;

class StreamWriterTest extends TestCase
{
    protected function getWriter($stream = null)
    {
        return new StreamWriter($stream ?: 'php://memory', $this->getFormatter());
    }

    protected function getStream()
    {
        return fopen('php://memory', 'a+');
    }

    protected function getFormatter(): FormatterInterface
    {
        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->any())
            ->method('format')
            ->willReturnCallback(function ($record) {
                return $record['message'];
        });

        return $formatter;
    }

    public function testNewInstance()
    {
        $this->assertInstanceOf(WriterInterface::class, $this->getWriter());
    }

    public function testInvalidStream()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A stream must either be a resource or a string');

        $this->getWriter(true);
    }

    public function testWrite()
    {
        $stream = $this->getStream();
        $record = ['message' => 'message-to-log'];

        $writer = $this->getWriter($stream);
        $writer->write($record);

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        $this->assertSame($record['message'] . PHP_EOL, $content);
    }

    public function testClose()
    {
        $stream = $this->getStream();
        $writer = $this->getWriter($stream);

        $this->assertSame(true, is_resource($stream));
        $writer->close();
        $this->assertSame(false, is_resource($stream));
    }

    public function testInvalidWrite()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The stream can not be opened');

        $writer = $this->getWriter($this->getStream());
        $writer->close();
        $writer->write(['message' => 'message-to-log']);
    }
}