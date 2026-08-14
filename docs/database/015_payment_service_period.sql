-- Lotto Platform payment approval service period
-- Purpose:
-- Keep the exact service start/end dates selected by an administrator at approval time.

ALTER TABLE `l_payment_request`
    ADD COLUMN `service_start_date` date DEFAULT NULL AFTER `installment_months`,
    ADD COLUMN `service_end_date` date DEFAULT NULL AFTER `service_start_date`;
