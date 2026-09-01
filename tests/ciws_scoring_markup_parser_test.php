<?php

require_once __DIR__ . '/../includes/CIWSScoringMarkupParser.php';

$markup = implode('', [
    '<button data-score="" class="btn-space">^</button>',
    '<button data-score="" class="btn-space">^</button>',
    '<button data-score="inacc_incorrect" class="btn-word">ⓈwordⓈ</button>',
    '<button data-score="inacc_seq" class="btn-space">Ⓢ</button>',
    '<button data-score="overlap_seq" class="btn-space">Ⓢ</button>',
    '<button data-score="nmae_trans" class="btn-word">ⓈwordⓈ</button>',
    '<button data-score="spell_error" class="btn-word">ⓌwordⓌ</button>',
]);

$expected = [
    'correct' => 2,
    'word' => 2,
    'inaccurate' => 3,
    'overlap' => 1,
    'nmae' => 2,
];

foreach ([$markup, addslashes($markup)] as $candidate) {
    $actual = CIWSScoringMarkupParser::parse($candidate);
    if ($actual !== $expected) {
        throw new RuntimeException(
            'Markup parser failed: ' . json_encode(['expected' => $expected, 'actual' => $actual])
        );
    }
}

$legacyMarkup = implode('', [
    '<button data-score="punc_missing">beforeⓈⓈ</button>',
    '<button data-score="sem_fit">ⓈwordⓈ</button>',
    '<button data-score="syn_agree">ⓈwordⓈ</button>',
    '<button data-score="">^</button>',
]);
$legacyExpected = [
    'correct' => 1,
    'word' => 0,
    'inaccurate' => 2,
    'overlap' => 2,
    'nmae' => 2,
];
if (CIWSScoringMarkupParser::parse($legacyMarkup) !== $legacyExpected) {
    throw new RuntimeException('Legacy sentence colors were not mapped to red, yellow, and teal.');
}

try {
    CIWSScoringMarkupParser::parse('<button data-score="future_type">Ⓢ</button>');
    throw new RuntimeException('Unknown sentence category did not throw.');
} catch (UnexpectedValueException $exception) {
    // Expected.
}

try {
    CIWSScoringMarkupParser::parse('');
    throw new RuntimeException('Empty markup did not throw.');
} catch (InvalidArgumentException $exception) {
    // Expected.
}

echo "CIWS historical-markup parser: passed.\n";
