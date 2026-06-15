<?php
// [app/Presentation/Activities/ActivitiesPresenter.php]

declare(strict_types=1);

namespace App\Presentation\Activities;

use Nette;
use Nette\Database\Explorer;

final class ActivitiesPresenter extends Nette\Application\UI\Presenter
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

        // Extract roles array
        $roles = $identity ? $identity->getRoles() : [];
        $role = $roles[0] ?? 'user';

        // Pull activities only for authorized internal roles
        if (in_array($role, ['admin', 'operator'], true)) {
            $this->template->activities = $this->database->table('activities')
                ->order('created_at DESC')
                ->limit(50)
                ->fetchAll();
        } else {
            $this->template->activities = [];
        }

        // Pass the primary role string down to default.latte
        $this->template->userRole = $role;
    }
}