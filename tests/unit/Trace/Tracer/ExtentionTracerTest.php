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

namespace OpenCensus\Tests\Unit\Trace\Tracer;

use OpenCensus\Trace\TraceContext;
use OpenCensus\Trace\Tracer\ExtensionTracer;

/**
 * @group trace
 */
class ExtensionTracerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('opencensus')) {
            $this->markTestSkipped('Must have the opencensus extension installed to run this test.');
        }
        opencensus_trace_clear();
    }

    public function testMaintainsContext()
    {
        $parentSpanId = 12345;
        $initialContext = new TraceContext('traceid', $parentSpanId);

        $tracer = new ExtensionTracer($initialContext);
        $context = $tracer->context();

        $this->assertEquals('traceid', $context->traceId());
        $this->assertEquals($parentSpanId, $context->spanId());

        $tracer->inSpan(['name' => 'test'], function () use ($tracer, $parentSpanId) {
            $context = $tracer->context();
            $this->assertNotEquals($parentSpanId, $context->spanId());
        });

        $spans = $tracer->spans();
        $this->assertCount(1, $spans);
        $span = $spans[0];
        $this->assertEquals('test', $span->name());
        $this->assertEquals($parentSpanId, $span->parentSpanId());
    }

    public function testAddsLabelsToCurrentSpan()
    {
        $tracer = new ExtensionTracer();
        $tracer->startSpan(['name' => 'root']);
        $tracer->startSpan(['name' => 'inner']);
        $tracer->addLabel('foo', 'bar');
        $tracer->endSpan();
        $tracer->endSpan();

        $spans = $tracer->spans();
        $this->assertCount(2, $spans);
        $span = $spans[1];
        $this->assertEquals('inner', $span->name());
        $info = $span->info();
        $this->assertEquals('bar', $info['labels']['foo']);
    }

    public function testAddsLabelsToRootSpan()
    {
        $tracer = new ExtensionTracer();
        $tracer->startSpan(['name' => 'root']);
        $tracer->startSpan(['name' => 'inner']);
        $tracer->addRootLabel('foo', 'bar');
        $tracer->endSpan();
        $tracer->endSpan();

        $spans = $tracer->spans();
        $this->assertCount(2, $spans);
        $span = $spans[0];
        $this->assertEquals('root', $span->name());
        $info = $span->info();
        $this->assertEquals('bar', $info['labels']['foo']);
    }

    public function testPersistsBacktrace()
    {
        $tracer = new ExtensionTracer();
        $tracer->inSpan(['name' => 'test'], function () {});
        $span = $tracer->spans()[0];
        $stackframe = $span->backtrace()[0];
        $this->assertEquals('testPersistsBacktrace', $stackframe['function']);
        $this->assertEquals(self::class, $stackframe['class']);
    }
}
