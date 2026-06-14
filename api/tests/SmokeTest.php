<?php
// [api/tests/SmokeTest.php]

declare(strict_types=1);

namespace App\Tests;

use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
class SmokeTest extends \Tester\TestCase
{
    public function testPureLogic(): void
    {
        Assert::same(2, 1 + 1);
    }
}

(new SmokeTest())->run();