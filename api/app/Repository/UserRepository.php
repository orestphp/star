<?php
declare(strict_types=1);

namespace App\Repository;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;

class UserRepository
{
    // DB Table
    private $table = 'users';

    public function __construct(private Explorer $database) {}

    public function findCustomers(string $search = '', string $status = 'all', string $sort = 'created_desc'): Selection
    {
        $query = $this->database->table($this->table)->where('role = ?', 'customer');

        if (!empty($search)) {
            $tokens = '%' . $search . '%';
            $query->where('name LIKE ? OR email LIKE ?', $tokens, $tokens);
        }

        if ($status === 'active') {
            $query->where('is_active = ?', 1);
        } elseif ($status === 'inactive') {
            $query->where('is_active = ?', 0);
        }

        switch ($sort) {
            case 'name_asc': $query->order('name ASC'); break;
            case 'name_desc': $query->order('name DESC'); break;
            case 'created_asc': $query->order('created_at ASC'); break;
            case 'created_desc':
            default:
                $query->order('created_at DESC');
                break;
        }

        return $query;
    }

    public function get(int $id): ?ActiveRow
    {
        return $this->database->table($this->table)->get($id);
    }
}