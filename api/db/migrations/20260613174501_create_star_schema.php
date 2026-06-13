<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStarSchema extends AbstractMigration
{
    public function up(): void
    {
        $sql = <<<'SQL'
        CREATE TABLE `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL,
            `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_users_role_active` (`role`, `is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE `customers` (
            `id` INT UNSIGNED PRIMARY KEY,
            `phone` VARCHAR(30) NULL,
            CONSTRAINT `fk_customers_user` FOREIGN KEY (`id`)
                REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE `activities` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `customer_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `details` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_activities_customer` FOREIGN KEY (`customer_id`)
                REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX `idx_activities_customer_date` (`customer_id`, `created_at`),
            INDEX `idx_activities_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE `comments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `activity_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `text` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_comments_activity` FOREIGN KEY (`activity_id`)
                REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`)
                REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX `idx_comments_activity` (`activity_id`),
            INDEX `idx_comments_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->table('comments')->drop()->save();
        $this->table('activities')->drop()->save();
        $this->table('customers')->drop()->save();
        $this->table('users')->drop()->save();
    }
}