<?php
// [api/tests/run.php]

declare(strict_types=1);

echo "🚀 Starting Centralized Test Suite via Direct Execution...\n";
echo "--------------------------------------------------------\n";

// Dynamically find all files matching *Test.php in the tests directory
$testFiles = glob(__DIR__ . '/*Test.php');

if (empty($testFiles)) {
    echo "❌ No test files found matching *Test.php\n";
    exit(1);
}

$suiteFailed = false;
$passedCount = 0;
$failedCount = 0;

foreach ($testFiles as $file) {
    $filename = basename($file);
    echo "👉 Running: {$filename}\n";

    // Clear test cache right before each standalone test to prevent cross-contamination
    $tempDir = __DIR__ . '/../temp/tests';
    if (is_dir($tempDir)) {
        shell_exec("rm -rf " . escapeshellarg($tempDir) . "/*");
    }

    // Execute the test file directly in a foreground PHP process
    passthru("php " . escapeshellarg($file), $exitCode);

    if ($exitCode === 0) {
        $passedCount++;
        echo "✅ {$filename} passed cleanly.\n\n";
    } else {
        $failedCount++;
        $suiteFailed = true;
        echo "❌ {$filename} failed with exit code {$exitCode}.\n\n";
    }
}

echo "--------------------------------------------------------\n";
echo "TEST SUITE SUMMARY\n";
echo "✅ Passed: {$passedCount}\n";
if ($failedCount > 0) {
    echo "❌ Failed: {$failedCount}\n";
} else {
    echo "✅ All tests passed successfully!\n";
}

exit($suiteFailed ? 1 : 0);