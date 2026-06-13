CREATE TABLE `users` (
                         `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                         `name` VARCHAR(100) NOT NULL,
                         `email` VARCHAR(150) NOT NULL UNIQUE, -- Automatically indexed by MySQL
                         `password` VARCHAR(255) NOT NULL,
                         `role` VARCHAR(50) NOT NULL,          -- 'admin', 'operator', or 'customer'
                         `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                         `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Speed up filtering active/inactive system operators or admins
                         INDEX `idx_users_role_active` (`role`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
                             `id` INT UNSIGNED PRIMARY KEY,         -- Automatically indexed by MySQL
                             `phone` VARCHAR(30) NULL,

                             CONSTRAINT `fk_customers_user` FOREIGN KEY (`id`)
                                 REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activities` (
                              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                              `customer_id` INT UNSIGNED NOT NULL,
                              `type` VARCHAR(50) NOT NULL,          -- e.g., 'call', 'email', 'meeting'
                              `details` TEXT NULL,
                              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

                              CONSTRAINT `fk_activities_customer` FOREIGN KEY (`customer_id`)
                                  REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    -- CRITICAL: Speeds up building a customer's chronological activity timeline
                              INDEX `idx_activities_customer_date` (`customer_id`, `created_at`),
    -- Speeds up general activity analytics/filtering by type (e.g., all phone calls)
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

    -- CRITICAL: Speeds up fetching comments belonging to an active stream block
                            INDEX `idx_comments_activity` (`activity_id`),
    -- Speeds up tracking what comment an operator or user posted
                            INDEX `idx_comments_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;