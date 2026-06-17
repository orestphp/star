<?php
declare(strict_types=1);

namespace App\Repository;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ActivityRepository
{
    // DB Table
    private $table = 'activities';
    
    public function __construct(private Explorer $database) {}

    /**
     * Tries multiple column fallbacks to find activities safe from schema drift
     */
    public function findByCustomerId(int $customerId, int $limit = 50): array
    {
        $columnFallbacks = ['customer_id', 'id_user', 'id_customer', 'user_id'];

        foreach ($columnFallbacks as $column) {
            try {
                return $this->database->table($this->table)
                    ->where("$column = ?", $customerId)
                    ->order('created_at DESC')
                    ->limit($limit)
                    ->fetchAll();
            } catch (\Nette\Database\DriverException $e) {
                if ($column === end($columnFallbacks)) {
                    throw $e;
                }
                continue;
            }
        }
        return [];
    }

    public function insert(array $data): ActiveRow
    {
        return $this->database->table($this->table)->insert($data);
    }

    /**
     * Inspects schema dynamically for column variations
     */
    public function getSchemaKeys(): array
    {
        $existingRow = $this->database->table($this->table)->limit(1)->fetch();
        return $existingRow ? array_keys($existingRow->toArray()) : ['customer_id', 'detail', 'type', 'created_at'];
    }
}