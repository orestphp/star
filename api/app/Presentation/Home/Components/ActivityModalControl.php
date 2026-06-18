<?php
// [app/Presentation/Home/Components/ActivityModalControl.php]

declare(strict_types=1);

namespace App\Presentation\Home\Components;

use Nette\Application\UI\Control;

class ActivityModalControl extends Control
{
    public function __construct(private string $csrfToken)
    {
    }

    public function render(): void
    {
        // Bind parameters safely to the isolated component template
        $this->template->csrfToken = $this->csrfToken;
        $this->template->render(__DIR__ . '/ActivityModal.latte');
    }
}