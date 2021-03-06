--TEST--
OpenCensus Trace: Provided span kind
--FILE--
<?php

require_once(__DIR__ . '/common.php');

// 1: Sanity test a simple profile run
opencensus_trace_function("bar", ['name' => 'foo', 'startTime' => 0.1, 'labels' => ['asdf' => 'qwer'], 'kind' => 1]);
bar();
$traces = opencensus_trace_list();
echo "Number of traces: " . count($traces) . "\n";
$span = $traces[0];

echo "Span kind is '{$span->kind()}'";
?>
--EXPECT--
Number of traces: 1
Span kind is '1'
