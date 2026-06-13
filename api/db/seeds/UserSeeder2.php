<?php
/*
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. Clear old data respecting foreign key constraint dependencies
        $clearSql = <<<SQL
            SET FOREIGN_KEY_CHECKS = 0;
            TRUNCATE TABLE `comments`;
            TRUNCATE TABLE `activities`;
            TRUNCATE TABLE `customers`;
            TRUNCATE TABLE `users`;
            SET FOREIGN_KEY_CHECKS = 1;
        SQL;

        $this->execute($clearSql);

        // 2. Insert Users
        $usersTable = $this->table('users');
        $usersData = [
            ['id' => 1, 'name' => 'System Administrator', 'email' => 'admin@admin.com', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'admin', 'is_active' => 1, 'created_at' => '2026-01-01 09:00:00'],
            ['id' => 2, 'name' => 'Alex Operator', 'email' => 'operator1@crm.com', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'operator', 'is_active' => 1, 'created_at' => '2026-01-02 10:00:00'],
            ['id' => 3, 'name' => 'Emma Operator', 'email' => 'operator2@crm.com', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'operator', 'is_active' => 1, 'created_at' => '2026-01-02 11:15:00'],
            ['id' => 4, 'name' => 'John Doe (Acme Corp)', 'email' => 'john.doe@acme.com', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'customer', 'is_active' => 1, 'created_at' => '2026-01-05 14:00:00'],
            ['id' => 5, 'name' => 'Jane Smith (Global Tech)', 'email' => 'jane.smith@globaltech.com', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'customer', 'is_active' => 1, 'created_at' => '2026-01-06 09:30:00'],
            ['id' => 6, 'name' => 'Bob Johnson (Local Shop)', 'email' => 'bob@localshop.org', 'password' => '$2y$10$Eefb6p8Jm7Z3P8Cg97p1reD2C2O9u4WbY8a6C8g8h8i8j8k8l8m8n', 'role' => 'customer', 'is_active' => 0, 'created_at' => '2026-01-10 16:45:00']
        ];
        $usersTable->insert($usersData)->saveData();

        // 3. Insert Customers (Omit created_at to match your exact structural definition)
        $customersTable = $this->table('customers');
        $customersData = [
            ['id' => 4, 'phone' => '+1-555-0198'],
            ['id' => 5, 'phone' => '+44-20-7946-0958'],
            ['id' => 6, 'phone' => null]
        ];
        $customersTable->insert($customersData)->saveData();

        // 4. Insert Activities
        $activitiesTable = $this->table('activities');
        $activitiesData = [
            ['id' => 1, 'customer_id' => 5, 'type' => 'email', 'details' => 'Sent onboarding introduction email and API contract documentation.', 'created_at' => '2026-01-06 10:00:00'],
            ['id' => 2, 'customer_id' => 5, 'type' => 'call', 'details' => 'Inbound call from Jane regarding integration pricing options.', 'created_at' => '2026-01-07 11:30:00'],
            ['id' => 3, 'customer_id' => 6, 'type' => 'meeting', 'details' => 'Initial physical discovery meeting at their local retail storefront office.', 'created_at' => '2026-01-11 13:00:00'],
            ['id' => 4, 'customer_id' => 4, 'type' => 'email', 'details' => 'System welcome notification message processed successfully.', 'created_at' => '2026-01-05 14:05:00'],
            ['id' => 5, 'customer_id' => 4, 'type' => 'call', 'details' => 'Outbound discovery call. Discussed primary CRM synchronization needs.', 'created_at' => '2026-01-05 15:30:00'],
            ['id' => 6, 'customer_id' => 4, 'type' => 'meeting', 'details' => 'Remote screen-share presentation of software capabilities.', 'created_at' => '2026-01-08 10:00:00'],
            ['id' => 7, 'customer_id' => 4, 'type' => 'email', 'details' => 'Sent custom quotation for enterprise server licensing add-ons.', 'created_at' => '2026-01-08 16:45:00'],
            ['id' => 8, 'customer_id' => 4, 'type' => 'call', 'details' => 'Follow up regarding contract terms. Requested adjustments to section 4.', 'created_at' => '2026-01-12 09:15:00'],
            ['id' => 9, 'customer_id' => 4, 'type' => 'email', 'details' => 'Dispatched revised contract documentation.', 'created_at' => '2026-01-12 11:00:00'],
            ['id' => 10, 'customer_id' => 4, 'type' => 'system', 'details' => 'Contract signed digitally via external processing portal.', 'created_at' => '2026-01-15 17:02:00'],
            ['id' => 11, 'customer_id' => 4, 'type' => 'meeting', 'details' => 'Technical kickoff workshop with development engineers.', 'created_at' => '2026-01-19 14:00:00'],
            ['id' => 12, 'customer_id' => 4, 'type' => 'task', 'details' => 'Created internal system configuration profile for cloud stack routing.', 'created_at' => '2026-01-20 08:30:00'],
            ['id' => 13, 'customer_id' => 4, 'type' => 'email', 'details' => 'Sent access credentials and secure keys to their security lead.', 'created_at' => '2026-01-20 16:00:00'],
            ['id' => 14, 'customer_id' => 4, 'type' => 'call', 'details' => 'Urgent check-in. Client reported connection timeout on payload delivery.', 'created_at' => '2026-01-22 11:12:00'],
            ['id' => 15, 'customer_id' => 4, 'type' => 'system', 'details' => 'Automated system profile adjustment applied by server optimization.', 'created_at' => '2026-01-22 11:45:00'],
            ['id' => 16, 'customer_id' => 4, 'type' => 'email', 'details' => 'Confirmation message dispatched verifying resolution of payload drops.', 'created_at' => '2026-01-22 12:30:00'],
            ['id' => 17, 'customer_id' => 4, 'type' => 'call', 'details' => 'Routine account management monthly follow-up call. Feedback highly positive.', 'created_at' => '2026-02-22 10:00:00'],
            ['id' => 18, 'customer_id' => 4, 'type' => 'email', 'details' => 'Dispatched platform roadmap preview release notification notes.', 'created_at' => '2026-03-01 09:15:00'],
            ['id' => 19, 'customer_id' => 4, 'type' => 'call', 'details' => 'Inquiry regarding additional pipeline processing options.', 'created_at' => '2026-04-15 14:22:00'],
            ['id' => 20, 'customer_id' => 4, 'type' => 'meeting', 'details' => 'Q2 Strategy review panel session.', 'created_at' => '2026-05-18 11:00:00']
        ];
        $activitiesTable->insert($activitiesData)->saveData();

        // 5. Insert Comments
        $commentsTable = $this->table('comments');
        $commentsData = [
            ['id' => 1, 'activity_id' => 2, 'user_id' => 2, 'text' => 'Jane sounded hesitant about enterprise costs. Might need to offer a bundle discount next week.', 'created_at' => '2026-01-07 11:45:00'],
            ['id' => 2, 'activity_id' => 2, 'user_id' => 1, 'text' => 'Approved. Alex, you can drop up to 15% on the base fee if she commits to a 24-month term.', 'created_at' => '2026-01-07 14:10:00'],
            ['id' => 3, 'activity_id' => 14, 'user_id' => 3, 'text' => 'Investigating network logs now. Looks like a firewall rule was blocking their specific static IP block.', 'created_at' => '2026-01-22 11:20:00'],
            ['id' => 4, 'activity_id' => 14, 'user_id' => 2, 'text' => 'Confirmed with Emma. The routing tables are clean now. Closing out support incident.', 'created_at' => '2026-01-22 12:25:00']
        ];
        $commentsTable->insert($commentsData)->saveData();
    }
}
*/