<?php

declare(strict_types=1);

namespace Aidphp\Tests\Log;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Aidphp\Log\MessageFilter;
use stdClass;

class MessageFilterTest extends TestCase
{
    public function testNewInstance()
    {
        $this->assertSame(true, is_callable(new MessageFilter()));
    }

    #[DataProvider('getReplacements')]
    public function testFilter($val, $expected)
    {
        $filter = new MessageFilter();
        $record = $filter([
            'message' => '{foo}',
            'context' => ['foo' => $val],
        ]);
        $this->assertSame($expected, $record['message']);
    }

    public static function getReplacements()
    {
        return [
            'string'     => ['foo', 'foo'],
            'string-int' => ['3', '3'],
            'int'        => [3, '3'],
            'null'       => [null, 'NULL'],
            'true'       => [true, 'true'],
            'false'      => [false, 'false'],
            'stdclass'   => [new stdClass, 'object(stdClass) {}'],
            'array'      => [[], '[]'],
        ];
    }
}