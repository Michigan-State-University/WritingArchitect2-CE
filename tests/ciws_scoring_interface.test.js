"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

function scoreButton(html, scoreType = "") {
  return {
    innerHTML: html,
    getAttribute(name) {
      return name === "data-score" ? scoreType : null;
    },
  };
}

const children = [
  ...Array.from({ length: 10 }, () => scoreButton("^")),
  scoreButton("ⓈwordⓈ", "inacc_incorrect"),
  scoreButton("Ⓢ", "inacc_seq"),
  scoreButton("Ⓢ", "overlap_seq"),
  scoreButton("ⓈwordⓈ", "nmae_trans"),
  scoreButton("ⓌwordⓌ", "spell_error"),
  scoreButton("ⓌwordⓌ", "spell_error"),
];

const elements = {
  "essay-raw-in": { value: "One sentence." },
  "essay-scoring": { children },
  "essay-words": { innerHTML: "10" },
  "sc-c": { innerHTML: "" },
  "sc-ciws": { innerHTML: "" },
  "sc-we": { innerHTML: "" },
  "sc-se": { innerHTML: "" },
  "sc-s-inacc": { innerHTML: "" },
  "sc-s-overlap": { innerHTML: "" },
  "sc-s-nmae": { innerHTML: "" },
  "sc-w-acc": { innerHTML: "" },
  "sc-s-acc": { innerHTML: "" },
  "essay-sent-complex": { innerHTML: "" },
};

const context = vm.createContext({
  console,
  URLSearchParams,
  setTimeout,
  window: { addEventListener() {} },
  document: {
    getElementById(id) {
      if (!(id in elements)) {
        throw new Error(`Unexpected element requested: ${id}`);
      }
      return elements[id];
    },
  },
});

for (const sourceFile of [
  "../includes/ciws_score_calculator.js",
  "../includes/scripts.js",
]) {
  vm.runInContext(
    fs.readFileSync(path.join(__dirname, sourceFile), "utf8"),
    context,
    { filename: sourceFile },
  );
}

vm.runInContext("countScores()", context);

assert.equal(elements["sc-c"].innerHTML, 10);
assert.equal(elements["sc-we"].innerHTML, 4);
assert.equal(elements["sc-s-inacc"].innerHTML, 3);
assert.equal(elements["sc-s-overlap"].innerHTML, 1);
assert.equal(elements["sc-s-nmae"].innerHTML, 2);
assert.equal(elements["sc-se"].innerHTML, 4);
assert.equal(elements["sc-s-acc"].innerHTML, "0.714");
assert.equal(elements["sc-ciws"].innerHTML, 4);

console.log("CIWS scoring interface wiring: passed.");
