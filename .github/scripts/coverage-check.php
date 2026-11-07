<?php

/**
 * Fails unless line coverage in a Clover report meets the minimum.
 *
 *   php .github/scripts/coverage-check.php <clover.xml> <min-percent>
 */

[$script, $file, $min] = array_pad($argv, 3, null);

if ($file === null || $min === null || !is_numeric($min)) {
    fwrite(STDERR, "usage: php {$script} <clover.xml> <min-percent>\n");
    exit(2);
}

// Fail closed: a missing or unreadable report is a failure, not a pass
$xml = is_file($file) ? simplexml_load_file($file) : false;
$metrics = $xml ? $xml->xpath('/coverage/project/metrics') : [];
if (!$metrics) {
    fwrite(STDERR, "::error::No coverage metrics found in {$file}\n");
    exit(1);
}

$total = (int) $metrics[0]['statements'];
$covered = (int) $metrics[0]['coveredstatements'];
$percent = $total > 0 ? $covered / $total * 100 : 0.0;

printf("Line coverage: %.2f%% (%d/%d), minimum %.2f%%\n", $percent, $covered, $total, $min);

if ($percent < (float) $min) {
    fwrite(STDERR, sprintf("::error::Line coverage %.2f%% is below the %.2f%% minimum\n", $percent, $min));
    exit(1);
}
