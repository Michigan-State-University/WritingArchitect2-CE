// ----------------------------------------
// Global State Setup
// ----------------------------------------

let currentType = null; // Currently selected TIDE tag (T, I, D, E, C)
let selections = []; // Stores all selected text spans with metadata
let isDragging = false; // Tracks if the user is currently dragging
let draggedWords = []; // Temporarily stores words touched during drag (legacy - not used in final version)
let selectedIndexes = new Set(); // Keeps track of already-tagged word indexes
let startIndex = null; // Word index where drag started
let currentHoverIndex = null; // Word index under current mouse position during drag


// ----------------------------------------
// Sample Essay Words (Array of Strings)
// ----------------------------------------

let sampleWords = [];

// TIDE score totals (updated when scoring)
let scoreCounts = { T: 0, I: 0, D: 0, E: 0, C: 0 };


// ----------------------------------------
// Render Essay Text as Clickable Words
// ----------------------------------------

const sampleDiv = document.getElementById('sample-text');
sampleWords.forEach((word, index) => {
  const span = document.createElement('span');
  span.className = 'word';
  span.textContent = word;
  span.dataset.index = index;
  span.addEventListener('mousedown', startDrag);
  span.addEventListener('mouseover', dragOver);
  span.addEventListener('mouseup', endDrag);
  sampleDiv.appendChild(span);
});


// ----------------------------------------
// TIDE Button Selection Logic
// ----------------------------------------

function selectTIDE(type) {
  currentType = type;
  document.getElementById('current-selection').innerHTML = "Selected: <strong>" + type + "</strong>";
}


// ----------------------------------------
// Drag Selection Logic
// ----------------------------------------

function startDrag(e) {
  if (!currentType) return;
  isDragging = true;
  draggedWords = []; // optional legacy
  clearTempHighlights();

  const wordElement = e.target;
  startIndex = parseInt(wordElement.dataset.index);
  currentHoverIndex = startIndex;
  wordElement.classList.add('dragging-preview');
}

function dragOver(e) {
  if (!isDragging || !e.target.classList.contains('word')) return;
  currentHoverIndex = parseInt(e.target.dataset.index);
  previewSelection(); // highlights word range
}

function endDrag(e) {
  if (!isDragging) return;
  isDragging = false;

  const endIndex = parseInt(e.target.dataset.index);
  if (startIndex === null || endIndex === null) return;

  const lower = Math.min(startIndex, endIndex);
  const upper = Math.max(startIndex, endIndex);

  // Check for overlapping words already scored
  for (let i = lower; i <= upper; i++) {
    if (selectedIndexes.has(i)) {
      alert("❗ Cannot create selection: one or more words already scored. Please delete or reset first.");
      clearTempHighlights();
      startIndex = null;
      currentHoverIndex = null;
      return;
    }
  }

  const selectedText = sampleWords.slice(lower, upper + 1).join(' ');

  selections.push({
    id: Date.now(),
    type: currentType,
    text: selectedText,
    start: lower,
    end: upper + 1,
    score: 1
  });

  for (let i = lower; i <= upper; i++) selectedIndexes.add(i);

  scoreCounts[currentType]++;
  updateSelections();
  updateScoreTotals();

  clearTempHighlights();
  startIndex = null;
  currentHoverIndex = null;
}

// Highlights in grey the words the user is about to select
function previewSelection() {
  clearTempHighlights();
  if (startIndex === null || currentHoverIndex === null) return;

  const lower = Math.min(startIndex, currentHoverIndex);
  const upper = Math.max(startIndex, currentHoverIndex);

  for (let i = lower; i <= upper; i++) {
    const word = document.querySelector(`.word[data-index="${i}"]`);
    if (word) word.classList.add('dragging-preview');
  }
}


// ----------------------------------------
// Selection Management (Scoring Table)
// ----------------------------------------

function updateSelections() {
  const body = document.getElementById('selection-body');
  body.innerHTML = '';

  selections.forEach(sel => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${sel.type}</td>
      <td>${sel.text}</td>
      <td>
        <select onchange="updateScore(${sel.id}, this.value)">
          <option value="1" ${sel.score == 1 ? 'selected' : ''}>1</option>
          <option value="2" ${sel.score == 2 ? 'selected' : ''}>2</option>
        </select>
      </td>
      <td><button onclick="deleteSelection(${sel.id})">Delete</button></td>
    `;
    body.appendChild(row);
  });

  recolorWords();
}

function updateScore(id, value) {
  const sel = selections.find(s => s.id === id);
  if (sel) {
    sel.score = parseInt(value);
    updateScoreTotals();
  }
}

function deleteSelection(id) {
  const sel = selections.find(s => s.id === id);
  if (!sel) return;
  for (let i = sel.start; i < sel.end; i++) selectedIndexes.delete(i);
  selections = selections.filter(s => s.id !== id);
  scoreCounts[sel.type]--;
  updateSelections();
  updateScoreTotals();
}


// ----------------------------------------
// Visual Feedback (Coloring, Reset, Totals)
// ----------------------------------------

function recolorWords() {
  document.querySelectorAll('.word').forEach(word => word.style.backgroundColor = '');
  selections.forEach(sel => {
    for (let i = sel.start; i < sel.end; i++) {
      const word = document.querySelector(`.word[data-index="${i}"]`);
      if (word) word.style.backgroundColor = getColor(sel.type);
    }
  });
}

function clearTempHighlights() {
  document.querySelectorAll('.word.dragging-preview').forEach(w => {
    w.classList.remove('dragging-preview');
  });
}

function resetAll() {
  selections = [];
  selectedIndexes.clear();
  scoreCounts = { T: 0, I: 0, D: 0, E: 0, C: 0 };
  document.querySelectorAll('.word').forEach(word => {
    word.style.backgroundColor = '';
  });
  updateSelections();
  updateScoreTotals();
  document.getElementById('current-selection').innerHTML = "<em>No scoring type selected.</em>";
}

function updateScoreTotals() {
  let totals = { T: 0, I: 0, D: 0, E: 0, C: 0 };
  selections.forEach(sel => {
    if (totals.hasOwnProperty(sel.type)) {
      totals[sel.type] += sel.score;
    }
  });
  const row = document.getElementById('score-row');
  row.children[0].textContent = totals.T;
  row.children[1].textContent = totals.I;
  row.children[2].textContent = totals.D;
  row.children[3].textContent = totals.E;
  row.children[4].textContent = totals.C;
}


// ----------------------------------------
// Utility & Color Functions
// ----------------------------------------

function getColor(type) {
  switch(type) {
    case 'T': return '#ffadad';
    case 'I': return '#fdffb6';
    case 'D': return '#caffbf';
    case 'E': return '#9bf6ff';
    case 'C': return '#afafaf';
    default: return '#ffffff';
  }
}

function getClass(type) {
  switch(type) {
    case 'T': return 'wordBtn pink';
    case 'I': return 'wordBtn yellow';
    case 'D': return 'wordBtn green';
    case 'E': return 'wordBtn blue';
    case 'C': return 'wordBtn grey';
    default: return 'wordBtn';
  }
}


// ----------------------------------------
// Answer Key Handling (Display & Match)
// ----------------------------------------

let answerVisible = false;
const answerSelections = [
  { type: 'T', text: 'Many schools should adopt longer lunch breaks to improve student well-being.', score: 2 },
  { type: 'I', text: 'Studies show that when students have more time to eat and relax, their focus in class improves dramatically.', score: 2 },
  { type: 'D', text: 'At Lincoln High, for example, grades and attendance rose after the school extended lunch by just 15 minutes.', score: 2 },
  { type: 'D', text: 'In addition to academic benefits, longer breaks give students time to socialize, reducing stress and building stronger peer relationships.', score: 2 },
  { type: 'E', text: 'If schools truly care about student success, they must start by rethinking the time given to recharge during the day.', score: 2 }
];

function toggleAnswer() {
  answerVisible = !answerVisible;
  const section = document.getElementById('answer-section');
  document.getElementById('btn-show-answer').textContent = answerVisible ? "Hide Answer" : "Show Answer";
  section.style.display = answerVisible ? 'block' : 'none';
  if (answerVisible) populateAnswer();
}

function populateAnswer() {
  const textDiv = document.getElementById('answer-text');
  const tableBody = document.getElementById('answer-score-body');
  const answerTotals = { T: 0, I: 0, D: 0, E: 0, C: 0 };

  textDiv.innerHTML = '';
  let wordAssignments = new Array(sampleWords.length).fill(null);

  // Match answer phrases to word array
  answerSelections.forEach(sel => {
    const phraseWords = sel.text.split(' ');
    for (let i = 0; i <= sampleWords.length - phraseWords.length; i++) {
      let match = true;
      for (let j = 0; j < phraseWords.length; j++) {
        if (sampleWords[i + j].replace(/[.,]/g, '').toLowerCase() !== phraseWords[j].replace(/[.,]/g, '').toLowerCase()) {
          match = false;
          break;
        }
      }
      if (match) {
        for (let j = 0; j < phraseWords.length; j++) {
          wordAssignments[i + j] = sel.type;
        }
        answerTotals[sel.type] += sel.score;
        break;
      }
    }
  });

  // Render answer text spans
  sampleWords.forEach((word, index) => {
    const span = document.createElement('span');
    span.className = 'word';
    if (wordAssignments[index]) span.className += ' ' + getClass(wordAssignments[index]);
    span.textContent = word + ' ';
    textDiv.appendChild(span);
  });

  // Update totals
  const row = document.getElementById('answer-score-row');
  row.children[0].textContent = answerTotals.T;
  row.children[1].textContent = answerTotals.I;
  row.children[2].textContent = answerTotals.D;
  row.children[3].textContent = answerTotals.E;
  row.children[4].textContent = answerTotals.C;

  // Fill answer table rows
  tableBody.innerHTML = '';
  answerSelections.forEach(sel => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${sel.type}</td>
      <td>${sel.text}</td>
      <td>${sel.score}</td>
    `;
    tableBody.appendChild(row);
  });
}

function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\\]\\]/g, '\\$&');
}
