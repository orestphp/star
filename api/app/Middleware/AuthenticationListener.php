<?php
// [app/Middleware/AuthenticationListener.php]

declare(strict_types=1);

namespace App\Middleware;

use Nette;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;

class AuthenticationListener
{
    public function __construct(
        private Nette\Security\User $user
    ) {}

    public function __invoke(Application $application, Nette\Application\IPresenter $presenter): void
    {
        if (!$presenter instanceof Presenter) {
            return;
        }

        // Bypass check for login/logout actions
        if ($presenter instanceof \App\Presentation\Sign\SignPresenter) {
            return;
        }

        // Role integrity on login
        if (!$this->user->isLoggedIn()) {
            $presenter->redirect('Sign:in');
            return;
        }
    }
}