<?php
// [app/Presentation/Home/Components/ActivityDetailsModalControl.php]

declare(strict_types=1);

namespace App\Presentation\Home\Components;

use Nette\Application\UI\Control;

class ActivityDetailsModalControl extends Control
{
    public function __construct(private string $csrfToken)
    {
    }

    public function render(): void
    {
        $this->template->csrfToken = $this->csrfToken;
        $this->template->render(__DIR__ . '/ActivityDetailsModal.latte');
    }
}