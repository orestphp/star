<?php
declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;
use App\Service\CustomerService;

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
        private CustomerService $customerService
    ) {}

    protected function startup(): void
    {
        parent::startup();

        // Force authentication check globally
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        // Home/Dashboard Authorization check
        $userRole = $this->getUser()->getRoles()[0] ?? '';
        if (!in_array($userRole, ['admin', 'operator'], true)) {
            $this->getUser()->logout(true);
            $this->flashMessage('Access denied. Insufficient account privileges.', 'danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderDefault(): void
    {
        $session = $this->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        // Force get or create a token space in Nette's standard location
        $section = $session->getSection('Nette.Forms.Csrf/token');
        if (!isset($section->token)) {
            $section->token = bin2hex(random_bytes(16));
        }

        $this->template->csrfToken = $section->token;

        $hasSignalAction = (bool) $this->getParameter('do');
        if ($this->isAjax() && !$hasSignalAction) {
            $this->selectedCustomerId = null;
        }

        // Gather dashboard context datasets
        $dashboardData = $this->customerService->getCustomerManagementData(
            $this->search,
            $this->status,
            $this->sort,
            $this->selectedCustomerId
        );

        // Bind parameter states to the template view
        $this->template->search = $this->search;
        $this->template->status = $this->status;
        $this->template->sort = $this->sort;

        $this->template->customers = $dashboardData['customers'];
        $this->template->selectedCustomer = $dashboardData['selectedCustomer'];
        $this->template->activities = $dashboardData['activities'];

        // Redraw target layout blocks
        if ($this->isAjax()) {
            $this->redrawControl('customersSnippet');
            $this->redrawControl('activitiesSnippet');
        }
    }

    // Get Customer-Activities
    public function handleLoadActivities(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;

        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

    // Add Customer-Activity
    public function handleAddActivity(int $customerId, string $comment, string $type = 'COMMENT'): void
    {
        $this->customerService->createActivity($customerId, $comment, $type);
        $this->selectedCustomerId = $customerId;

        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

    // Get Customer-Activity-Comments
    public function handleGetComments(int $activityId): void
    {
        $comments = $this->customerService->getActivityComments($activityId);
        $this->sendJson(['comments' => $comments]);
    }

    // Add Customer-Activity-Comment
    public function handleAddActivityComment(): void
    {
        $activityId = (int) $this->getParameter('activityId');
        $text = $this->getParameter('text');

        if (empty(trim($text))) {
            $this->sendJson(['success' => false, 'error' => 'Comment body context block cannot be blank.']);
            return;
        }

        $userId = $this->getUser()->getId() ?? 1;
        $this->customerService->addCommentToActivity($activityId, $userId, (string)$text);

        $this->sendJson(['success' => true]);
    }
}