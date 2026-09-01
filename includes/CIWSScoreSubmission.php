<?php

require_once __DIR__ . '/CIWSScoreCalculator.php';

final class CIWSScoreSubmission
{
    /**
     * Validate source counts and calculate authoritative derived scores.
     * Browser-posted derived values are intentionally ignored.
     *
     * @param array<string,mixed> $input
     * @return array{counts:array{correct:int,word:int,inaccurate:int,overlap:int,nmae:int},scores:array{sentence_error:int,sentence_accuracy:float,sentence_denominator:int,ciws:int}}
     */
    public static function fromInput(array $input): array
    {
        $counts = [
            'correct' => self::requireCount($input, 'Q_TOKEN_CORRECT'),
            'word' => self::requireCount($input, 'Q_TOKEN_WORD'),
            'inaccurate' => self::requireCount($input, 'Q_TOKEN_SEN_INACC'),
            'overlap' => self::requireCount($input, 'Q_TOKEN_SEN_OVERLAP'),
            'nmae' => self::requireCount($input, 'Q_TOKEN_SEN_NMAE'),
        ];

        return [
            'counts' => $counts,
            'scores' => CIWSScoreCalculator::calculate(
                $counts['correct'],
                $counts['inaccurate'],
                $counts['overlap'],
                $counts['nmae'],
                $counts['word']
            ),
        ];
    }

    /** @param array<string,mixed> $input */
    private static function requireCount(array $input, string $name): int
    {
        if (!array_key_exists($name, $input) || is_array($input[$name])) {
            throw new InvalidArgumentException($name . ' is required.');
        }

        $value = filter_var(
            $input[$name],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        if ($value === false) {
            throw new InvalidArgumentException($name . ' must be a non-negative integer.');
        }

        return $value;
    }
}
