<?php

final class CIWSScoreCalculator
{
    /**
     * @return array{sentence_error:int,sentence_accuracy:float,sentence_denominator:int,ciws:int}
     */
    public static function calculate(
        int $correct,
        int $inaccurate,
        int $overlap,
        int $nmae,
        int $word
    ): array {
        $counts = [
            'correct' => $correct,
            'inaccurate' => $inaccurate,
            'overlap' => $overlap,
            'nmae' => $nmae,
            'word' => $word,
        ];

        foreach ($counts as $name => $count) {
            if ($count < 0) {
                throw new InvalidArgumentException($name . ' must be a non-negative integer.');
            }
        }

        $sentenceError = $inaccurate + $overlap;
        // Total symbols - word symbols - teal/NMAE symbols.
        $sentenceDenominator = $correct + $inaccurate + $overlap;
        $sentenceAccuracy = $sentenceDenominator === 0
            ? 0.000
            : round(1 - ($sentenceError / $sentenceDenominator), 3);
        $ciws = $correct + $nmae - ($inaccurate + $overlap + $word);

        return [
            'sentence_error' => $sentenceError,
            'sentence_accuracy' => $sentenceAccuracy,
            'sentence_denominator' => $sentenceDenominator,
            'ciws' => $ciws,
        ];
    }
}
