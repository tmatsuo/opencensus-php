--TEST--
OpenCensus Trace: Basic Method Test
--FILE--
<?php

require_once(__DIR__ . '/common.php');

// 1: Sanity test a simple profile run
opencensus_trace_method("Foo", "bar");
$f = new Foo();
$f->bar();
$traces = opencensus_trace_list();
echo "Number of traces: " . count($traces) . "\n";
$span = $traces[0];

$test = gettype($span->spanId());
echo "Span id is a $test\n";

echo "Span name is: '{$span->name()}'\n";

$test = gettype($span->startTime()) == 'double';
echo "Span startTime is a double: $test\n";

$test = gettype($span->endTime()) == 'double';
echo "Span endTime is a double: $test\n";

?>
--EXPECT--
Number of traces: 1
Span id is a integer
Span name is: 'Foo::bar'
Span startTime is a double: 1
Span endTime is a double: 1
