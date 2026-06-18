<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\ActivityRepository;
use App\Repository\CommentRepository;

/**
 * Facade
 *
 * Class CustomerService
 * @package App\Service
 */
class CustomerService
{
    public function __construct(
        private UserRepository $userRepository,
        private ActivityRepository $activityRepository,
        private CommentRepository $commentRepository
    ) {}

    public function getCustomerManagementData(string $search, string $status, string $sort, ?int $selectedCustomerId): array
    {
        return [
            'customers' => $this->userRepository->findCustomers($search, $status, $sort)->fetchAll(),
            'selectedCustomer' => $selectedCustomerId ? $this->userRepository->get($selectedCustomerId) : null,
            'activities' => $selectedCustomerId ? $this->activityRepository->findByCustomerId($selectedCustomerId) : [],
        ];
    }

    public function createActivity(int $customerId, string $comment, string $type): void
    {
        $activityType = strtoupper(trim($type));

        // Validate type
        $allowedTypes = ['COMMENT', 'CALL', 'EMAIL', 'MEETING', 'SYSTEM'];
        if (!in_array($activityType, $allowedTypes, true)) {
            throw new \InvalidArgumentException("Invalid activity type: {$type}");
        }

        $insertData = [
            'customer_id' => $customerId,
            'type'        => $activityType,
            'details'     => empty(trim($comment)) ? null : trim($comment), // Handles nullable text column safely
            'created_at'  => new \DateTime(),
        ];

        $this->activityRepository->insert($insertData);
    }

    public function getActivityComments(int $activityId): array
    {
        $rows = $this->commentRepository->findByActivityId($activityId);

        $commentsPayload = [];
        foreach ($rows as $row) {
            $commentsPayload[] = [
                'id'          => $row->id,
                'user_id'     => $row->user_id ?? 1,
                'text'        => $row->text,
                'created_at'  => $row->created_at->format('Y-m-d H:i:s'),
            ];
        }
        return $commentsPayload;
    }

    public function addCommentToActivity(int $activityId, int $userId, string $text): void
    {
        $this->commentRepository->insert([
            'activity_id' => $activityId,
            'user_id'     => $userId,
            'text'        => trim($text),
            'created_at'  => new \DateTime(),
            'updated_at'  => new \DateTime()// TODO: Just in case soft delete will be implemented
        ]);
    }
}