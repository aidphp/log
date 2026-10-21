<?php

declare(strict_types=1);

namespace Aidphp\Log;

use DateTimeInterface;
use Throwable;
use Traversable;
use JsonSerializable;

class StringConverter
{
    public const DEFAULT_JSON_FLAGS  = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR;
    public const DEFAULT_DATE_FORMAT = "Y-m-d\TH:i:s.uP";

    protected string $dateFormat;
    protected int $jsonFlags;

    public function __construct(?string $dateFormat = null, ?int $jsonFlags = null)
    {
        $this->dateFormat = $dateFormat ?: self::DEFAULT_DATE_FORMAT;
        $this->jsonFlags  = $jsonFlags ?: self::DEFAULT_JSON_FLAGS;
    }

    public function __invoke($value): string
    {
        switch (gettype($value))
        {
            case 'boolean':
                return $value ? 'true' : 'false';

            case 'object':
                return $this->convertObject($value);

            case 'array':
                return $this->convertIterable($value);

            case 'resource':
                return 'resource(' . get_resource_type($value) . ')';

            case 'NULL':
                return 'NULL';

            case 'double':
            default:
                return (string) $value;
        }
    }

    protected function convertObject(object $value): string
    {
        if ($value instanceof DateTimeInterface)
        {
            return $value->format($this->dateFormat);
        }
        elseif ($value instanceof Throwable)
        {
            return 'object(' . get_class($value) . ') ' . $value->getMessage() . ' in ' . $value->getFile() . ' on line ' . $value->getLine();
        }
        elseif ($value instanceof Traversable)
        {
            return $this->convertIterable($value);
        }
        elseif ($value instanceof JsonSerializable)
        {
            return $this->convertToJson($value->jsonSerialize());
        }
        elseif (! method_exists($value, '__toString'))
        {
            return 'object(' . get_class($value) . ') ' . $this->convertToJson($value);
        }
        else
        {
            return (string) $value;
        }
    }

    protected function convertIterable(iterable $value): string
    {
        $vars = [];

        foreach ($value as $key => $val)
        {
            $vars[$key] = $this->__invoke($val);
        }

        return $this->convertToJson($vars);
    }

    protected function convertToJson($value): string
    {
        return @json_encode($value, $this->jsonFlags);
    }
}