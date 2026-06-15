<?php
// [app/Presentation/Home/CustomersPresenter.php]

declare(strict_types=1);

namespace App\Presentation\Customers;

use Nette;
use Nette\Database\Explorer;

final class CustomersPresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public string $search = '';

    /** @persistent */
    public string $status = 'all';

    /** @persistent */
    public string $sort = 'created_desc';

    public function __construct(
        private Explorer $database
    ) {}

    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        // Ensure roles are parsed securely
        $roles = $this->getUser()->getIdentity()?->getRoles() ?? [];
        $role = $roles[0] ?? 'user';

        if (!in_array($role, ['admin', 'operator'], true)) {
            $this->flashMessage('You do not have permission to view this directory.', 'danger');
            $this->redirect('Sign:in'); // or another fallback route
        }
    }

    public function renderDefault(): void
    {
        // Fetch and filter customers for the home view directory
        $query = $this->database->table('users')->where('role = ?', 'customer');

        // 🔍 Search
        if (!empty($this->search)) {
            $tokens = '%' . $this->search . '%';
            $query->where('name LIKE ? OR email LIKE ?', $tokens, $tokens);
        }

        // 🚦 Status filter
        if ($this->status === 'active') {
            $query->where('is_active = ?', 1);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active = ?', 0);
        }

        // 📐 Sorting
        switch ($this->sort) {
            case 'name_asc': $query->order('name ASC'); break;
            case 'name_desc': $query->order('name DESC'); break;
            case 'created_asc': $query->order('created_at ASC'); break;
            case 'created_desc':
            default:
                $query->order('created_at DESC');
                break;
        }

        // Bind data parameters to template
        $this->template->customers = $query->fetchAll();
        $this->template->search = $this->search;
        $this->template->status = $this->status;
        $this->template->sort = $this->sort;
    }
}