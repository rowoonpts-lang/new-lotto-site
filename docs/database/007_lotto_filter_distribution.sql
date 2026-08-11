CREATE TABLE IF NOT EXISTS `l_filter_run` (
    `lfr_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `draw_no` int unsigned NOT NULL,
    `source_draw_no` int unsigned NOT NULL DEFAULT 0,
    `status` varchar(30) NOT NULL DEFAULT 'ready',
    `total_combinations` int unsigned NOT NULL DEFAULT 8145060,
    `candidate_count` int unsigned NOT NULL DEFAULT 0,
    `excluded_numbers` varchar(255) NOT NULL DEFAULT '',
    `started_at` datetime DEFAULT NULL,
    `filtered_at` datetime DEFAULT NULL,
    `ranked_at` datetime DEFAULT NULL,
    `distributed_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    `last_error` text,
    `created_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lfr_id`),
    UNIQUE KEY `uq_filter_run_draw` (`draw_no`),
    KEY `idx_filter_run_status` (`status`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_filter_candidate` (
    `lfc_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `lfr_id` bigint unsigned NOT NULL,
    `draw_no` int unsigned NOT NULL,
    `num1` tinyint unsigned NOT NULL,
    `num2` tinyint unsigned NOT NULL,
    `num3` tinyint unsigned NOT NULL,
    `num4` tinyint unsigned NOT NULL,
    `num5` tinyint unsigned NOT NULL,
    `num6` tinyint unsigned NOT NULL,
    `score` decimal(12,6) NOT NULL DEFAULT 0,
    `rank_no` int unsigned NOT NULL,
    `sum_value` smallint unsigned NOT NULL DEFAULT 0,
    `ac_value` tinyint unsigned NOT NULL DEFAULT 0,
    `odd_count` tinyint unsigned NOT NULL DEFAULT 0,
    `low_count` tinyint unsigned NOT NULL DEFAULT 0,
    `carry_count` tinyint unsigned NOT NULL DEFAULT 0,
    `neighbor_count` tinyint unsigned NOT NULL DEFAULT 0,
    `prime_count` tinyint unsigned NOT NULL DEFAULT 0,
    `multiple3_count` tinyint unsigned NOT NULL DEFAULT 0,
    `max_consecutive` tinyint unsigned NOT NULL DEFAULT 0,
    `empty_zone_count` tinyint unsigned NOT NULL DEFAULT 0,
    `analysis_data` json DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`lfc_id`),
    UNIQUE KEY `uq_candidate_numbers` (
        `draw_no`,
        `num1`,
        `num2`,
        `num3`,
        `num4`,
        `num5`,
        `num6`
    ),
    UNIQUE KEY `uq_candidate_rank` (`draw_no`, `rank_no`),
    KEY `idx_candidate_run` (`lfr_id`),
    KEY `idx_candidate_score` (`draw_no`, `score`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_distribution_cursor` (
    `ldc_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `lfr_id` bigint unsigned NOT NULL,
    `draw_no` int unsigned NOT NULL,
    `last_rank_no` int unsigned NOT NULL DEFAULT 0,
    `cycle_no` int unsigned NOT NULL DEFAULT 1,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ldc_id`),
    UNIQUE KEY `uq_distribution_cursor_draw` (`draw_no`),
    KEY `idx_distribution_cursor_run` (`lfr_id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_member_distribution_order` (
    `lmdo_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL,
    `member_type` varchar(50) NOT NULL,
    `sort_order` int unsigned NOT NULL,
    `order_mode` varchar(20) NOT NULL DEFAULT 'signup',
    `updated_by` varchar(20) NOT NULL DEFAULT '',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lmdo_id`),
    UNIQUE KEY `uq_member_distribution_order` (`mb_id`),
    KEY `idx_member_type_order` (`member_type`, `sort_order`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_member_combination` (
    `lmc_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `draw_no` int unsigned NOT NULL,
    `mb_id` varchar(20) NOT NULL,
    `member_type` varchar(50) NOT NULL DEFAULT '',
    `lfc_id` bigint unsigned DEFAULT NULL,
    `candidate_rank` int unsigned NOT NULL DEFAULT 0,
    `candidate_cycle` int unsigned NOT NULL DEFAULT 1,
    `num1` tinyint unsigned NOT NULL,
    `num2` tinyint unsigned NOT NULL,
    `num3` tinyint unsigned NOT NULL,
    `num4` tinyint unsigned NOT NULL,
    `num5` tinyint unsigned NOT NULL,
    `num6` tinyint unsigned NOT NULL,
    `distribution_type` varchar(20) NOT NULL DEFAULT 'regular',
    `distribution_day` tinyint unsigned DEFAULT NULL,
    `distribution_batch` varchar(64) NOT NULL,
    `distribution_seq` int unsigned NOT NULL,
    `score` decimal(12,6) NOT NULL DEFAULT 0,
    `sms_required` tinyint(1) NOT NULL DEFAULT 0,
    `sms_status` varchar(20) NOT NULL DEFAULT 'not_required',
    `sms_result_code` varchar(50) DEFAULT NULL,
    `sms_sent_at` datetime DEFAULT NULL,
    `sms_error` text,
    `match_count` tinyint unsigned DEFAULT NULL,
    `bonus_match` tinyint(1) DEFAULT NULL,
    `result_rank` tinyint unsigned DEFAULT NULL,
    `result_checked_at` datetime DEFAULT NULL,
    `created_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`lmc_id`),
    UNIQUE KEY `uq_member_distribution` (
        `draw_no`,
        `mb_id`,
        `distribution_batch`,
        `distribution_seq`
    ),
    KEY `idx_member_draw` (`mb_id`, `draw_no`),
    KEY `idx_draw_type` (`draw_no`, `member_type`),
    KEY `idx_candidate` (`lfc_id`),
    KEY `idx_regular_sms` (
        `draw_no`,
        `distribution_day`,
        `sms_required`,
        `sms_status`
    ),
    KEY `idx_draw_result` (`draw_no`, `result_rank`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_member_draw` (
    `lmd_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `draw_no` int unsigned NOT NULL,
    `mb_id` varchar(20) NOT NULL,
    `member_type` varchar(50) NOT NULL DEFAULT '',
    `combination_count` int unsigned NOT NULL DEFAULT 0,
    `rank1_count` int unsigned NOT NULL DEFAULT 0,
    `rank2_count` int unsigned NOT NULL DEFAULT 0,
    `rank3_count` int unsigned NOT NULL DEFAULT 0,
    `rank4_count` int unsigned NOT NULL DEFAULT 0,
    `rank5_count` int unsigned NOT NULL DEFAULT 0,
    `best_rank` tinyint unsigned DEFAULT NULL,
    `result_checked_at` datetime DEFAULT NULL,
    `winner_sms_required` tinyint(1) NOT NULL DEFAULT 0,
    `winner_sms_status` varchar(20) NOT NULL DEFAULT 'not_required',
    `winner_sms_result_code` varchar(50) DEFAULT NULL,
    `winner_sms_sent_at` datetime DEFAULT NULL,
    `winner_sms_error` text,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lmd_id`),
    UNIQUE KEY `uq_member_draw` (`draw_no`, `mb_id`),
    KEY `idx_draw_best_rank` (`draw_no`, `best_rank`),
    KEY `idx_winner_sms` (
        `draw_no`,
        `winner_sms_required`,
        `winner_sms_status`
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `l_result_job` (
    `lrj_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `draw_no` int unsigned NOT NULL,
    `status` varchar(30) NOT NULL DEFAULT 'waiting',
    `attempt_21_at` datetime DEFAULT NULL,
    `attempt_21_status` varchar(20) DEFAULT NULL,
    `attempt_22_at` datetime DEFAULT NULL,
    `attempt_22_status` varchar(20) DEFAULT NULL,
    `attempt_09_at` datetime DEFAULT NULL,
    `attempt_09_status` varchar(20) DEFAULT NULL,
    `result_saved_at` datetime DEFAULT NULL,
    `result_checked_at` datetime DEFAULT NULL,
    `winner_sms_completed_at` datetime DEFAULT NULL,
    `last_error` text,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lrj_id`),
    UNIQUE KEY `uq_result_job_draw` (`draw_no`),
    KEY `idx_result_job_status` (`status`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
