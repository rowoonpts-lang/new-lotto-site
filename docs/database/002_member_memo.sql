-- Lotto Platform member memo table
-- Purpose: restore member memo and alarm storage

CREATE TABLE `l_memo` (
    `lm_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `from_mb_id` varchar(20) NOT NULL DEFAULT '',
    `lm_memo_type` varchar(100) NOT NULL DEFAULT '',
    `lm_memo` text,
    `lm_misu` varchar(50) NOT NULL DEFAULT '',
    `lm_alarm_type` varchar(100) NOT NULL DEFAULT '',
    `lm_alarm_date` varchar(50) NOT NULL DEFAULT '',
    `lm_alarm_view` tinyint NOT NULL DEFAULT 0,
    `lm_price` varchar(50) NOT NULL DEFAULT '',
    `lm_datetime` datetime DEFAULT NULL,
    `st_tp` tinyint NOT NULL DEFAULT 1,
    `etc_1` varchar(50) NOT NULL DEFAULT '',

    PRIMARY KEY (`lm_id`),
    KEY `mb_id` (`mb_id`),
    KEY `from_mb_id` (`from_mb_id`),
    KEY `lm_alarm_date` (`lm_alarm_date`),
    KEY `lm_alarm_view` (`lm_alarm_view`),
    KEY `st_tp` (`st_tp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
