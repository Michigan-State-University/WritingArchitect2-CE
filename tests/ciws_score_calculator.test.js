"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const calculator = require("../includes/ciws_score_calculator.js");

const fixtures = JSON.parse(
  fs.readFileSync(path.join(__dirname, "ciws_score_fixtures.json"), "utf8"),
);

for (const fixture of fixtures) {
  assert.deepStrictEqual(
    calculator.calculate(fixture.counts),
    fixture.expected,
    fixture.name,
  );
}

for (const invalid of [-1, 1.5, Number.NaN, Number.POSITIVE_INFINITY, "abc", null]) {
  assert.throws(
    () => calculator.calculate({
      correct: invalid,
      inaccurate: 0,
      overlap: 0,
      nmae: 0,
      word: 0,
    }),
    /correct must be a non-negative integer/,
  );
}

assert.throws(() => calculator.calculate(), /Score counts are required/);

console.log(`CIWS JavaScript calculator: ${fixtures.length} fixtures passed.`);
