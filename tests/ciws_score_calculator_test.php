<?php

require_once __DIR__ . '/../includes/CIWSScoreCalculator.php';

$fixtures = json_decode(
    file_get_contents(__DIR__ . '/ciws_score_fixtures.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

foreach ($fixtures as $fixture) {
    $counts = $fixture['counts'];
    $actual = CIWSScoreCalculator::calculate(
        $counts['correct'],
        $counts['inaccurate'],
        $counts['overlap'],
        $counts['nmae'],
        $counts['word']
    );
    $expected = [
        'sentence_error' => $fixture['expected']['sentenceError'],
        'sentence_accuracy' => (float) $fixture['expected']['sentenceAccuracy'],
        'sentence_denominator' => $fixture['expected']['sentenceDenominator'],
        'ciws' => $fixture['expected']['ciws'],
    ];

    if ($actual !== $expected) {
        throw new RuntimeException(
            $fixture['name'] . ' failed: ' . json_encode(['expected' => $expected, 'actual' => $actual])
        );
    }
}

try {
    CIWSScoreCalculator::calculate(-1, 0, 0, 0, 0);
    throw new RuntimeException('Negative-count validation did not throw.');
} catch (InvalidArgumentException $exception) {
    // Expected.
}

echo 'CIWS PHP calculator: ' . count($fixtures) . " fixtures passed.\n";
