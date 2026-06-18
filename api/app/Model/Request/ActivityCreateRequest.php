<?php
// [app/Model/Request/ActivityCreateRequest.php]

declare(strict_types=1);

namespace App\Model\Request;

use Nette\Http\IRequest;
use Webmozart\Assert\Assert;

final class ActivityCreateRequest
{
    public function __construct(
        public int $customerId,
        public string $type,
        public ?string $comment
    ) {}

    /**
     * Validate Input Data
     */
    public static function fromHttpRequest(IRequest $httpRequest): self
    {
        // 1. Extract parameter tokens regardless of payload format (POST or JSON)
        $postData = $httpRequest->getPost();

        $customerId = isset($postData['customerId']) ? (int)$postData['customerId'] : 0;
        $type = strtoupper(trim((string)($postData['type'] ?? '')));
        $comment = isset($postData['comment']) ? trim((string)$postData['comment']) : null;

        // 2. Perform Strict Validation Rules (Throws Exception if criteria fails)
        Assert::greaterThan($customerId, 0, 'Invalid Customer ID');
        Assert::inArray($type, ['COMMENT', 'CALL', 'EMAIL', 'MEETING', 'SYSTEM'], 'Invalid activity type.');

        return new self($customerId, $type, $comment);
    }
}