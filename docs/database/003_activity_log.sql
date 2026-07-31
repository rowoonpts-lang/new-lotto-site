-- Lotto Platform activity log table
-- Purpose: restore administrator activity logging

CREATE TABLE `l_log` (
    `ll_id` int NOT NULL AUTO_INCREMENT,
    `ll_ip` varchar(45) NOT NULL DEFAULT '',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `ll_content` text,
    `ll_datetime` datetime DEFAULT NULL,

    PRIMARY KEY (`ll_id`),
    KEY `mb_id` (`mb_id`),
    KEY `ll_datetime` (`ll_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
