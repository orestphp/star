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

        // Role validation
        $userRole = $this->getUser()->getRoles()[0] ?? ''; // Returns array of roles from SimpleIdentity
        if (!in_array($userRole, ['admin', 'operator'], true)) {
            $this->getUser()->logout(true); // Evict user identity structure completely
            $this->flashMessage('Access denied. Insufficient account privileges.', 'danger');
            $this->redirect('Sign:in');
        }
    }

    public function renderDefault(): void
    {
        $data = $this->customerService->getCustomerManagementData(
            $this->search,
            $this->status,
            $this->sort,
            $this->selectedCustomerId
        );

        $this->template->customers = $data['customers'];
        $this->template->selectedCustomer = $data['selectedCustomer'];
        $this->template->activities = $data['activities'];

        $this->template->search = $this->search;
        $this->template->status = $this->status;
        $this->template->sort = $this->sort;

        // Redraw the customer snippet block if requested via AJAX search/filters
        if ($this->isAjax()) {
            $this->redrawControl('customersSnippet');
        }

        $section = $this->getSession()->getSection('Nette.Forms.Form');
        $this->template->csrfToken = $section->token ??= Nette\Utils\Random::generate();
    }

    public function handleLoadActivities(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;

        if ($this->isAjax()) {
            $this->redrawControl('activitiesSnippet');
        } else {
            $this->redirect('this');
        }
    }

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

    public function handleGetComments(int $activityId): void
    {
        $comments = $this->customerService->getActivityComments($activityId);
        $this->sendJson(['comments' => $comments]);
    }

    public function handleAddActivityComment(): void
    {
        $activityId = (int) $this->getParameter('activityId');
        $text = $this->getParameter('text');

        if (empty(trim($text))) {
            $this->sendJson(['success' => false, 'error' => 'Comment body context block cannot be blank.']);
            return;
        }

        $userId = $this->getUser()->getId() ?? 1;
        $this->customerService->addCommentToActivity($activityId, $userId, $text);

        $this->sendJson(['success' => true]);
    }
}