<?php
declare(strict_types=1);

namespace App\Repository;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class CommentRepository
{
    // DB Table
    private $table = 'comments';

    public function __construct(private Explorer $database) {}

    public function findByActivityId(int $activityId): array
    {
        return $this->database->table($this->table)
            ->where('activity_id = ?', $activityId)
            ->order('created_at ASC')
            ->fetchAll();
    }

    public function insert(array $data):\Nette\Database\Table\ActiveRow
    {
        return $this->database->table($this->table)->insert($data);
    }
}