// ***************************************************************************
// SAVE AND UNDO
// ***************************************************************************

let undoLog = [];  // array of objects for undo scoring
let isEditing = false // set to true on first scoring, false after saving

window.addEventListener('beforeunload', function (e) {
  if( isEditing ){
    // Cancel the event
    e.preventDefault(); // If you prevent default behavior in Mozilla Firefox prompt will always be shown
    // Chrome requires returnValue to be set
    e.returnValue = '';
  }
});

function undoStack(){
  if(undoLog.length > 1 && Array.isArray(undoLog) ){
    let lastEdit = undoLog.pop();
    document.getElementById("essay-scoring").innerHTML = lastEdit;
    countScores();
  } else {
    isEditing = false;
    document.getElementById("btn-save").disabled = true;
    document.getElementById("btn-sign").disabled = true;
  };
}

function resetSelected(){
  const activeBtn = document.getElementsByClassName("btn-active")[0];
  activeBtn.innerHTML = "^";
  activeBtn.setAttribute("data-score","");
  activeBtn.classList.remove("btn-active")
  document.getElementById("reset-selected").disabled = true;
  countScores()
}

function resetAll(){
  if(confirm("Reset All Scoring?")){
    parseInput();
    document.getElementById("reset-all").disabled = true;  
    countScores()  
  }
}

function saveScoring(){
  alert('scoring progress saved')
}

function completeScoring(){
  confirm('Save and submit final score?')
}

function loadResponse(){
  essayRaw = document.getElementById("essay-raw-in")
  essayRaw.innerHTML = copyPassage;
  parseInput()
}

// ***************************************************************************
// WORD BUTTONS
// ***************************************************************************

function strToObjArr(str) {
  const keepLineReturns = str.replace(/(\r\n|\n|\r)/gm, " ||br|| ");
  const puncSeparate = keepLineReturns.replace(/((?:\.|\?|!|,|;)+)/g, " $1 ");
  // wrap in spaces for outter writing sequences, split to array
  let strArr = puncSeparate.replace(/(.+)/," $1 ").split(/(\s+)/);
  // remove empty elements from splitting with spaces at both ends
  strArr.pop();
  strArr.shift();
  let objArr = [];
  strArr.map((str, idx) => {
    let type = str.match(/^\s+$/) ? "space" : str === "||br||" ? "break" : "word";
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
  return btnArr.join("");
}

function makeWordBtn(id, word, btnType) {
  return btnType === "break" ? "<br>" 
  : `<button id="${id}" data-score="" class="btn-${btnType}" onclick="selectToScore(this.id)">${word}</button>`;
}

function parseInput(){
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

function selectToScore(id){
  const btn = document.getElementById(id);
  const elements = document.querySelectorAll(".btn-active");
  elements.forEach(el => {el.classList.remove("btn-active")})
  btn.classList.add("btn-active"); 
  const scTypeBtns = document.querySelectorAll(".btn-sc-type");
  const activeBtnType = btn.classList.contains("btn-space") ? "space" : "word";
  
  scTypeBtns.forEach(el => {
    const btnType = scoreConfig[el.id].type;
    el.disabled = btnType == activeBtnType ? false : true;
  })
  if(btn.innerHTML != "^" && activeBtnType == "space"){
    document.getElementById("reset-selected").disabled = false
  } else {
    document.getElementById("reset-selected").disabled = true
  }
}

function applyScoring(scoreType){
  const activeBtn = document.getElementsByClassName("btn-active")[0];
  
  if(!activeBtn){
    alert("Select a word or space to score, then click a scoring button.")
  }else{
    let lastEdit = document.getElementById("essay-scoring").innerHTML;
    undoLog.push(lastEdit);
    isEditing = true;
    document.getElementById("reset-all").disabled = false;
    document.getElementById("undo").disabled = false;
    document.getElementById("btn-save").disabled = false;
    document.getElementById("btn-sign").disabled = false;
    document.querySelector(".btn-active").classList.remove("btn-active") ;

    const sc = scoreConfig[scoreType];
    const applyTo   = sc.apply_to;
    const btnLeft   = document.getElementById(Number(activeBtn.id) - 1 );
    const btnRight  = document.getElementById(Number(activeBtn.id) + 1 );
    const wObjLeft  = undoLog.slice(-1)[0][Number(activeBtn.id) - 1];
    const wObj      = undoLog.slice(-1)[0][Number(activeBtn.id)    ];
    const wObjRight = undoLog.slice(-1)[0][Number(activeBtn.id) + 1];
    
    if(["left","wrap"].includes(applyTo)){ 
      btnLeft.innerHTML = sc.symbol;
      btnLeft.setAttribute("data-score",scoreType);
      wObjLeft.format = scoreType;
    };
    if(["right","wrap"].includes(applyTo)){ 
      btnRight.innerHTML = sc.symbol
      btnRight.setAttribute("data-score",scoreType);
      wObjRight.format = scoreType;
    };
    if(applyTo == "space"){ 
      activeBtn.innerHTML = sc.symbol
      activeBtn.setAttribute("data-score",scoreType);
      wObj.format = scoreType
    };
  }
  countScores();
}

function makeScoreBtns(scoreTypes,forFS){
  const scoreArr = scoreTypes.split(",");
  const fieldsetHtml = document.getElementById(forFS);
  const allBtns = scoreArr.map((type,idx) => {
    const sc = scoreConfig[type];
    const symbol = `<code class="sc-symbol" data-score="${type}">${sc.symbol.charAt(0)}</code>`;
    const label = `<label class="sc-category" data-score="${type}">${sc.category}</label>`;
    const buttonText = sc.button_text.replace(/\[text\]/g,`<span style="color:black">[text]</span>`)
    const button = `<button onclick="applyScoring(this.name)" id="${type}" name="${type}" title="${sc.scoring_guide}" class="btn btn-sc-type" data-score="${type}">${buttonText}</button>`;
    const info = `<label class="sc-type-info">${sc.button_info}</label>`;
    return symbol + label + button + info + (idx + 1 == scoreArr.length ? "" : "<br>")
  });
  fieldsetHtml.innerHTML = fieldsetHtml.innerHTML + allBtns.join("")
}

// ***************************************************************************
// CALCULATE SCORES
// ***************************************************************************

function countInput(){
  const essayRaw = document.getElementById("essay-raw-in").value.trim();
  const wordArr = essayRaw.match(/\w+/g);
  const countW = wordArr.length;
  const countS = essayRaw.split(/[!|.|?]+/).length - 1;
  const countWGTE7 = essayRaw.match(/\w{7,}/g,)?.length || 0;
  document.getElementById("essay-sentences").innerHTML = countS;
  document.getElementById("essay-words").innerHTML = countW;
  document.getElementById("essay-wordsGTE7").innerHTML = countWGTE7;
}

function countScores(){
  const essayRaw = document.getElementById("essay-raw-in").value.trim();
  const essayScoring = document.getElementById("essay-scoring");
  const countWE = essayScoring.innerText.split("Ⓦ").length - 1;
  const countSE = essayScoring.innerText.split("Ⓢ").length - 1;
  const countTE = countWE + countSE;
  const countWS = document.getElementsByClassName("btn-space").length;
  const cws = essayScoring.innerHTML.split("^").length - 1 || 0;
  const se = essayScoring.innerHTML.split("Ⓢ").length - 1 || 0;
  const we = essayScoring.innerHTML.split("Ⓦ").length - 1 || 0;
  const te = se + we;
  const ts = te + cws;
  const ciws = cws - te; 
  const wa = (1 - (we/ts)).toFixed(3);
  const sa = (1 - (se/ts)).toFixed(3);
  const sentences = essayRaw.split(/[.|?|!]+/).filter((sent) => sent.length > 0);
  const sentLenArr = sentences.map((s) => s.trim().split(/\s/).length);  
  const sComp = getStandardDeviation(sentLenArr);
  document.getElementById("sc-ciws").innerHTML = ciws;
  document.getElementById("sc-we").innerHTML = we;
  document.getElementById("sc-se").innerHTML = se;
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
    array.map((x) => Math.pow(x - mean, 2)).reduce((a, b) => a + b) / n
  );
}
// ***************************************************************************
// Typing Test Scoring Functions
// ***************************************************************************



function loadPassage(){
  document.getElementById("copy-response").innerHTML = copyPassage;
  document.getElementById("copy-passage").innerHTML = copyPassage;
  scoreTyping()
}

function scoreTyping(){
  var currentResponse = document.getElementById("copy-response").value;
  var responseWords = currentResponse.split(/\s+/g);
  var sourceWords = copyPassage.split(/\s+/g);
  document.getElementById("count-response-word").innerHTML = responseWords.length;
  document.getElementById("count-response-char").innerHTML = currentResponse.length;
  document.getElementById("count-source-word").innerHTML = sourceWords.length;
  document.getElementById("count-source-char").innerHTML = copyPassage.length;
  console.log("words: " + responseWords.length);
}

// ***************************************************************************
// REF DATA
// ***************************************************************************

const scoreConfig = {
  "cap_missing": {
    "symbol": "Ⓦ",
    "category": "CAPITAL",
    "button_text": "Ⓦ[text]",
    "button_info": "Missing Capitalization",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "space",
    "type": "space"
  },
  "spell_error": {
    "symbol": "Ⓦ",
    "category": "SPELLING",
    "button_text": "Ⓦ[text]Ⓦ",
    "button_info": "Misspelled Word",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "wrap",
    "type": "word"
  },
  "spell_seq": {
    "symbol": "Ⓦ",
    "category": "SPELLING",
    "button_text": "[text]Ⓦ",
    "button_info": "Single Incorrect Sequence",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "space",
    "type": "space"
  },
  "gram_tense": {
    "symbol": "Ⓢ",
    "category": "GRAMMAR",
    "button_text": "Ⓢ[text]Ⓢ",
    "button_info": "Tense Error",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "wrap",
    "type": "word"
  },
  "punc_missing": {
    "symbol": "ⓈⓈ",
    "category": "PUNCTUATION",
    "button_text": "[text]ⓈⓈ",
    "button_info": "Missing Punctuation",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "space",
    "type": "space"
  },
  "sem_fit": {
    "symbol": "Ⓢ",
    "category": "SEMANTICS",
    "button_text": "Ⓢ[text]Ⓢ",
    "button_info": "Meaning/Doesn't Fit",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "wrap",
    "type": "word"
  },
  "syn_missing": {
    "symbol": "ⓈⓈ",
    "category": "SYNTAX",
    "button_text": "[text]ⓈⓈ[text]",
    "button_info": "Word Missing",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "space",
    "type": "space"
  },
  "syn_trans": {
    "symbol": "Ⓢ",
    "category": "SYNTAX",
    "button_text": "Ⓢ[text]Ⓢ",
    "button_info": "Transpsed/ Extra Word",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "wrap",
    "type": "word"
  },
  "syn_agree": {
    "symbol": "Ⓢ",
    "category": "SYNTAX",
    "button_text": "Ⓢ[text]Ⓢ",
    "button_info": "v/subj - n/mod Agreement",
    "scoring_guide": "scoring instructions: asd fa sdfsdf asdf. asdfsdf aadsfasdfasdf.",
    "apply_to": "wrap",
    "type": "word"
  }
}

const copyPassage = `A little boy lived with his father in a large forest. Every day the father went out to cut wood. One day the boy was walking through the woods with a basket of lunch for his father. Suddenly he met a huge bear. The boy was frightened, but he threw a piece of bread and jelly to the bear. The bear thought it was very kind for the boy to share his lunch. Unfortunately, the bear did not like grape jelly. The bear decided to ask the boy if he wanted to go find some honey together instead. When the little boy saw the bear approach him and his father, he was frightened again. This time, the father told his son to be calm. It seemed like the bear was friendly. Together, the bear, son, and father went on a journey through the forest to find honey.`

