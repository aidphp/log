<?php

declare(strict_types=1);

namespace Aidphp\Log;

use InvalidArgumentException;
use UnexpectedValueException;

class StreamWriter implements WriterInterface
{
    protected $stream;
    protected FormatterInterface $formater;

    public function __construct($stream, FormatterInterface $formater)
    {
        if (is_resource($stream))
        {
            $this->stream = $stream;
        }
        elseif (is_string($stream))
        {
            $this->stream = fopen($stream, 'a');
        }
        else
        {
            throw new InvalidArgumentException('A stream must either be a resource or a string');
        }

        $this->formater = $formater;
    }

    public function write(array $record): void
    {
        if (! is_resource($this->stream))
        {
            throw new UnexpectedValueException('The stream can not be opened');
        }

        fwrite($this->stream, $this->formater->format($record) . PHP_EOL);
    }

    public function close(): void
    {
        if (is_resource($this->stream))
        {
            fclose($this->stream);
        }

        $this->stream = null;
    }
}