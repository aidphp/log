<?php

declare(strict_types=1);

namespace Aidphp\Log;

interface FormatterInterface
{
    public function format(array $record): string;
}