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

    /**
     * ⚡ AJAX Signal Handler: Adds a new operator comment note safely
     */
    public function handleAddComment(int $customerId, string $comment): void
    {
        // 1. Fetch a dummy or existing row from activities to check column keys safely
        $existingRow = $this->database->table('activities')->limit(1)->fetch();

        // Fallback default array if the table is completely empty
        $rowKeys = $existingRow
            ? array_keys($existingRow->toArray())
            : ['customer_id', 'detail', 'type', 'created_at'];

        // 2. Map out the correct foreign key field
        $foreignKey = 'customer_id';
        foreach (['customer_id', 'id_user', 'id_customer', 'user_id'] as $col) {
            if (in_array($col, $rowKeys, true)) { $foreignKey = $col; break; }
        }

        // 3. Map out the correct comment/detail text field
        $textField = 'detail';
        foreach (['detail', 'description', 'details', 'message', 'comment'] as $col) {
            if (in_array($col, $rowKeys, true)) { $textField = $col; break; }
        }

        // 4. Build our data entry matrix array
        $insertData = [
            $foreignKey => $customerId,
            $textField  => trim($comment),
            'created_at' => new \DateTime(), // or use NOW() database side
        ];

        // 5. Match and insert a type column if it exists in your schema
        foreach (['type', 'action_type', 'action'] as $col) {
            if (in_array($col, $rowKeys, true)) {
                $insertData[$col] = 'COMMENT'; // Explicit classification tag
                break;
            }
        }

        // 6. Execute safe insert statement
        $this->database->table('activities')->insert($insertData);

        // 7. Retain structural presentation variables for snippet redraw
        $this->selectedCustomerId = $customerId;

        if (method_exists($this, 'loadCustomerActivities')) {
            $this->loadCustomerActivities($customerId);
        } else {
            $this->template->selectedCustomer = $this->database->table('customers')->get($customerId);
            $this->template->activities = $this->database->table('activities')
                ->where($foreignKey, $customerId)
                ->order('created_at DESC')
                ->limit(50);
        }

        // Redraw snippet for seamless dynamic UI tracking updates
        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * ⚡ AJAX Signal Handler: Modifies an existing operator note/activity detail safely
     */
    public function handleUpdateComment(int $activityId, string $detail): void
    {
        // 1. Fetch the specific activity row record
        $activityRow = $this->database->table('activities')->get($activityId);
        if (!$activityRow) {
            return;
        }

        // 2. Safely find the text field column by inspecting the ActiveRow keys directly
        $rowKeys = array_keys($activityRow->toArray());
        $textField = 'detail'; // Fallback default

        foreach (['detail', 'description', 'details', 'message', 'comment'] as $col) {
            if (in_array($col, $rowKeys, true)) {
                $textField = $col;
                break;
            }
        }

        // 3. Apply the updated detail text payload string
        $activityRow->update([
            $textField => trim($detail)
        ]);

        // 4. Safely find the foreign key column back to the customer profile
        $foreignKey = 'customer_id'; // Fallback default
        foreach (['customer_id', 'id_user', 'id_customer', 'user_id'] as $col) {
            if (in_array($col, $rowKeys, true)) {
                $foreignKey = $col;
                break;
            }
        }

        $customerId = $activityRow->{$foreignKey};

        // 5. Reload the timeline payload variables for the snippet redraw
        $this->selectedCustomerId = $customerId;

        // Match whichever internal data loading method your presenter uses (e.g., template assignments)
        if (method_exists($this, 'loadCustomerActivities')) {
            $this->loadCustomerActivities($customerId);
        } else {
            // Manual fallback if you load variables inline
            $this->template->selectedCustomer = $this->database->table('customers')->get($customerId);
            $this->template->activities = $this->database->table('activities')
                ->where($foreignKey, $customerId)
                ->order('created_at DESC')
                ->limit(50);
        }

        // 6. Handle the AJAX snippet redraw response cycle cleanly
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

        // Try standard variations via graceful fallbacks to find your schema column
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