-- Lotto Platform payment bank account table
-- Purpose:
-- 1. Manage bank accounts used for bank-transfer payment approval requests.
-- 2. Replace the legacy cf_mu_num custom config field that is not present in the current g5_config schema.
-- 3. Allow multiple active accounts and stable ordering without changing g5_config.

CREATE TABLE `l_payment_bank_account` (
    `lpba_id` int NOT NULL AUTO_INCREMENT,
    `bank_name` varchar(100) NOT NULL DEFAULT '',
    `account_number` varchar(100) NOT NULL DEFAULT '',
    `account_holder` varchar(100) NOT NULL DEFAULT '',
    `is_active` tinyint NOT NULL DEFAULT 1,
    `sort_order` int NOT NULL DEFAULT 0,
    `created_by` varchar(20) NOT NULL DEFAULT '',
    `updated_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lpba_id`),
    UNIQUE KEY `bank_account` (`bank_name`, `account_number`),
    KEY `is_active_sort_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
