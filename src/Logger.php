<?php

declare(strict_types=1);

namespace Aidphp\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use DateTimeZone;
use Stringable;
use InvalidArgumentException;
use DateTimeImmutable;

class Logger implements LoggerInterface
{
    protected WriterInterface $writer;
    protected array $filters;
    protected DateTimeZone $timezone;

    public function __construct(WriterInterface $writer, array $filters = [], ?DateTimeZone $timezone = null)
    {
        $this->writer   = $writer;
        $this->filters  = $filters;
        $this->timezone = $timezone ?: new DateTimeZone(date_default_timezone_get() ?: 'UTC');
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (! defined(LogLevel::class . '::' . strtoupper($level)))
        {
            throw new InvalidArgumentException('Level "' . $level . '" is not defined');
        }

        $record = [
            'datetime' => new DateTimeImmutable('now', $this->timezone),
            'level'    => $level,
            'message'  => (string) $message,
            'context'  => $context
        ];

        foreach ($this->filters as $filter)
        {
            $record = $filter($record);
        }

        $this->writer->write($record);
    }

    public function __destruct()
    {
        $this->writer->close();
    }
}