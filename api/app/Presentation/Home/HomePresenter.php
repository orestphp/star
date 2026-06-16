<?php
// [app/Presentation/Home/HomePresenter.php]

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use Nette\Database\Explorer;

/**
 * This class represent "Customer", "Activities" and "Activity Comments" business logic
 *
 * Class HomePresenter
 * @package App\Presentation\Home
 */
final class HomePresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public string $search = '';
    /** @persistent */
    public string $status = 'all';
    /** @persistent */
    public string $sort = 'created_desc';

    /** @persistent */
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

        $this->template->csrfToken = $this->getHttpRequest()->getCookie('nette-samesite')
            ? md5($this->getSession()->getId())
            : $this->getSession()->getSection('Security')->token ??= Nette\Utils\Random::generate();
    }

    /**
     * ⚡ AJAX Signal Handler: Customer Row Click Action
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
     * ⚡ AJAX Signal Handler: Creates standalone parent activity logs
     */
    public function handleAddComment(int $customerId, string $comment, string $type = 'COMMENT'): void
    {
        // 1. Verify the custom CSRF parameter coming from JavaScript
        $passedToken = $this->getParameter('_sec');
        $expectedToken = $this->getSession()->getSection('Security')->token ?? md5($this->getSession()->getId());

        if (!$passedToken || $passedToken !== $expectedToken) {
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S403_FORBIDDEN);
            $this->sendJson(['error' => 'Security token invalid or expired.']);
            return;
        }

        // 2. Safely extract table layout schema columns using dynamic inspection loops
        $existingRow = $this->database->table('activities')->limit(1)->fetch();
        $rowKeys = $existingRow ? array_keys($existingRow->toArray()) : ['customer_id', 'detail', 'type', 'created_at'];

        $foreignKey = 'customer_id';
        foreach (['customer_id', 'id_user', 'id_customer', 'user_id'] as $col) {
            if (in_array($col, $rowKeys, true)) { $foreignKey = $col; break; }
        }

        $textField = 'detail';
        foreach (['detail', 'description', 'details', 'message', 'comment'] as $col) {
            if (in_array($col, $rowKeys, true)) { $textField = $col; break; }
        }

        // 3. Construct clean insertion payload arrays
        $insertData = [
            $foreignKey  => $customerId,
            $textField   => trim($comment),
            'created_at' => new \DateTime(),
        ];

        foreach (['type', 'action_type', 'action'] as $col) {
            if (in_array($col, $rowKeys, true)) {
                $insertData[$col] = strtoupper(trim($type));
                break;
            }
        }

        // Execute save record routine down to database persistence engines
        $this->database->table('activities')->insert($insertData);

        // 4. Reset our persistent state pointer context and reload template data sets cleanly
        $this->selectedCustomerId = $customerId;
        $this->loadCustomerActivities($customerId);

        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * ⚡ AJAX Endpoint: Pulls a structured JSON collection of child comment tracking threads
     */
    public function handleGetComments(int $activityId): void
    {
        $rows = $this->database->table('comments')
            ->where('activity_id = ?', $activityId)
            ->order('created_at ASC')
            ->fetchAll();

        $commentsPayload = [];
        foreach ($rows as $row) {
            $commentsPayload[] = [
                'id'          => $row->id,
                'user_id'     => $row->user_id ?? 1,
                'text'        => $row->text,
                'created_at'  => $row->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->sendJson(['comments' => $commentsPayload]);
    }

    /**
     * ⚡ AJAX Endpoint: Appends an ongoing thread sub-comment record entry securely
     */
    public function handleAddActivityComment(): void
    {
        $passedToken = $this->getParameter('_sec');
        $expectedToken = $this->getSession()->getSection('Security')->token ?? md5($this->getSession()->getId());

        if (!$passedToken || $passedToken !== $expectedToken) {
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S403_FORBIDDEN);
            $this->sendJson(['success' => false, 'error' => 'Security token invalid or expired.']);
            return;
        }

        $activityId = (int) $this->getParameter('activityId');
        $text = $this->getParameter('text');

        if (empty(trim($text))) {
            $this->sendJson(['success' => false, 'error' => 'Comment body context block cannot be blank.']);
            return;
        }

        $this->database->table('comments')->insert([
            'activity_id' => $activityId,
            'user_id'     => $this->getUser()->getId() ?? 1,
            'text'        => trim($text),
            'created_at'  => new \DateTime(),
            'updated_at'  => new \DateTime()
        ]);

        $this->sendJson(['success' => true]);
    }

    /**
     * 🔒 Internal Helper: Populates timeline parameters safe from DriverExceptions
     */
    private function loadCustomerActivities(int $customerId): void
    {
        $customer = $this->database->table('users')->get($customerId);
        if (!$customer) {
            return;
        }

        $this->template->selectedCustomer = $customer;
        $columnFallbacks = ['customer_id', 'id_user', 'id_customer', 'user_id'];

        foreach ($columnFallbacks as $column) {
            try {
                $this->template->activities = $this->database->table('activities')
                    ->where("$column = ?", $customerId)
                    ->order('created_at DESC')
                    ->limit(50)
                    ->fetchAll();

                break;
            } catch (\Nette\Database\DriverException $e) {
                if ($column === end($columnFallbacks)) {
                    throw $e;
                }
                continue;
            }
        }
    }
}