-- Add payment approval request note
-- Used by bank/card approval requests and administrator copy text.

ALTER TABLE `l_payment_request`
    ADD COLUMN `request_note` TEXT NULL
    AFTER `installment_months`;
