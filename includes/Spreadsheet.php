<?php

function spreadsheet_safe_value($value): string
{
    $value = (string) $value;

    return preg_match('/^[=+\-@]/', $value) === 1 ? "'" . $value : $value;
}
function spreadsheet_safe_row(array $row): array
{
    return array_map('spreadsheet_safe_value', $row);
}
