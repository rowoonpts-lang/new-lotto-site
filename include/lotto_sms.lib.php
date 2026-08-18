<?php

function lottoSmsBuildCombinationMessage($drawNo, $title, $rows)
{
    $message = (int) $drawNo . '회 ' . trim((string) $title) . "\n";
    $total = count($rows);

    foreach ($rows as $index => $row) {
        $message .= ($index + 1) . '. ';
        $message .= (int) $row['num1'] . ',';
        $message .= (int) $row['num2'] . ',';
        $message .= (int) $row['num3'] . ',';
        $message .= (int) $row['num4'] . ',';
        $message .= (int) $row['num5'] . ',';
        $message .= (int) $row['num6'];

        if ($index < $total - 1) {
            $message .= "\n";
        }
    }

    return $message;
}
