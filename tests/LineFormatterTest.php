<?php

declare(strict_types=1);

namespace Aidphp\Tests\Log;

use PHPUnit\Framework\TestCase;
use Aidphp\Log\FormatterInterface;
use Aidphp\Log\LineFormatter;
use DateTime;

class LineFormatterTest extends TestCase
{
    protected function getRecord(): array
    {
        return [
            'datetime' => new DateTime('1998-07-12T18:15:00Z'),
            'level'    => 'bar',
            'message'  => 'foo',
            'context'  => []
        ];
    }

    public function testNewInstance()
    {
        $this->assertInstanceOf(FormatterInterface::class, new LineFormatter());
    }

    public function testDefaultFormat()
    {
        $formater = new LineFormatter();
        $this->assertSame('1998-07-12T18:15:00.000000+00:00 bar: foo', $formater->format($this->getRecord()));
    }

    public function testCustomFormat()
    {
        $formater = new LineFormatter('%level%: %message%');
        $this->assertSame('bar: foo', $formater->format($this->getRecord()));
    }
}