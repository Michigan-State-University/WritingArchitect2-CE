(function (root, factory) {
  const calculator = factory();

  if (typeof module === "object" && module.exports) {
    module.exports = calculator;
  }

  root.CIWSScoreCalculator = calculator;
})(typeof globalThis !== "undefined" ? globalThis : this, function () {
  "use strict";

  function requireCount(value, name) {
    if (!Number.isSafeInteger(value) || value < 0) {
      throw new TypeError(`${name} must be a non-negative integer.`);
    }

    return value;
  }

  function calculate(counts) {
    if (!counts || typeof counts !== "object") {
      throw new TypeError("Score counts are required.");
    }

    const correct = requireCount(counts.correct, "correct");
    const inaccurate = requireCount(counts.inaccurate, "inaccurate");
    const overlap = requireCount(counts.overlap, "overlap");
    const nmae = requireCount(counts.nmae, "nmae");
    const word = requireCount(counts.word, "word");

    const sentenceError = inaccurate + overlap;
    // Total symbols - word symbols - teal/NMAE symbols.
    const sentenceDenominator = correct + inaccurate + overlap;
    const sentenceAccuracy = sentenceDenominator === 0
      ? 0
      : Number((1 - sentenceError / sentenceDenominator).toFixed(3));
    const ciws = correct + nmae - (inaccurate + overlap + word);

    return {
      sentenceError,
      sentenceAccuracy,
      sentenceDenominator,
      ciws,
    };
  }

  return { calculate };
});
