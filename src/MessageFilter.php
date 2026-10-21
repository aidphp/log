<?php

declare(strict_types=1);

namespace Aidphp\Log;

class MessageFilter
{
    protected $converter;

    public function __construct(?callable $converter = null)
    {
        $this->converter = $converter ?: new StringConverter();
    }

    public function __invoke(array $record): array
    {
        $data = [];

        foreach ($record['context'] as $key => $val)
        {
            if (str_contains($record['message'], '{' . $key . '}'))
            {
                $data['{' . $key . '}'] = ($this->converter)($val);
            }
        }

        $record['message'] = strtr($record['message'], $data);

        return $record;
    }
}