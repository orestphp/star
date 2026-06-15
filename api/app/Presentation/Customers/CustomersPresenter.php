<?php
// [app/Presentation/Customers/CustomersPresenter.php]

declare(strict_types=1);

namespace App\Presentation\Customers;

use Nette;
use Nette\Database\Explorer;

final class CustomersPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Explorer $database
    ) {}

    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function renderDefault(): void
    {
        $user = $this->getUser();
        $identity = $user->getIdentity();
        $roles = $identity ? $identity->getRoles() : [];
        $role = $roles[0] ?? 'user';

        $this->template->customers = $this->database->table('users')
            ->where('role = ?', 'customer')
            ->order('created_at DESC')
            ->fetchAll();

        $this->template->userRole = $role;
    }
}