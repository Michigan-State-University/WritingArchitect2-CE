<?php

require_once __DIR__ . '/../includes/CIWSScoreSubmission.php';

$input = [
    'Q_TOKEN_CORRECT' => '10',
    'Q_TOKEN_WORD' => '4',
    'Q_TOKEN_SEN_INACC' => '3',
    'Q_TOKEN_SEN_OVERLAP' => '1',
    'Q_TOKEN_SEN_NMAE' => '2',
    // Deliberately stale/tampered browser-derived values.
    'Q_SENTENCE_ERROR' => '999',
    'Q_SENTENCE_ACCURACY' => '0.001',
    'Q_CIWS' => '999',
];

$submission = CIWSScoreSubmission::fromInput($input);
$expectedScores = [
    'sentence_error' => 4,
    'sentence_accuracy' => 0.714,
    'sentence_denominator' => 14,
    'ciws' => 4,
];
if ($submission['scores'] !== $expectedScores) {
    throw new RuntimeException('Server-authoritative calculation failed.');
}

foreach ([
    ['Q_TOKEN_CORRECT' => '-1'],
    ['Q_TOKEN_CORRECT' => '1.5'],
    ['Q_TOKEN_CORRECT' => ['1']],
] as $override) {
    try {
        CIWSScoreSubmission::fromInput(array_merge($input, $override));
        throw new RuntimeException('Invalid source count did not throw.');
    } catch (InvalidArgumentException $exception) {
        // Expected.
    }
}

$missing = $input;
unset($missing['Q_TOKEN_SEN_NMAE']);
try {
    CIWSScoreSubmission::fromInput($missing);
    throw new RuntimeException('Missing source count did not throw.');
} catch (InvalidArgumentException $exception) {
    // Expected.
}

echo "CIWS server-authoritative submission: passed.\n";
