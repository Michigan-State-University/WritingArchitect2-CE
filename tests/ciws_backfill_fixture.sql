DROP TABLE IF EXISTS quiz_ciws_formula_backup;
DROP TABLE IF EXISTS quiz;

CREATE TABLE quiz (
  Q_ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Q_WORD_ERROR int(11) DEFAULT NULL,
  Q_SENTENCE_ERROR int(11) DEFAULT NULL,
  Q_CIWS int(11) DEFAULT NULL,
  Q_SENTENCE_ACCURACY decimal(10,3) DEFAULT NULL,
  Q_SCORING mediumtext DEFAULT NULL,
  Q_TOKEN_CORRECT int(11) UNSIGNED DEFAULT NULL,
  Q_TOKEN_WORD int(11) UNSIGNED DEFAULT NULL,
  Q_TOKEN_SEN_INACC int(11) UNSIGNED DEFAULT NULL,
  Q_TOKEN_SEN_OVERLAP int(11) UNSIGNED DEFAULT NULL,
  Q_TOKEN_SEN_NMAE int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Complete token row with all three old derived values wrong.
INSERT INTO quiz VALUES (
  1, 4, 6, 3, 0.800,
  CONCAT(
    REPEAT('<button data-score="">^</button>', 10),
    '<button data-score="inacc_incorrect">ⓈwordⓈ</button>',
    '<button data-score="inacc_seq">Ⓢ</button>',
    '<button data-score="overlap_seq">Ⓢ</button>',
    '<button data-score="nmae_trans">ⓈwordⓈ</button>',
    '<button data-score="spell_error">ⓌwordⓌ</button>',
    '<button data-score="spell_error">ⓌwordⓌ</button>'
  ),
  10, 4, 3, 1, 2
);

-- Pre-token historical row recoverable only from Q_SCORING.
INSERT INTO quiz VALUES (
  2, 2, 6, -3, 0.200,
  CONCAT(
    REPEAT('<button data-score="">^</button>', 2),
    '<button data-score="inacc_incorrect">ⓈwordⓈ</button>',
    '<button data-score="inacc_seq">Ⓢ</button>',
    '<button data-score="overlap_seq">Ⓢ</button>',
    '<button data-score="nmae_trans">ⓈwordⓈ</button>',
    '<button data-score="spell_error">ⓌwordⓌ</button>'
  ),
  NULL, NULL, NULL, NULL, NULL
);

-- Already-correct row should not be updated or backed up.
INSERT INTO quiz VALUES (
  3, 1, 0, 9, 1.000, NULL,
  8, 1, 0, 0, 2
);

-- NULL derived fields remain NULL; only the populated CIWS field changes.
INSERT INTO quiz VALUES (
  4, 0, NULL, 0, NULL, NULL,
  2, 0, 1, 0, 0
);

-- Zero sentence denominator: teal contributes to CIWS, accuracy is 0.000.
INSERT INTO quiz VALUES (
  5, 0, 1, 0, 1.000, NULL,
  0, 0, 0, 0, 3
);
