// ***************************************************************************
// SAVE AND UNDO
// ***************************************************************************

let undoLog = []; // array of objects for undo scoring
let isEditing = false; // set to true on first scoring, false after saving

window.addEventListener("beforeunload", function (e) {
  if (isEditing) {
    // Cancel the event
    e.preventDefault(); // If you prevent default behavior in Mozilla Firefox prompt will always be shown
    // Chrome requires returnValue to be set
    e.returnValue = "";
  }
});

function undoStack() {
  if (undoLog.length >= 1 && Array.isArray(undoLog)) {
    let lastEdit = undoLog.pop();
    document.getElementById("essay-scoring").innerHTML = lastEdit;
    countScores();
  } else {
    isEditing = false;
    document.getElementById("btn-save").disabled = true;
    // document.getElementById("btn-sign").disabled = true;
  }
}

function resetSelected() {
  const activeBtn = document.getElementsByClassName("btn-active")[0];
  activeBtn.innerHTML = "^";
  activeBtn.setAttribute("data-score", "");
  activeBtn.classList.remove("btn-active");
  document.getElementById("reset-selected").disabled = true;
  countScores();
}

function resetAll() {
  if (confirm("Reset All Scoring?")) {
    parseInput();
    document.getElementById("reset-all").disabled = true;
    countScores();
  }
}

function clearEditStack(quiz_completed) {
  while (undoLog.length) {
    undoLog.pop();
  }
  isEditing = false;
  document.getElementById("btn-save").disabled = true;
  if (quiz_completed) {
    document.getElementById("btn-sign").disabled = true;
  } else {
    document.getElementById("btn-sign").disabled = false;
  }
  document.getElementById("reset-all").disabled = true;
  document.getElementById("undo").disabled = true;
}

function save_scoring(quiz_completed) {
  // get payload
  q_id = document.getElementById("Q_ID").value;

  q_token_correct = document.getElementById("sc-c").innerHTML;
  q_token_word = document.getElementById("sc-we").innerHTML;
  q_token_sen_inacc = document.getElementById("sc-s-inacc").innerHTML;
  q_token_sen_overlap = document.getElementById("sc-s-overlap").innerHTML;
  q_token_sen_nmae = document.getElementById("sc-s-nmae").innerHTML;

  q_word_count = document.getElementById("essay-words").innerHTML;
  q_sentence_count = document.getElementById("essay-sentences").innerHTML;
  q_word_error = document.getElementById("sc-we").innerHTML;
  q_sentence_error = document.getElementById("sc-se").innerHTML;
  q_ciws = document.getElementById("sc-ciws").innerHTML;
  q_word_accuracy = document.getElementById("sc-w-acc").innerHTML;
  q_sentence_accuracy = document.getElementById("sc-s-acc").innerHTML;
  q_word_complexity = document.getElementById("essay-wordsGTE7").innerHTML;
  q_sentence_complexity =
    document.getElementById("essay-sent-complex").innerHTML;
  q_essay_notes = document.getElementById("notes").innerHTML;
  q_scoring = document.getElementById("essay-scoring").innerHTML;
  q_grader_id = document.getElementById("Q_GRADER_ID").value;
  if (quiz_completed) q_grading_status = "Completed";
  else q_grading_status = "In Progress";

  // Get ?id from query string
  let sessionID = new URLSearchParams(window.location.search).get("id");

  $.ajax({
    url: "../ajax/save_quiz_grade.php?id=" + sessionID,
    type: "POST",
    //  dataType: 'jsonp',
    data: {
      Q_ID: q_id,
      Q_WORD_COUNT: q_word_count,
      Q_SENTENCE_COUNT: q_sentence_count,
      Q_WORD_ERROR: q_word_error,
      Q_SENTENCE_ERROR: q_sentence_error,
      Q_CIWS: q_ciws,
      Q_WORD_ACCURACY: q_word_accuracy,
      Q_SENTENCE_ACCURACY: q_sentence_accuracy,
      Q_WORD_COMPLEXITY: q_word_complexity,
      Q_SENTENCE_COMPLEXITY: q_sentence_complexity,
      Q_ESSAY_NOTES: q_essay_notes,
      Q_SCORING: q_scoring,
      Q_GRADER_ID: q_grader_id,
      Q_GRADING_STATUS: q_grading_status,
      Q_TOKEN_CORRECT: q_token_correct,
      Q_TOKEN_WORD: q_token_word,
      Q_TOKEN_SEN_INACC: q_token_sen_inacc,
      Q_TOKEN_SEN_OVERLAP: q_token_sen_overlap,
      Q_TOKEN_SEN_NMAE: q_token_sen_nmae,
    },
    success: function (data) {
      // update SCORED BY Section
      alert(data);

      $("#CTL_MESSAGE").show();
      setTimeout(function () {
        $("#CTL_MESSAGE").hide();
      }, 3800);
      clearEditStack(quiz_completed);
    },
    error: function (data) {
      alert("error: " + data.responseText);
    },
  });
}

function save_scoring2(quiz_completed) {
  // get payload
  q_id = document.getElementById("Q_ID").value;

  q_token_correct = document.getElementById("sc-c").innerHTML;
  q_token_word = document.getElementById("sc-we").innerHTML;
  q_token_sen_inacc = document.getElementById("sc-s-inacc").innerHTML;
  q_token_sen_overlap = document.getElementById("sc-s-overlap").innerHTML;
  q_token_sen_nmae = document.getElementById("sc-s-nmae").innerHTML;

  q_word_count = document.getElementById("essay-words").innerHTML;
  q_sentence_count = document.getElementById("essay-sentences").innerHTML;
  q_word_error = document.getElementById("sc-we").innerHTML;
  q_sentence_error = document.getElementById("sc-se").innerHTML;
  q_ciws = document.getElementById("sc-ciws").innerHTML;
  q_word_accuracy = document.getElementById("sc-w-acc").innerHTML;
  q_sentence_accuracy = document.getElementById("sc-s-acc").innerHTML;
  q_word_complexity = document.getElementById("essay-wordsGTE7").innerHTML;
  q_sentence_complexity =
    document.getElementById("essay-sent-complex").innerHTML;
  q_essay_notes = document.getElementById("notes").value;
  q_scoring = document.getElementById("essay-scoring").innerHTML;
  q_grader_id = document.getElementById("Q_GRADER_ID").value;
  q_planning = document.getElementById("Q_PLANNING").value;
  if (quiz_completed) q_grading_status = "Completed";
  else q_grading_status = "In Progress";

  // Get ?id from query string
  let sessionID = new URLSearchParams(window.location.search).get("id");

  $.ajax({
    url: "../ajax/save_quiz_grade.php?id=" + sessionID,
    type: "POST",
    //  dataType: 'jsonp',
    data: {
      Q_ID: q_id,
      Q_WORD_COUNT: q_word_count,
      Q_SENTENCE_COUNT: q_sentence_count,
      Q_WORD_ERROR: q_word_error,
      Q_SENTENCE_ERROR: q_sentence_error,
      Q_CIWS: q_ciws,
      Q_WORD_ACCURACY: q_word_accuracy,
      Q_SENTENCE_ACCURACY: q_sentence_accuracy,
      Q_WORD_COMPLEXITY: q_word_complexity,
      Q_SENTENCE_COMPLEXITY: q_sentence_complexity,
      Q_ESSAY_NOTES: q_essay_notes,
      Q_SCORING: q_scoring,
      Q_GRADER_ID: q_grader_id,
      Q_GRADING_STATUS: q_grading_status,
      Q_PLANNING: q_planning,
      Q_TOKEN_CORRECT: q_token_correct,
      Q_TOKEN_WORD: q_token_word,
      Q_TOKEN_SEN_INACC: q_token_sen_inacc,
      Q_TOKEN_SEN_OVERLAP: q_token_sen_overlap,
      Q_TOKEN_SEN_NMAE: q_token_sen_nmae,
    },
    success: function (data) {
      // update SCORED BY Section
      items = data.split("|");
      document.getElementById("scorer").innerHTML = items[0];
      document.getElementById("score-status").innerHTML = items[1];
      document.getElementById("score-timestamp").innerHTML = items[2];

      $("#CTL_MESSAGE").show();
      setTimeout(function () {
        $("#CTL_MESSAGE").hide();
      }, 3800);
      clearEditStack(quiz_completed);
    },
    error: function (data) {
      alert("error: " + data.responseText);
    },
  });
}

function completeScoring() {
  confirm("Save and submit final score?");
}

function loadResponse(scoredata) {
  if (scoredata == "") parseInput();
  else document.getElementById("essay-scoring").innerHTML = scoredata;
}

// ***************************************************************************
// WORD BUTTONS
// ***************************************************************************

function strToObjArr(str) {
  const keepLineReturns = str.replace(/(\r\n|\n|\r)/gm, " ||br|| ");
  const puncSeparate = keepLineReturns.replace(/((?:\.|\?|!|,|;|")+)/g, " $1 ");
  // wrap in spaces for outter writing sequences, split to array
  let strArr = puncSeparate.replace(/(.+)/, " $1 ").split(/(\s+)/);
  // remove empty elements from splitting with spaces at both ends
  strArr.pop();
  strArr.shift();
  let objArr = [];
  strArr.map((str, idx) => {
    let type = str.match(/^\s+$/)
      ? "space"
      : str === "||br||"
        ? "break"
        : "word";
    let text = type === "word" ? str : type === "space" ? "^" : "<br>";
    objArr[idx] = { type, text, score: "" };
    return objArr[idx];
  });
  // console.log(objArr);
  return objArr;
}

function objArrToBtns(objArr) {
  let btnArr = [];
  objArr.map((obj, idx) => {
    const word = objArr[idx].text;
    const type = objArr[idx].type;
    btnArr.push(makeWordBtn(idx, word, type));
    return obj;
  });
  btnArr.pop(); // Remove last item
  return btnArr.join("");
}

function makeWordBtn(id, word, btnType) {
  return btnType === "break"
    ? "<br>"
    : `<button id="${id}" data-score="" class="btn-${btnType}" onclick="selectToScore(this.id)">${word}</button>`;
}

function parseInput() {
  const essayRaw = document.getElementById("essay-raw-in").value.trim();
  const wordArr = strToObjArr(essayRaw);
  const btnArr = objArrToBtns(wordArr);
  document.getElementById("essay-scoring").innerHTML = btnArr;
  undoLog.push(btnArr);
  countInput();
  countScores();
  // console.log(btnArr);
  return btnArr;
}

// ***************************************************************************
// APPLY SCORING
// ***************************************************************************

function selectToScore(id) {
  const btn = document.getElementById(id);
  const elements = document.querySelectorAll(".btn-active");
  elements.forEach((el) => {
    el.classList.remove("btn-active");
  });
  btn.classList.add("btn-active");
  const scTypeBtns = document.querySelectorAll(".btn-sc-type");
  const activeBtnType = btn.classList.contains("btn-space") ? "space" : "word";

  scTypeBtns.forEach((el) => {
    const btnType = scoreConfig[el.id].type;
    el.disabled = btnType == activeBtnType ? false : true;
  });
  if (btn.innerHTML != "^" && activeBtnType == "space") {
    document.getElementById("reset-selected").disabled = false;
  } else {
    document.getElementById("reset-selected").disabled = true;
  }
}

function applyScoring(scoreType) {
  const activeBtn = document.getElementsByClassName("btn-active")[0];

  if (!activeBtn) {
    alert("Select a word or space to score, then click a scoring button.");
  } else {
    let lastEdit = document.getElementById("essay-scoring").innerHTML;
    undoLog.push(lastEdit);
    isEditing = true;
    document.getElementById("reset-all").disabled = false;
    document.getElementById("undo").disabled = false;
    document.getElementById("btn-save").disabled = false;
    // document.getElementById("btn-sign").disabled = false;
    document.querySelector(".btn-active").classList.remove("btn-active");

    const sc = scoreConfig[scoreType];
    const applyTo = sc.apply_to;
    const btnLeft = document.getElementById(Number(activeBtn.id) - 1);
    const btnRight = document.getElementById(Number(activeBtn.id) + 1);
    const wObjLeft = undoLog.slice(-1)[0][Number(activeBtn.id) - 1];
    const wObj = undoLog.slice(-1)[0][Number(activeBtn.id)];
    const wObjRight = undoLog.slice(-1)[0][Number(activeBtn.id) + 1];

    if (["left", "wrap"].includes(applyTo)) {
      btnLeft.innerHTML = sc.symbol;
      btnLeft.setAttribute("data-score", scoreType);
      wObjLeft.format = scoreType;
    }
    if (["right", "wrap"].includes(applyTo)) {
      btnRight.innerHTML = sc.symbol;
      btnRight.setAttribute("data-score", scoreType);
      wObjRight.format = scoreType;
    }
    if (applyTo == "space") {
      activeBtn.innerHTML = sc.symbol;
      activeBtn.setAttribute("data-score", scoreType);
      wObj.format = scoreType;
    }
  }
  countScores();
}

function makeScoreBtns(scoreTypes, forFS) {
  const scoreArr = scoreTypes.split(",");
  const fieldsetHtml = document.getElementById(forFS);
  const allBtns = scoreArr.map((type, idx) => {
    const sc = scoreConfig[type];
    const symbol = `<code class="sc-symbol" data-score="${type}">${sc.symbol.charAt(
      0,
    )}</code>`;
    const label = `<label class="sc-category" data-score="${type}">${sc.category}</label>`;
    const buttonText = sc.button_text.replace(
      /\[text\]/g,
      `<span style="color:black">[text]</span>`,
    );
    const button = `<button onclick="applyScoring(this.name)" id="${type}" name="${type}" title="${sc.scoring_guide}" class="btn btn-sc-type" data-score="${type}">${buttonText}</button>`;
    const info = `<label class="sc-type-info">${sc.button_info}</label>`;
    return (
      symbol +
      label +
      button +
      info +
      (idx + 1 == scoreArr.length ? "" : "<br>")
    );
  });
  fieldsetHtml.innerHTML = fieldsetHtml.innerHTML + allBtns.join("");
}

// ***************************************************************************
// CALCULATE SCORES
// ***************************************************************************

function countInput() {
  const essayRaw = document.getElementById("essay-raw-in").value.trim();
  const wordArr = essayRaw.match(/\w+/g);
  const countW = wordArr.length;
  const countS = essayRaw.split(/[!|.|?]+/).length - 1;
  const countWGTE7 = essayRaw.match(/\w{7,}/g)?.length || 0;
  document.getElementById("essay-sentences").innerHTML = countS;
  document.getElementById("essay-words").innerHTML = countW;
  document.getElementById("essay-wordsGTE7").innerHTML = countWGTE7;
}

function countScores() {
  const essayRaw = document.getElementById("essay-raw-in").value.trim();
  const essayScoring = document.getElementById("essay-scoring");
  // console.log(essayScoring.innerHTML);
  let red_s = 0;
  let yellow_s = 0;
  let teal_s = 0;
  let w_symbols = 0;
  let s_symbols = 0;
  let correct_symbols = 0;

  // Loop through each button in the scoring area
  for (let i = 0; i < essayScoring.children.length; i++) {
    const btn = essayScoring.children[i];
    const scoreType = btn.getAttribute("data-score");
    if (scoreType?.startsWith("inacc_")) { // if scoreType begins with inacc_, increment red_s
      red_s += btn.innerHTML.split("Ⓢ").length - 1;
    } else if (scoreType?.startsWith("overlap_")) { // if scoreType begins with overlap_, increment yellow_s
      yellow_s += btn.innerHTML.split("Ⓢ").length - 1;
    } else if (scoreType?.startsWith("nmae_")) { // if scoreType begins with nmae_, increment teal_s
      teal_s += btn.innerHTML.split("Ⓢ").length - 1;
    }

    // Check if button text contains Ⓦ, increment w_symbols by number of Ⓦ
    if (btn.innerHTML.includes("Ⓦ")) {
      w_symbols += btn.innerHTML.split("Ⓦ").length - 1;
    } else if (btn.innerHTML.includes("Ⓢ")) { // Check if button text contains Ⓢ, increment s_symbols by number of Ⓢ
      s_symbols += btn.innerHTML.split("Ⓢ").length - 1;
    } else if (btn.innerHTML === "^") { // Check if button text is ^, increment correct_symbols
      correct_symbols++;
    }
  }

  const te = s_symbols + w_symbols;
  const ts = te + correct_symbols;
  // const ciws = cws - te; 
  // New CIWS calculation: Total correct symbols (^) - Red S symbols (Ⓢ) - Total W symbols (Ⓦ)
  const ciws = correct_symbols - red_s - w_symbols;
  const wa = (1 - w_symbols / ts).toFixed(3);
  // New Sentence Accuracy calculation: 1 – (red S Symbols + yellow s symbols) / Total Symbols (^, Ⓢ, Ⓦ)
  const sa = (1 - (red_s + yellow_s) / ts).toFixed(3);
  const sentences = essayRaw
    .split(/[.|?|!]+/)
    .filter((sent) => sent.length > 0);
  const sentLenArr = sentences.map((s) => s.trim().split(/\s/).length);
  const sComp = getStandardDeviation(sentLenArr);
  document.getElementById("sc-c").innerHTML = correct_symbols;
  document.getElementById("sc-ciws").innerHTML = ciws;
  document.getElementById("sc-we").innerHTML = w_symbols;
  document.getElementById("sc-se").innerHTML = s_symbols;
  document.getElementById("sc-s-inacc").innerHTML = red_s;
  document.getElementById("sc-s-overlap").innerHTML = yellow_s;
  document.getElementById("sc-s-nmae").innerHTML = teal_s;
  document.getElementById("sc-w-acc").innerHTML = wa;
  document.getElementById("sc-s-acc").innerHTML = sa;
  document.getElementById("essay-sent-complex").innerHTML = sComp.toFixed(3);
  // document.getElementById("sc-per-cor").innerHTML = Math.round((cws / ts) * 1000)/10;
  // document.getElementById("sc-per-incor").innerHTML = Math.round((te / ts) * 1000)/10;
}

function getStandardDeviation(array) {
  const n = array.length;
  const mean = array.reduce((a, b) => a + b) / n;
  return Math.sqrt(
    array.map((x) => Math.pow(x - mean, 2)).reduce((a, b) => a + b) / n,
  );
}
// ***************************************************************************
// Typing Test Scoring Functions
// ***************************************************************************

function loadPassage() {
  // document.getElementById("copy-response").innerHTML = copyPassage;
  document.getElementById("copy-passage").innerHTML = copyPassage;
  scoreTyping();
}

function scoreTyping() {
  var currentResponse = document.getElementById("copy-response").value;
  var responseWords = currentResponse.split(/\s+/g);
  var sourceWords = copyPassage.split(/\s+/g);
  document.getElementById("count-response-word").innerHTML =
    responseWords.length;
  document.getElementById("count-response-char").innerHTML =
    currentResponse.length;
  document.getElementById("count-source-word").innerHTML = sourceWords.length;
  document.getElementById("count-source-char").innerHTML = copyPassage.length;
  console.log("words: " + responseWords.length);
}

// ***************************************************************************
// REF DATA
// ***************************************************************************

const scoreConfig = {
  cap_missing: {
    symbol: "Ⓦ",
    category: "CAPITAL",
    button_text: "Ⓦ[text]",
    button_info: "Missing Capitalization",
    scoring_guide:
      "Mark before the word for (1) a proper name, (2) the beginning of a sentence, (3) the word “I”",
    apply_to: "space",
    type: "space",
  },
  spell_error: {
    symbol: "Ⓦ",
    category: "SPELLING",
    button_text: "Ⓦ[text]Ⓦ",
    button_info: "Misspelled Word",
    scoring_guide:
      "General misspellings. Misspellings also include incorrect homophone, combining two words, and incorrect apostrophe usage/lack thereof.",
    apply_to: "wrap",
    type: "word",
  },
  spell_seq: {
    symbol: "Ⓦ",
    category: "SPELLING",
    button_text: "[text]Ⓦ",
    button_info: "Single Incorrect Sequence",
    scoring_guide:
      "Use this button when there is already another type of error marked at the beginning of the word.",
    apply_to: "space",
    type: "space",
  },
  gram_tense: {
    symbol: "Ⓢ",
    category: "GRAMMAR",
    button_text: "Ⓢ[text]Ⓢ",
    button_info: "Tense Error",
    scoring_guide:
      "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    apply_to: "wrap",
    type: "word",
  },
  inacc_missing: {
    symbol: "ⓈⓈ",
    category: "INACCURATE",
    button_text: "[text]ⓈⓈ",
    button_info: "Missing Punctuation or Word",
    scoring_guide:
      "Missing punctuation (mark the sequence before the punctuation). Commas are counted as missing only when (1) introducing a direct quote, (2) in a series (Oxford not required), (3) after an introductory phrase/clause (not transition words).",
    apply_to: "space",
    type: "space",
  },
  inacc_incorrect: {
    symbol: "Ⓢ",
    category: "INACCURATE",
    button_text: "Ⓢ[text]Ⓢ",
    button_info: "Incorrect Punctuation or Grammar or Word/Meaning Doesn’t Fit",
    scoring_guide:
      "Incorrect Punctuation. Use when punctuation is misused. This applies to incorrect and missing punctuation.",
    apply_to: "wrap",
    type: "word",
  },
  inacc_seq: {
    symbol: "Ⓢ",
    category: "INACCURATE",
    button_text: "[text]Ⓢ",
    button_info: "Single Inaccurate Sequence",
    scoring_guide:
      "fdsghj fsdghj fdshj ",
    apply_to: "space",
    type: "space",
  },
  overlap_fit: {
    symbol: "Ⓢ",
    category: "OVERLAP",
    button_text: "Ⓢ[text]Ⓢ",
    button_info: "Feature common to both NMAE & DLD",
    scoring_guide:
      "Use when the word is spelled correctly and is the correct figure of speech, but does not convey the correct meaning in the context of the sentence",
    apply_to: "wrap",
    type: "word",
  },
  overlap_seq: {
    symbol: "Ⓢ",
    category: "OVERLAP",
    button_text: "[text]Ⓢ",
    button_info: "Single sequence feature common to both NMAE & DLD",
    scoring_guide: "Use when a word is missing",
    apply_to: "space",
    type: "space",
  },
  nmae_trans: {
    symbol: "Ⓢ",
    category: "NMAE",
    button_text: "Ⓢ[text]Ⓢ",
    button_info: "Feature of NMAE only",
    scoring_guide:
      "fdsa dfs fd sa  ",
    apply_to: "wrap",
    type: "word",
  },
  nmae_seq: {
    symbol: "Ⓢ",
    category: "NMAE",
    button_text: "[text]Ⓢ",
    button_info: "Single Sequence",
    scoring_guide:
      "Use this button when there is already another type of error marked at the beginning of the sentence.",
    apply_to: "space",
    type: "space",
  },
};

const copyPassage = `A little boy lived with his father in a large forest. Every day the father went out to cut wood. One day the boy was walking through the woods with a basket of lunch for his father. Suddenly he met a huge bear. The boy was frightened, but he threw a piece of bread and jelly to the bear. The bear thought it was very kind for the boy to share his lunch. Unfortunately, the bear did not like grape jelly. The bear decided to ask the boy if he wanted to go find some honey together instead. When the little boy saw the bear approach him and his father, he was frightened again. This time, the father told his son to be calm. It seemed like the bear was friendly. Together, the bear, son, and father went on a journey through the forest to find honey.`;
