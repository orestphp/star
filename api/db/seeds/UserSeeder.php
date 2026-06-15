<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $sql = <<<'SQL'
        -- Clear old test data cleanly (respecting foreign key dependency order)
        SET FOREIGN_KEY_CHECKS = 0;
        TRUNCATE TABLE `comments`;
        TRUNCATE TABLE `activities`;
        TRUNCATE TABLE `customers`;
        TRUNCATE TABLE `users`;
        SET FOREIGN_KEY_CHECKS = 1;

        -- ============================================================================
        -- 1. SEED USERS ('admin', 'operator', 'customer')
        -- All accounts utilize the password: 'password'
        -- ============================================================================
        INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
        (1, 'System Administrator', 'admin@admin.com', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'admin', 1, '2026-01-01 09:00:00'),
        (2, 'Alex Operator', 'operator1@crm.com', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'operator', 1, '2026-01-02 10:00:00'),
        (3, 'Emma Operator', 'operator2@crm.com', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'operator', 1, '2026-01-02 11:15:00'),
        (4, 'John Doe (Acme Corp)', 'john.doe@acme.com', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'customer', 1, '2026-01-05 14:00:00'),
        (5, 'Jane Smith (Global Tech)', 'jane.smith@globaltech.com', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'customer', 1, '2026-01-06 09:30:00'),
        (6, 'Bob Johnson (Local Shop)', 'bob@localshop.org', '$2y$12$n1i3e87UZIjVYDR60wY1gOvoGqe.1WleIiWvK44yP/YQGAjWQTUGq', 'customer', 0, '2026-01-10 16:45:00');

        -- ============================================================================
        -- 2. SEED CUSTOMERS PROFILES (1-to-1 Mapping via Shared User IDs)
        -- ============================================================================
        INSERT INTO `customers` (`id`, `phone`) VALUES
        (4, '+1-555-0198'),
        (5, '+44-20-7946-0958'),
        (6, NULL);

        -- ============================================================================
        -- 3. SEED ACTIVITIES
        -- Detailed log records matching types, details, and sequential timestamps.
        -- ============================================================================
        INSERT INTO `activities` (`id`, `customer_id`, `type`, `details`, `created_at`) VALUES
        (1, 5, 'email', 'Sent onboarding introduction email and API contract documentation.', '2026-01-06 10:00:00'),
        (2, 5, 'call', 'Inbound call from Jane regarding integration pricing options.', '2026-01-07 11:30:00'),
        (3, 6, 'meeting', 'Initial physical discovery meeting at their local retail storefront office.', '2026-01-11 13:00:00'),
        (4, 4, 'email', 'System welcome notification message processed successfully.', '2026-01-05 14:05:00'),
        (5, 4, 'call', 'Outbound discovery call. Discussed primary CRM synchronization needs.', '2026-01-05 15:30:00'),
        (6, 4, 'meeting', 'Remote screen-share presentation of software capabilities.', '2026-01-08 10:00:00'),
        (7, 4, 'email', 'Sent custom quotation for enterprise server licensing add-ons.', '2026-01-08 16:45:00'),
        (8, 4, 'call', 'Follow up regarding contract terms. Requested adjustments to section 4.', '2026-01-12 09:15:00'),
        (9, 4, 'email', 'Dispatched revised contract documentation.', '2026-01-12 11:00:00'),
        (10, 4, 'system', 'Contract signed digitally via external processing portal.', '2026-01-15 17:02:00'),
        (11, 4, 'meeting', 'Technical kickoff workshop with development engineers.', '2026-01-19 14:00:00'),
        (12, 4, 'task', 'Created internal system configuration profile for cloud stack routing.', '2026-01-20 08:30:00'),
        (13, 4, 'email', 'Sent access credentials and secure keys to their security lead.', '2026-01-20 16:00:00'),
        (14, 4, 'call', 'Urgent check-in. Client reported connection timeout on payload delivery.', '2026-01-22 11:12:00'),
        (15, 4, 'system', 'Automated system profile adjustment applied by server optimization.', '2026-01-22 11:45:00'),
        (16, 4, 'email', 'Confirmation message dispatched verifying resolution of payload drops.', '2026-01-22 12:30:00'),
        (17, 4, 'call', 'Routine account management monthly follow-up call. Feedback highly positive.', '2026-02-22 10:00:00'),
        (18, 4, 'email', 'Dispatched platform roadmap preview release notification notes.', '2026-03-01 09:15:00'),
        (19, 4, 'call', 'Inquiry regarding additional pipeline processing options.', '2026-04-15 14:22:00'),
        (20, 4, 'meeting', 'Q2 Strategy review panel session.', '2026-05-18 11:00:00');

        -- ============================================================================
        -- 4. SEED COMMENTS
        -- Simulating internal employee discussion blocks on those specific activities.
        -- ============================================================================
        INSERT INTO `comments` (`id`, `activity_id`, `user_id`, `text`, `created_at`) VALUES
        (1, 2, 2, 'Jane sounded hesitant about enterprise costs. Might need to offer a bundle discount next week.', '2026-01-07 11:45:00'),
        (2, 2, 1, 'Approved. Alex, you can drop up to 15% on the base fee if she commits to a 24-month term.', '2026-01-07 14:10:00'),
        (3, 14, 3, 'Investigating network logs now. Looks like a firewall rule was blocking their specific static IP block.', '2026-01-22 11:20:00'),
        (4, 14, 2, 'Confirmed with Emma. The routing tables are clean now. Closing out support incident.', '2026-01-22 12:25:00');
        SQL;

        // Execute raw query script directly on the PDO instance
        $this->execute($sql);
    }
}