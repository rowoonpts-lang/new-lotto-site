-- Payment sales cancellation history
-- Supports multiple partial cancellations for one approved sale.
-- Original l_sales.sale_amount is never overwritten.

CREATE TABLE `l_sales_cancel` (
    `lsc_id` int(11) NOT NULL AUTO_INCREMENT,
    `ls_id` int(11) NOT NULL,
    `lpr_id` int(11) NOT NULL,
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `cancel_amount` decimal(15,0) NOT NULL DEFAULT 0,
    `cancel_reason` varchar(255) NOT NULL DEFAULT '',
    `cancelled_by` varchar(20) NOT NULL DEFAULT '',
    `cancelled_at` datetime NOT NULL,
    `created_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lsc_id`),
    KEY `ls_id` (`ls_id`),
    KEY `lpr_id` (`lpr_id`),
    KEY `mb_id` (`mb_id`),
    KEY `cancelled_by` (`cancelled_by`),
    KEY `cancelled_at` (`cancelled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
