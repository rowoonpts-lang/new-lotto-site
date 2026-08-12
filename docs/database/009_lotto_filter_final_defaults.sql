-- Lotto filter final default settings
--
-- Final specification:
-- sum range default = 100 ~ 190
--
-- Existing administrator-customized values must be preserved.

UPDATE l_filter_setting
SET
    setting_value = '190',
    updated_at = CURRENT_TIMESTAMP
WHERE setting_key = 'sum_max'
  AND setting_value = '180'
  AND updated_by = '';
