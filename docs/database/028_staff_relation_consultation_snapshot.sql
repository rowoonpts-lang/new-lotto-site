-- Staff relation and consultation assignment snapshot
--
-- Purpose:
-- 1. Allow one staff member to belong to more than one upper staff member.
-- 2. Store the assigned staff snapshot on consultation history.
--
-- The production database already contains this final structure.
-- This migration records the schema change in the repository and is
-- written so that it can also be applied to databases that do not yet
-- contain the changes.

CREATE TABLE IF NOT EXISTS `l_staff_relation` (
    `lsr_id` int(11) NOT NULL AUTO_INCREMENT,
    `parent_mb_id` varchar(20) NOT NULL DEFAULT '',
    `child_mb_id` varchar(20) NOT NULL DEFAULT '',
    `created_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`lsr_id`),
    UNIQUE KEY `uniq_parent_child` (`parent_mb_id`, `child_mb_id`),
    KEY `parent_mb_id` (`parent_mb_id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb3
COLLATE=utf8mb3_general_ci;


-- Remove a previous UNIQUE index that allows only one parent per child.
SELECT GROUP_CONCAT(
           CONCAT('DROP INDEX `', `index_name`, '`')
           SEPARATOR ', '
       )
INTO @staff_relation_drop_indexes
FROM (
    SELECT `index_name`
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'l_staff_relation'
      AND non_unique = 0
      AND index_name <> 'PRIMARY'
    GROUP BY `index_name`
    HAVING GROUP_CONCAT(
               `column_name`
               ORDER BY `seq_in_index`
               SEPARATOR ','
           ) = 'child_mb_id'
) AS old_unique_indexes;

SET @staff_relation_drop_sql =
    IF(
        @staff_relation_drop_indexes IS NULL,
        'SELECT 1',
        CONCAT(
            'ALTER TABLE `l_staff_relation` ',
            @staff_relation_drop_indexes
        )
    );

PREPARE staff_relation_drop_stmt
FROM @staff_relation_drop_sql;

EXECUTE staff_relation_drop_stmt;

DEALLOCATE PREPARE staff_relation_drop_stmt;


-- Add the parent/child composite UNIQUE index when it does not exist.
SELECT COUNT(*)
INTO @staff_relation_pair_unique_count
FROM (
    SELECT `index_name`
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'l_staff_relation'
      AND non_unique = 0
      AND index_name <> 'PRIMARY'
    GROUP BY `index_name`
    HAVING GROUP_CONCAT(
               `column_name`
               ORDER BY `seq_in_index`
               SEPARATOR ','
           ) = 'parent_mb_id,child_mb_id'
) AS pair_unique_indexes;

SET @staff_relation_pair_sql =
    IF(
        @staff_relation_pair_unique_count > 0,
        'SELECT 1',
        'ALTER TABLE `l_staff_relation`
         ADD UNIQUE KEY `uniq_parent_child`
         (`parent_mb_id`, `child_mb_id`)'
    );

PREPARE staff_relation_pair_stmt
FROM @staff_relation_pair_sql;

EXECUTE staff_relation_pair_stmt;

DEALLOCATE PREPARE staff_relation_pair_stmt;


-- Add consultation assignment snapshot column when it does not exist.
SELECT COUNT(*)
INTO @memo_staff_column_count
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'l_memo'
  AND column_name = 'staff_mb_id';

SET @memo_staff_column_sql =
    IF(
        @memo_staff_column_count > 0,
        'SELECT 1',
        'ALTER TABLE `l_memo`
         ADD COLUMN `staff_mb_id` varchar(20) DEFAULT NULL
         AFTER `from_mb_id`'
    );

PREPARE memo_staff_column_stmt
FROM @memo_staff_column_sql;

EXECUTE memo_staff_column_stmt;

DEALLOCATE PREPARE memo_staff_column_stmt;
