-- Lotto Platform temporary card payment request secrets
--
-- Purpose:
-- Store card details only while an administrator needs them to process
-- a pending manual card payment request.
--
-- Security:
-- 1. Plain card data must never be stored in this table.
-- 2. encrypted_payload contains authenticated encrypted data only.
-- 3. The encryption key must not be stored in the database or Git.
-- 4. Sensitive payload must be cleared after approval/rejection.
-- 5. l_sales must never contain sensitive card data.

CREATE TABLE `l_payment_card_secret` (
    `lpcs_id` int NOT NULL AUTO_INCREMENT,
    `lpr_id` int NOT NULL,
    `encrypted_payload` mediumtext NOT NULL,
    `card_last4` char(4) NOT NULL DEFAULT '',
    `key_version` int NOT NULL DEFAULT 1,
    `created_at` datetime DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `cleared_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lpcs_id`),
    UNIQUE KEY `lpr_id` (`lpr_id`),
    KEY `expires_at` (`expires_at`),
    KEY `cleared_at` (`cleared_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb3
  COLLATE=utf8mb3_general_ci;
