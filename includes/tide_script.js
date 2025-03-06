let inputText = `This is a story all about How my life got twist-turned upside down And I liked to take a minute and sit right there And tell you how I became the prince of a town called Bel Air  In West Philadelphia born and raised On the playground was where I spent most of my days Chillin' out, maxin', relaxin' all cool And all shootin' some b-ball outside of the school  When a couple of guys who were up to no good Startin', makin' trouble in my neighborhood I got in one lil' fight and my mom got scared She said, "You're movin' with your auntie and uncle in Bel Air`;

let tideColors = {
  T: {
    label: "Topic",
    color: "pink",
    hex: "#ffadad",
  },
  I: {
    label: "Idea",
    color: "yellow",
    hex: "#fdffb6",
  },
  D: {
    label: "Details",
    color: "green",
    hex: "#caffbf",
  },
  E: {
    label: "Ending",
    color: "blue",
    hex: "#9bf6ff",
  },
  C: {
    label: "Copy",
    color: "grey",
    hex: "#afafaf",
  },
};

function reset(isinitialload, q_id) {
  // Get ?id from query string
  let sessionID = new URLSearchParams(window.location.search).get("id");

  $.ajax({
    url: "../ajax/get_TIDE_data.php?id=" + sessionID,
    type: "GET",
    //  dataType: 'jsonp',
    data: { Q_ID: q_id },
    success: function (data) {
      items = data.split("||");
      tide_base = items[0];
      tide_markup = items[1];
      tide_score = items[2];
      if (tide_markup === null) tide_markup = "";
      if (tide_score === null) tide_score = "[]";
      if (isinitialload && tide_markup.length > 0) {
        let textDiv = document.getElementById("sourceText");
        //if (text.indexOf("class=") != -1) textDiv.innerHTML = stringToButtons(textDiv.innerHTML);
        textDiv.innerHTML = tide_markup;
        localStorage.setItem("TIDE", "");
        localStorage.setItem("scoring", tide_score);
        document.getElementById("selectedTIDE").innerHTML =
          "Select a TIDE scoring type";
        jsonToTable([{ T: 0, I: 0, D: 0, E: 0, C: 0 }], "scoreTotals");
        //	jsonToTable([{element:'', selection:'', score:'', start:'',end:'', Delete:''}],'display_json_data');
        let scoring = JSON.parse(localStorage.getItem("scoring")) || [];
        jsonToTable(scoring, "display_json_data");
        tideSelect();
        updateTotals();
      } else {
        if (isinitialload) {
          localStorage.setItem("TIDE", "");
          localStorage.setItem("scoring", "[]");
        }
        jp = JSON.parse(localStorage.getItem("scoring"));
        if (JSON.parse(localStorage.getItem("scoring")).length > 0) {
          let confirmReset = window.confirm("Reset all scoring?");
          if (!confirmReset) {
            return;
          }
        }
        let textDiv = document.getElementById("sourceText");
        //if (text.indexOf("class=") != -1) textDiv.innerHTML = stringToButtons(textDiv.innerHTML);
        textDiv.innerHTML = stringToButtons(tide_base);
        localStorage.setItem("TIDE", "");
        localStorage.setItem("scoring", "[]");
        document.getElementById("selectedTIDE").innerHTML =
          "Select a TIDE scoring type";
        jsonToTable([{ T: 0, I: 0, D: 0, E: 0, C: 0 }], "scoreTotals");
        jsonToTable(
          [
            {
              element: "",
              selection: "",
              score: "",
              start: "",
              end: "",
              Delete: "",
            },
          ],
          "display_json_data",
        );
        tideSelect();
      }
    },
    error: function (data) {
      alert("error: " + data.responseText);
    },
  });
}

function tideSelect(id) {
  [...document.querySelectorAll("#buttonbar button")].forEach((el) =>
    el.classList.remove("selectedTIDE"),
  );
  if (id == undefined) {
    return;
  }
  localStorage.setItem("TIDE", id);
  let color = tideColors[id]["color"];
  let label = tideColors[id]["label"];
  localStorage.setItem("color", color);
  localStorage.setItem("letter", label[0]);
  document.getElementById(id).classList.add("selectedTIDE");
  document.getElementById("selectedTIDE").innerHTML = label;
}

function stringToButtons(str) {
  let strArr = str.split(" ");
  return strArr
    .map((char, idx) => {
      return `<span class='wordBtn' id='${idx}' onMouseDown='btnMouseDown(${idx})' onMouseUp='btnMouseUp(${idx})'>${char}</span> `;
    })
    .join("");
}

function isHighlighted(id) {
  return [...document.getElementById(id).classList].some((c) =>
    ["green", "yellow", "pink", "blue"].includes(c),
  );
}

function btnMouseDown(id) {
  let selectedColor = localStorage.getItem("color");

  if (isHighlighted(id) && selectedColor != "grey") {
    alert("First word is already highlighted.");
    return;
  }

  let startWord = document.getElementById(id);
  let currentClasses = startWord.classList;
  console.log(currentClasses);
  if (
    selectedColor !== "grey" &&
    [...currentClasses].some((c) =>
      ["green", "yellow", "pink", "blue"].includes(c),
    )
  ) {
    window.alert("word already scored");
    localStorage.setItem("start", "-1");
    return;
  }
  localStorage.setItem("start", id);
}

async function btnMouseUp(id) {
  let selectedColor = localStorage.getItem("color");

  if (isHighlighted(id) && selectedColor != "grey") {
    alert("End word is already highlighted.");
    return;
  }

  let start = parseInt(localStorage.getItem("start"));
  let end = parseInt(id) + 1;

  if (start == -1) {
    return;
  } //score already applied to start word
  if (start > end) {
    alert("Select text from left to right.");
    return;
  }

  if (
    selectedColor != "grey" &&
    Array.from({ length: end - start })
      .fill(0)
      .some((el, idx) => isHighlighted(idx + start))
  ) {
    alert("Selection contains previously scored word(s).");
    return;
  }

  let color = localStorage.getItem("color");
  let letter = localStorage.getItem("letter");
  let btnArr = document.querySelectorAll("#sourceText span");
  let strArr = [...btnArr].map((btn) => btn.innerHTML);
  let str = strArr.slice(start, end).join(" ");
  console.log(str);

  for (let i = start; i < end; i++) {
    let span = document.getElementById(i);
    span.classList.remove(["T", "I", "D", "E"]);
    span.classList.add(color);
  }

  let scoring = JSON.parse(localStorage.getItem("scoring")) || [];
  let style = letter == "C" ? "display:none" : "";
  scoring.push({
    element: letter,
    selection: str,
    score: scoreSelOpt(scoring.length, "1", style),
    start,
    end,
  });

  await localStorage.setItem("scoring", JSON.stringify(scoring));
  console.log(scoring);
  jsonToTable(scoring, "display_json_data");
  updateTotals();
}

function getScore() {
  let btnArr = document.querySelectorAll("#sourceText span");
  let scoreTotals = { T: 0, I: 0, D: 0, E: 0, C: 0 };

  [...btnArr].map((btn) => console.log(btn.style.background));
}

function jsonToTable(jsonData, tableSelector) {
  let table = document.getElementById(tableSelector);
  if (jsonData.length == 0) {
    table.innerHTML = "";
  }

  if (tableSelector == "display_json_data") {
    jsonData.forEach((row, idx) => (row["Delete"] = deleteBtn(idx)));
  }
  let headers = Object.keys(jsonData[0]);
  //Display in correct order
  if (tableSelector == "scoreTotals") {
    headers = ["T", "I", "D", "E", "C"];
  }

  let headerRowHTML = "<tr>";
  for (let i = 0; i < headers.length; i++) {
    headerRowHTML += "<th>" + headers[i] + "</th>";
  }
  headerRowHTML += "</tr>";

  let allRecordsHTML = "";
  for (let i = 0; i < jsonData.length; i++) {
    allRecordsHTML += "<tr>";
    for (let j = 0; j < headers.length; j++) {
      let header = headers[j];
      allRecordsHTML +=
        "<td>" +
        (jsonData[i][header] == undefined ? "" : jsonData[i][header]) +
        "</td>";
    }
    allRecordsHTML += "</tr>";
  }

  table.innerHTML = headerRowHTML + allRecordsHTML;
}

function scoreSelOpt(id, selectedValue, style) {
  let selectEl = `<select id="sel_${id}" onChange="scoreTotals(this.id,this.value)" data-selected="${selectedValue}" style="${style}"></select>`;
  let options = [0, 1, 2]
    .map(
      (val) =>
        `<option value="${val}"${
          selectedValue == val ? "selected" : ""
        }>${val}</option>`,
    )
    .join("");
  return selectEl.replace("</", options + "</");
}

function deleteBtn(id) {
  return `<button id="del_${id}" onClick="deleteElement(this.id)">Delete</button>`;
}

function deleteElement(id) {
  if (window.confirm("Delete this scoring element?") !== true) {
    return false;
  }
  let idx = parseInt(id.split("_")[1]);
  let scoringData = JSON.parse(localStorage.getItem("scoring")) || [];
  let color = tideColors[scoringData[idx].element].color;
  for (let i = scoringData[idx].start; i < scoringData[idx].end; i++) {
    document.getElementById(i).classList.remove(color);
  }
  if (scoringData.length == 1) {
    scoringData = [];
  } else {
    scoringData.splice(idx, 1);
  }
  localStorage.setItem("scoring", JSON.stringify(scoringData));
  console.log(scoringData);
  jsonToTable(scoringData, "display_json_data");
  //scoreTotals();
  updateTotals();
}

async function scoreTotals(selectId, selectedValue) {
  let scoringData = JSON.parse(localStorage.getItem("scoring"));
  let updatedRow = parseInt(selectId?.split("_")[1]) || -1;
  if (selectId?.split("_")[1] == "0") {
    updatedRow = 0;
  }
  if (updatedRow > -1 && selectedValue != "") {
    scoringData[updatedRow].score = scoreSelOpt(updatedRow, selectedValue);
    await localStorage.setItem("scoring", JSON.stringify(scoringData));
    console.log({ scoringData, updatedRow, selectedValue });
    await updateTotals();
  }
}

function updateTotals() {
  let scoringData = JSON.parse(localStorage.getItem("scoring"));
  let currentScores = [...document.querySelectorAll("tr select")].map(
    (sel) => sel.value,
  );
  let currentElements = scoringData.map((row) => row.element);
  let totals = {};
  let selectInputs = [...document.querySelectorAll("select")];
  currentElements.forEach(
    (el, i) =>
      (totals[el] =
        el == "C"
          ? (parseInt(totals[el]) || 0) + 1
          : (parseInt(totals[el]) || 0) + parseInt(currentScores[i])),
  );
  // currentElements.forEach(
  //     (el, i) =>  (totals[el] = (parseInt(totals[el])||0) + parseInt(currentScores[i]))
  //     );
  console.log("totals=", totals);
  jsonToTable([totals], "scoreTotals");
  return totals;
}

//scoringData[i].selection.split(' ').length

// function updateSelectedIndexes() {
//     let scoringData = JSON.parse(localStorage.getItem('scoring'));
//     let selectInputs = [...document.querySelectorAll('select')];
//     scoringData.map((el,i)=>selectInputs[i].selectedIndex=scoringData[i].score)
// }
