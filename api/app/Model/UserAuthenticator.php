<?php
// [app/Model/UserAuthenticator.php]

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;
use Nette\Database\Explorer;

final class UserAuthenticator implements IAuthenticator
{
    public function __construct(
        private Explorer $database
    ) {}

    public function authenticate(array $credentials): IIdentity
    {
        $username = $credentials[0] ?? $credentials['username'] ?? '';
        $password = $credentials[1] ?? $credentials['password'] ?? '';

        // 1. Fetch user record directly from your real MySQL 'users' table
        $row = $this->database->table('users')
            ->where('email = ?', $username)
            ->fetch();

        // 2. Check if user exists
        if (!$row) {
            throw new AuthenticationException('Invalid email address or user not found.');
        }

        // 3. Check if the account is active
        if ((int)$row->is_active !== 1) {
            throw new AuthenticationException('This account has been deactivated.');
        }

        // 4. Validate hashed password entry
        if (!password_verify($password, $row->password)) {
            throw new AuthenticationException('Invalid password.');
        }

        $allowedRoles = ['admin', 'operator'];
        if (!in_array($row->role, $allowedRoles, true)) {
            throw new AuthenticationException('Access denied. You do not have permission to view this console.');
        }

        // 5. Dispatch validated web session identity with real schema columns
        return new SimpleIdentity(
            $row->id,
            $row->role, // 'admin' or 'operator'
            [
                'email' => $row->email,
                'name' => $row->name
            ]
        );
    }
}