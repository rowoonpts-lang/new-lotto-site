-- Lotto Platform payment approval, sales, and notification tables
-- Purpose:
-- 1. Store staff payment approval requests without completing payment immediately.
-- 2. Record sales only after an administrator completes approval.
-- 3. Notify the assigned staff member after approval.
--
-- Important security note:
-- Sensitive card authentication data is intentionally NOT stored here.
-- A separate secure card-handling design must be confirmed with the active PG/acquirer
-- before card approval-request implementation is enabled.

CREATE TABLE `l_payment_request` (
    `lpr_id` int NOT NULL AUTO_INCREMENT,
    `request_no` varchar(40) NOT NULL DEFAULT '',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `staff_mb_id` varchar(20) NOT NULL DEFAULT '',
    `requested_by` varchar(20) NOT NULL DEFAULT '',
    `payment_method` varchar(20) NOT NULL DEFAULT '',
    `product_type` varchar(100) NOT NULL DEFAULT '',
    `request_amount` decimal(15,0) NOT NULL DEFAULT 0,
    `request_status` varchar(20) NOT NULL DEFAULT '승인대기',

    `member_phone` varchar(50) NOT NULL DEFAULT '',
    `bank_account` varchar(255) NOT NULL DEFAULT '',
    `depositor_name` varchar(100) NOT NULL DEFAULT '',
    `sms_send` tinyint NOT NULL DEFAULT 0,

    `card_company` varchar(100) NOT NULL DEFAULT '',
    `installment_months` tinyint NOT NULL DEFAULT 0,

    `approved_amount` decimal(15,0) NOT NULL DEFAULT 0,
    `approved_by` varchar(20) NOT NULL DEFAULT '',
    `approved_at` datetime DEFAULT NULL,
    `rejected_by` varchar(20) NOT NULL DEFAULT '',
    `rejected_at` datetime DEFAULT NULL,
    `reject_reason` varchar(255) NOT NULL DEFAULT '',

    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lpr_id`),
    UNIQUE KEY `request_no` (`request_no`),
    KEY `mb_id` (`mb_id`),
    KEY `staff_mb_id` (`staff_mb_id`),
    KEY `requested_by` (`requested_by`),
    KEY `payment_method` (`payment_method`),
    KEY `request_status` (`request_status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


CREATE TABLE `l_sales` (
    `ls_id` int NOT NULL AUTO_INCREMENT,
    `lpr_id` int NOT NULL,
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `staff_mb_id` varchar(20) NOT NULL DEFAULT '',
    `payment_method` varchar(20) NOT NULL DEFAULT '',
    `product_type` varchar(100) NOT NULL DEFAULT '',
    `sale_amount` decimal(15,0) NOT NULL DEFAULT 0,
    `approved_by` varchar(20) NOT NULL DEFAULT '',
    `approved_at` datetime DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,

    PRIMARY KEY (`ls_id`),
    UNIQUE KEY `lpr_id` (`lpr_id`),
    KEY `mb_id` (`mb_id`),
    KEY `staff_mb_id` (`staff_mb_id`),
    KEY `approved_at` (`approved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


CREATE TABLE `l_notification` (
    `ln_id` int NOT NULL AUTO_INCREMENT,
    `recipient_mb_id` varchar(20) NOT NULL DEFAULT '',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `notification_type` varchar(50) NOT NULL DEFAULT '',
    `title` varchar(255) NOT NULL DEFAULT '',
    `message` text,
    `reference_type` varchar(50) NOT NULL DEFAULT '',
    `reference_id` int NOT NULL DEFAULT 0,
    `is_read` tinyint NOT NULL DEFAULT 0,
    `read_at` datetime DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,

    PRIMARY KEY (`ln_id`),
    KEY `recipient_mb_id` (`recipient_mb_id`),
    KEY `mb_id` (`mb_id`),
    KEY `notification_type` (`notification_type`),
    KEY `is_read` (`is_read`),
    KEY `created_at` (`created_at`),
    KEY `reference` (`reference_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
