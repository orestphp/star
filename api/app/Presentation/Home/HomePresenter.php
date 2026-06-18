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

    private string $token;
    
    public function __construct(
        private CustomerService $customerService
    ) {}

    protected function startup(): void
    {
        parent::startup();

        // CSRF token preparation
        $session = $this->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $section = $session->getSection('Nette.Forms.Csrf/token');
        if (!isset($section->token)) {
            $section->token = bin2hex(random_bytes(16));
        }

        $this->token = (string) $section->token;
    }

    public function renderDefault(): void
    {
        // Clean up AJAX signal tracking state
        if ($this->isAjax() && !$this->getParameter('do')) {
            $this->selectedCustomerId = null;
        }

        // Gather dashboard context datasets
        $dashboardData = $this->customerService->getCustomerManagementData(
            $this->search,
            $this->status,
            $this->sort,
            $this->selectedCustomerId
        );

        // Bind parameters cleanly to template
        $this->template->search = $this->search;
        $this->template->status = $this->status;
        $this->template->sort = $this->sort;

        $this->template->customers = $dashboardData['customers'];
        $this->template->selectedCustomer = $dashboardData['selectedCustomer'];
        $this->template->activities = $dashboardData['activities'];

        if ($this->isAjax()) {
            $this->redrawControl('customersSnippet');
            $this->redrawControl('activitiesSnippet');
        }
    }

    // Activity Modal
    protected function createComponentActivityModal(): Components\ActivityModalControl
    {
        return new Components\ActivityModalControl((string)$this->token);
    }

    // Activity Details Modal
    protected function createComponentActivityDetailsModal(): Components\ActivityDetailsModalControl
    {
        return new Components\ActivityDetailsModalControl((string)$this->token);
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
    public function handleAddActivity(): void
    {
        try {
            // Validate POST
            $activityRequest = \App\Model\Request\ActivityCreateRequest::fromHttpRequest($this->getHttpRequest());

            // Pass the safe into service
            $this->customerService->createActivity(
                $activityRequest->customerId,
                $activityRequest->comment ?? '',
                $activityRequest->type
            );

            $this->selectedCustomerId = $activityRequest->customerId;

            if ($this->isAjax()) {
                $this->redrawControl('activitiesSnippet');
            } else {
                $this->redirect('this');
            }

        } catch (\Webmozart\Assert\InvalidArgumentException $e) {
            // Handle validation failure gracefully (for AJAX responses or Flash messages)
            if ($this->isAjax()) {
                $this->getHttpResponse()->setCode(400);
                $this->sendJson(['error' => $e->getMessage()]);
            } else {
                $this->flashMessage($e->getMessage(), 'danger');
                $this->redirect('this');
            }
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