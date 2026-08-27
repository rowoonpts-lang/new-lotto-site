-- Lotto Platform SMS history
-- Purpose:
--   Keep permanent SMS/LMS history independently from the OShot queue.
--   Member history is linked by mb_id so phone-number changes do not
--   disconnect previous SMS records.

CREATE TABLE `l_sms_history` (
    `lsh_id` bigint unsigned NOT NULL AUTO_INCREMENT,

    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `sender_mb_id` varchar(20) NOT NULL DEFAULT '',

    `receiver_phone` varchar(15) NOT NULL DEFAULT '',
    `sender_phone` varchar(15) NOT NULL DEFAULT '',

    `send_type` varchar(10) NOT NULL DEFAULT '',
    `send_category` varchar(30) NOT NULL DEFAULT '',
    `subject` varchar(120) NOT NULL DEFAULT '',
    `message` text,

    `oshot_msg_id` int unsigned DEFAULT NULL,
    `oshot_group_id` varchar(20) NOT NULL DEFAULT '',

    `send_status` varchar(20) NOT NULL DEFAULT 'queued',
    `send_result` smallint DEFAULT NULL,
    `result_message` varchar(300) NOT NULL DEFAULT '',

    `queued_at` datetime DEFAULT NULL,
    `sent_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lsh_id`),
    UNIQUE KEY `oshot_msg_id` (`oshot_msg_id`),
    KEY `mb_id_queued_at` (`mb_id`, `queued_at`),
    KEY `sender_mb_id_queued_at` (`sender_mb_id`, `queued_at`),
    KEY `send_category` (`send_category`),
    KEY `queued_at` (`queued_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb3
  COLLATE=utf8mb3_general_ci;
