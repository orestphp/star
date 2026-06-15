<?php
// [app/Presentation/Home/HomePresenter.php]

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use Nette\Database\Explorer;

final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public string $search = '';
    /** @persistent */
    public string $status = 'all';
    /** @persistent */
    public string $sort = 'created_desc';

    // 💡 FIX: Explicitly declaring the property avoids the MemberAccessException
    public ?int $selectedCustomerId = null;

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
        $query = $this->database->table('users')->where('role = ?', 'customer');

        if (!empty($this->search)) {
            $tokens = '%' . $this->search . '%';
            $query->where('name LIKE ? OR email LIKE ?', $tokens, $tokens);
        }

        if ($this->status === 'active') {
            $query->where('is_active = ?', 1);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active = ?', 0);
        }

        switch ($this->sort) {
            case 'name_asc': $query->order('name ASC'); break;
            case 'name_desc': $query->order('name DESC'); break;
            case 'created_asc': $query->order('created_at ASC'); break;
            case 'created_desc':
            default:
                $query->order('created_at DESC');
                break;
        }

        $this->template->customers = $query->fetchAll();
        $this->template->search = $this->search;
        $this->template->status = $this->status;
        $this->template->sort = $this->sort;

        if ($this->selectedCustomerId !== null) {
            $this->loadCustomerActivities($this->selectedCustomerId);
        } else {
            $this->template->activities = [];
            $this->template->selectedCustomer = null;
        }
    }

    /**
     * ⚡ AJAX Signal Handler
     */
    public function handleLoadActivities(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->loadCustomerActivities($customerId);

        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

    private function loadCustomerActivities(int $customerId): void
    {
        $customer = $this->database->table('users')->get($customerId);
        if (!$customer) {
            return;
        }

        $this->template->selectedCustomer = $customer;

        // 💡 Try standard variations via graceful fallbacks to find your schema column
        $columnFallbacks = ['customer_id', 'id_user', 'id_customer', 'user_id'];

        foreach ($columnFallbacks as $column) {
            try {
                // Duplicate the selection initialization per pass to keep fresh state
                $this->template->activities = $this->database->table('activities')
                    ->where("$column = ?", $customerId)
                    ->order('created_at DESC')
                    ->limit(50)
                    ->fetchAll();

                // If it succeeds without throwing a DriverException, break out early!
                break;
            } catch (\Nette\Database\DriverException $e) {
                // If we ran through all fallbacks and it still fails, rethrow the error
                if ($column === end($columnFallbacks)) {
                    throw $e;
                }
                continue;
            }
        }
    }
}