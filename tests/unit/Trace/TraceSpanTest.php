<?php
/**
 * Copyright 2017 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCensus\Tests\Unit\Trace;

use OpenCensus\Trace\TraceSpan;

/**
 * @group trace
 */
class TraceSpanTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_TIMESTAMP_FORMAT = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}Z$/';

    public function testGeneratesDefaultSpanId()
    {
        $traceSpan = new TraceSpan();
        $info = $traceSpan->info();
        $this->assertArrayHasKey('spanId', $info);
        $this->assertEquals($info['spanId'], $traceSpan->spanId());
    }

    public function testReadsSpanId()
    {
        $traceSpan = new TraceSpan(['spanId' => '1234']);
        $info = $traceSpan->info();
        $this->assertArrayHasKey('spanId', $info);
        $this->assertEquals('1234', $info['spanId']);
    }

    public function testReadsLabels()
    {
        $traceSpan = new TraceSpan(['labels' => ['foo' => 'bar']]);
        $info = $traceSpan->info();
        $this->assertArrayHasKey('labels', $info);
        $this->assertEquals('bar', $info['labels']['foo']);
    }

    public function testCanAddLabel()
    {
        $traceSpan = new TraceSpan();
        $traceSpan->addLabel('foo', 'bar');
        $info = $traceSpan->info();
        $this->assertArrayHasKey('labels', $info);
        $this->assertEquals('bar', $info['labels']['foo']);
    }

    public function testNoLabels()
    {
        $traceSpan = new TraceSpan();
        $info = $traceSpan->info();
        $this->assertArrayNotHasKey('labels', $info);
    }

    public function testEmptyLabels()
    {
        $traceSpan = new TraceSpan(['labels' => []]);
        $info = $traceSpan->info();
        $this->assertArrayNotHasKey('labels', $info);
    }

    public function testGeneratesDefaultSpanName()
    {
        $traceSpan = new TraceSpan();
        $info = $traceSpan->info();
        $this->assertArrayHasKey('name', $info);
        $this->assertStringStartsWith('app', $info['name']);
        $this->assertEquals($info['name'], $traceSpan->name());
    }

    public function testReadsName()
    {
        $traceSpan = new TraceSpan(['name' => 'myspan']);
        $info = $traceSpan->info();
        $this->assertArrayHasKey('name', $info);
        $this->assertEquals('myspan', $info['name']);
    }

    public function testStartFormat()
    {
        $traceSpan = new TraceSpan();
        $traceSpan->setStartTime();
        $info = $traceSpan->info();
        $this->assertArrayHasKey('startTime', $info);
        $this->assertInstanceOf(\DateTimeInterface::class, $info['startTime']);
    }

    public function testFinishFormat()
    {
        $traceSpan = new TraceSpan();
        $traceSpan->setEndTime();
        $info = $traceSpan->info();
        $this->assertArrayHasKey('endTime', $info);
        $this->assertInstanceOf(\DateTimeInterface::class, $info['endTime']);
    }

    public function testGeneratesDefaultKind()
   {
       $traceSpan = new TraceSpan();
       $info = $traceSpan->info();
       $this->assertArrayHasKey('kind', $info);
       $this->assertEquals(TraceSpan::SPAN_KIND_UNKNOWN, $info['kind']);
   }
   public function testReadsKind()
   {
       $traceSpan = new TraceSpan(['kind' => TraceSpan::SPAN_KIND_CLIENT]);
       $info = $traceSpan->info();
       $this->assertArrayHasKey('kind', $info);
       $this->assertEquals(TraceSpan::SPAN_KIND_CLIENT, $info['kind']);
   }

    public function testIgnoresUnknownFields()
    {
        $traceSpan = new TraceSpan(['extravalue' => 'something']);
        $info = $traceSpan->info();
        $this->assertArrayNotHasKey('extravalue', $info);
    }

    public function testGeneratesBacktrace()
    {
        $traceSpan = new TraceSpan();
        $this->assertInternalType('array', $traceSpan->backtrace());
        $this->assertTrue(count($traceSpan->backtrace()) > 0);
        $stackframe = $traceSpan->backtrace()[0];
        $this->assertEquals('testGeneratesBacktrace', $stackframe['function']);
        $this->assertEquals(self::class, $stackframe['class']);
    }

    public function testOverrideBacktrace()
    {
        $backtrace = [
            [
                'class' => 'Foo',
                'line' => 1234,
                'function' => 'asdf',
                'type' => '::'
            ]
        ];
        $traceSpan = new TraceSpan([
            'backtrace' => $backtrace
        ]);

        $this->assertCount(1, $traceSpan->backtrace());
        $stackframe = $traceSpan->backtrace()[0];
        $this->assertEquals('asdf', $stackframe['function']);
        $this->assertEquals('Foo', $stackframe['class']);
    }

    /**
     * @dataProvider timestampFields
     */
    public function testCanFormatTimestamps($field, $timestamp, $expected)
    {
        $traceSpan = new TraceSpan([$field => $timestamp]);
        $this->assertEquals($expected, $traceSpan->info()[$field]->format('Y-m-d\TH:i:s.u000\Z'));
    }

    public function timestampFields()
    {
        return [
            ['startTime', 1490737410, '2017-03-28T21:43:30.000000000Z'],
            ['startTime', 1490737450.4843, '2017-03-28T21:44:10.484299000Z'],
            ['endTime', 1490737410, '2017-03-28T21:43:30.000000000Z'],
            ['endTime', 1490737450.4843, '2017-03-28T21:44:10.484299000Z'],
        ];
    }
}
