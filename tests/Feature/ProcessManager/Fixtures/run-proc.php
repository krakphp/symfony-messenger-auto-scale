<?php

pcntl_async_signals(true);

foreach ([SIGTERM, SIGINT] as $signal) {
    pcntl_signal($signal, function() { exit; });
}


// just in case we get a rogue proc, just stop after 10 iterations
foreach (range(1, 10) as $i) {
    file_put_contents(__DIR__ . '/run-proc.log', 'log: ' . getmypid() . "\n", FILE_APPEND);
    sleep(1);
}
