<?php

declare(strict_types=1);

namespace Aidphp\Log;

interface WriterInterface
{
    public function write(array $record): void;
    public function close(): void;
}