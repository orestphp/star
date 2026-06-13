<?php
// [api/tests/AdminLoginTest.php]

declare(strict_types=1);

namespace App\Tests;

use Tester\Assert;
use Nette\Security\User;
use Nette\Security\SimpleAuthenticator;
use Nette\Security\AuthenticationException;

/** @var \Nette\DI\Container $container */
$container = require __DIR__ . '/bootstrap.php';

class AdminLoginTest extends \Tester\TestCase
{
    private User $user;

    public function __construct(\Nette\DI\Container $container)
    {
        $this->user = $container->getByType(User::class);

        // Fix: Use the accurate flat array format required by SimpleAuthenticator
        $mockAuthenticator = new SimpleAuthenticator([
            'admin@admin.com' => 'password'
        ]);

        $this->user->setAuthenticator($mockAuthenticator);
    }

    protected function setUp(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->user->logout(true);
        }
    }

    public function testSuccessfulAdminLogin(): void
    {
        $this->user->login('admin@admin.com', 'password');

        Assert::true($this->user->isLoggedIn(), 'User should be successfully logged in.');

        $identity = $this->user->getIdentity();
        Assert::notNull($identity, 'Identity mapping should exist.');
        Assert::same('admin@admin.com', $identity->getId(), 'Identity ID should match username.');
    }

    public function testInvalidPasswordThrowsException(): void
    {
        Assert::exception(function () {
            $this->user->login('admin@admin.com', 'wrong_password_here');
        }, AuthenticationException::class);

        Assert::false($this->user->isLoggedIn(), 'User should remain logged out.');
    }
}

// Run the test suite natively
(new AdminLoginTest($container))->run();