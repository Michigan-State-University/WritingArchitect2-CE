<?php

final class CIWSScoringMarkupParser
{
    /**
     * Reconstruct the five source counts from stored Q_SCORING button markup.
     *
     * @return array{correct:int,word:int,inaccurate:int,overlap:int,nmae:int}
     */
    public static function parse(string $markup): array
    {
        if (trim($markup) === '') {
            throw new InvalidArgumentException('Q_SCORING markup is empty.');
        }

        $candidates = [$markup];
        $withoutSlashes = stripslashes($markup);
        if ($withoutSlashes !== $markup) {
            $candidates[] = $withoutSlashes;
        }

        $lastError = null;
        foreach ($candidates as $candidate) {
            try {
                return self::parseCandidate($candidate);
            } catch (UnexpectedValueException $exception) {
                $lastError = $exception;
            }
        }

        throw $lastError ?? new UnexpectedValueException('Q_SCORING markup could not be parsed.');
    }

    /**
     * @return array{correct:int,word:int,inaccurate:int,overlap:int,nmae:int}
     */
    private static function parseCandidate(string $markup): array
    {
        if (!class_exists('DOMDocument')) {
            throw new RuntimeException('The PHP DOM extension is required to parse Q_SCORING.');
        }

        $previousErrors = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="ciws-scoring-root">' . $markup . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        $parseErrors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (!$loaded) {
            throw new UnexpectedValueException('Q_SCORING markup is not valid HTML.');
        }

        $xpath = new DOMXPath($document);
        $buttons = $xpath->query('//div[@id="ciws-scoring-root"]//button');
        if ($buttons === false || $buttons->length === 0) {
            $detail = count($parseErrors) > 0 ? ' HTML parser errors were reported.' : '';
            throw new UnexpectedValueException('Q_SCORING markup contains no scoring buttons.' . $detail);
        }

        $counts = [
            'correct' => 0,
            'word' => 0,
            'inaccurate' => 0,
            'overlap' => 0,
            'nmae' => 0,
        ];

        foreach ($buttons as $button) {
            $text = $button->textContent;
            $scoreType = $button->getAttribute('data-score');
            $sentenceSymbols = substr_count($text, 'Ⓢ');
            $wordSymbols = substr_count($text, 'Ⓦ');

            // Before the category rename, punc_* rendered red, sem_* yellow,
            // and syn_* teal. Preserve that historical color meaning for
            // quizzes that predate the token counter columns.
            if (str_starts_with($scoreType, 'inacc_') || str_starts_with($scoreType, 'punc_')) {
                $counts['inaccurate'] += $sentenceSymbols;
            } elseif (str_starts_with($scoreType, 'overlap_') || str_starts_with($scoreType, 'sem_')) {
                $counts['overlap'] += $sentenceSymbols;
            } elseif (str_starts_with($scoreType, 'nmae_') || str_starts_with($scoreType, 'syn_')) {
                $counts['nmae'] += $sentenceSymbols;
            } elseif ($sentenceSymbols > 0) {
                throw new UnexpectedValueException(
                    'A sentence symbol has an unknown data-score category: ' . $scoreType
                );
            }

            if ($wordSymbols > 0) {
                $counts['word'] += $wordSymbols;
            } elseif ($sentenceSymbols === 0 && $text === '^') {
                $counts['correct']++;
            }
        }

        return $counts;
    }
}
