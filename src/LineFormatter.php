<?php

declare(strict_types=1);

namespace Aidphp\Log;

class LineFormatter implements FormatterInterface
{
    public const DEFAULT_FORMAT = '%datetime% %level%: %message%';

    protected string $format;
    protected $converter;

    public function __construct(?string $format = null, ?callable $converter = null)
    {
        $this->format = $format ?: self::DEFAULT_FORMAT;
        $this->converter = $converter ?: new StringConverter();
    }

    public function format(array $record): string
    {
        $output = $this->format;

        foreach ($record as $key => $val)
        {
            if (str_contains($output, '%' . $key . '%'))
            {
                $output = str_replace('%' . $key . '%', ($this->converter)($val), $output);
            }
        }

        return $output;
    }
}