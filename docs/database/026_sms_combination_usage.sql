ALTER TABLE l_sms_history
    ADD COLUMN usage_group_id varchar(40) NOT NULL DEFAULT ''
        AFTER oshot_group_id,
    ADD COLUMN draw_no int unsigned DEFAULT NULL
        AFTER usage_group_id,
    ADD COLUMN combination_count int unsigned NOT NULL DEFAULT 0
        AFTER draw_no,
    ADD COLUMN usage_applied_at datetime DEFAULT NULL
        AFTER combination_count;

ALTER TABLE l_sms_history
    ADD KEY usage_group_id (usage_group_id),
    ADD KEY combination_usage (
        send_category,
        send_status,
        usage_applied_at
    );
