<?php

declare(strict_types=1);

namespace Aidphp\Tests\Log;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Aidphp\Log\StringConverter;
use DateTime;
use EmptyIterator;
use ArrayIterator;
use JsonSerializable;
use stdClass;
use LogicException;

class StringConverterTest extends TestCase
{
    protected $converter;

    public function setUp(): void
    {
        $this->converter = new StringConverter();
    }

    public function testNewInstance()
    {
        $this->assertSame(true, is_callable($this->converter));
    }

    #[DataProvider('getValues')]
    public function testConvert($value, $expected)
    {
        $this->assertSame($expected, ($this->converter)($value));
    }

    public static function getValues()
    {
        return [
            'datetime'          => [$datetime = new DateTime(), $datetime->format(StringConverter::DEFAULT_DATE_FORMAT)],
            'float'             => [0.2, '0.2'],
            'boolean'           => [false, 'false'],
            'array_empty'       => [[], '[]'],
            'array'             => [range(0, 4), '["0","1","2","3","4"]'],
            'traversable_empty' => [new EmptyIterator(), '[]'],
            'traversable'       => [new ArrayIterator(['id', 42]), '["id","42"]'],
            'null'              => [null, 'NULL'],
            'object_empty'      => [new stdClass(), 'object(stdClass) {}'],
            'object'            => [new Foo(), 'object(' . Foo::class . ') {"foo":"fooValue"}'],
            'closure'           => [function ($foo) {return $foo;}, 'object(Closure) {}'],
            'string object'     => [new Bar(), 'Bar'],
            'exception'         => [new LogicException('Fail'), 'object(LogicException) Fail in ' . __FILE__ . ' on line ' . (__LINE__)],
            'json_serializable' => [new Foobar(), '{"Foobar":"foobar"}'],
            'resource'          => [fopen('php://stdout', 'w'), 'resource(stream)'],
        ];
    }
}

class Foo
{
    public $foo = 'fooValue';
}

class Bar
{
    public function __toString()
    {
        return 'Bar';
    }
}

class Foobar implements JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return ['Foobar' => 'foobar'];
    }
}