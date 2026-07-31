-- Lotto Platform member extensions
-- Target database: local development database
-- Purpose: restore custom member columns and g5_member_etc table

ALTER TABLE `g5_member`
    ADD COLUMN `mb_code` varchar(20) NOT NULL DEFAULT '',
    ADD COLUMN `mb_type` varchar(50) NOT NULL DEFAULT '',
    ADD COLUMN `mb_team` varchar(50) NOT NULL DEFAULT '',
    ADD COLUMN `emp_pw` varchar(255) NOT NULL DEFAULT '',
    ADD COLUMN `free_sms_turn` varchar(50) NOT NULL DEFAULT '',
    ADD COLUMN `free_change` int NOT NULL DEFAULT 0,
    ADD COLUMN `free_pre_type` varchar(50) NOT NULL DEFAULT '';

CREATE TABLE `g5_member_etc` (
    `mb_id` varchar(20) NOT NULL,
    `mb_hp_etc` varchar(255) NOT NULL DEFAULT '',
    `mb_db` varchar(100) NOT NULL DEFAULT '',
    `mb_in` varchar(50) NOT NULL DEFAULT '',
    `mb_yak` varchar(100) NOT NULL DEFAULT '',

    `num_mon` int NOT NULL DEFAULT 0,
    `num_tue` int NOT NULL DEFAULT 0,
    `num_wed` int NOT NULL DEFAULT 0,
    `num_thur` int NOT NULL DEFAULT 0,
    `num_fri` int NOT NULL DEFAULT 0,
    `num_sat` int NOT NULL DEFAULT 0,
    `use_num` int NOT NULL DEFAULT 0,

    `start_date` varchar(50) DEFAULT NULL,
    `end_date` varchar(50) DEFAULT NULL,
    `stop_start_date` varchar(50) DEFAULT NULL,
    `left_day` int NOT NULL DEFAULT 0,
    `hold_datetime` datetime DEFAULT NULL,

    `recent_turn` int NOT NULL DEFAULT 0,
    `recent_auto_date` varchar(50) DEFAULT NULL,
    `recent_auto_datetime` datetime DEFAULT NULL,
    `recent_free_date` varchar(50) DEFAULT NULL,
    `recent_free_datetime` datetime DEFAULT NULL,

    `free_num_qty` int NOT NULL DEFAULT 10,
    `free_num_date` varchar(50) NOT NULL DEFAULT '',

    `recent_select` varchar(100) NOT NULL DEFAULT '',
    `recent_memo` text,
    `recent_misu` varchar(50) NOT NULL DEFAULT '',

    `set3grade` int NOT NULL DEFAULT 0,
    `set4grade` int NOT NULL DEFAULT 0,

    `lucky1` int NOT NULL DEFAULT 0,
    `lucky2` int NOT NULL DEFAULT 0,
    `lucky3` int NOT NULL DEFAULT 0,
    `lucky4` int NOT NULL DEFAULT 0,
    `lucky5` int NOT NULL DEFAULT 0,

    PRIMARY KEY (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
