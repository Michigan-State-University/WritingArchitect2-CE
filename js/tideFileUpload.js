// External Dependencies: pdfjsLib and mammoth.js must be globally available
document.addEventListener("DOMContentLoaded", () => {
    const uploadInput = document.getElementById('file-upload');
    if (uploadInput) {
      uploadInput.addEventListener('change', handleFileUpload);
    }
  });
  
  async function handleFileUpload(event) {
    const file = event.target.files[0];
    const status = document.getElementById('upload-status');
    if (!file) return;
  
    let text = '';
    if (file.type === "application/pdf") {
      text = await extractTextFromPDF(file);
    } else if (file.name.endsWith(".docx")) {
      text = await extractTextFromDocx(file);
    } else {
      status.textContent = "❌ Unsupported file type.";
      return;
    }
  
    sampleWords = text.match(/[\w'-]+[.,!?]?/g) || [];

    countLongWords(text);

    updateWordBlocks();
    status.textContent = `✅ Uploaded: ${file.name}`;
  }
  
  async function extractTextFromPDF(file) {
    const typedarray = new Uint8Array(await file.arrayBuffer());
    const pdf = await pdfjsLib.getDocument(typedarray).promise;
    let fullText = '';
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);
      const content = await page.getTextContent();
      const strings = content.items.map(item => item.str);
      fullText += strings.join(' ') + ' ';
    }
    return fullText;
  }
  
  async function extractTextFromDocx(file) {
    const arrayBuffer = await file.arrayBuffer();
    const result = await mammoth.extractRawText({ arrayBuffer });
    return result.value;
  }
  
  function updateWordBlocks() {
    const sampleDiv = document.getElementById('sample-text');
    sampleDiv.innerHTML = '';
    selectedIndexes.clear();
    selections = [];
    scoreCounts = { T: 0, I: 0, D: 0, E: 0, C: 0 };
    updateScoreTotals();
  
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
    updateSelections();
  }
  


  // ----------------------------
// PDF Export with jsPDF
// ----------------------------
function downloadPDF() {
    const doc = new window.jspdf.jsPDF();
    const pageWidth = doc.internal.pageSize.getWidth() - 20;
    const margin = 10;
    const lineHeight = 7;
    const padding = 1;
    let x = margin;
    let y = 10;
  
    doc.setFontSize(12);
  
    for (let i = 0; i < sampleWords.length; i++) {
      const word = sampleWords[i] + ' ';
      const wordWidth = doc.getTextWidth(word);
      const sel = getSelectionByIndex(i);
      const bgColor = sel ? getHexColor(sel.type) : null;
  
      // Wrap to next line if word won't fit
      if (x + wordWidth > pageWidth + margin) {
        x = margin;
        y += lineHeight;
        if (y > 280) {
          doc.addPage();
          y = 10;
        }
      }
  
      // Draw background rectangle
      if (bgColor) {
        doc.setFillColor(...bgColor);
        doc.rect(x - padding, y - 5.5, wordWidth + padding * 2, 6.5, 'F');
      }
  
      // Draw word text in black
      doc.setTextColor(0, 0, 0);
      doc.text(word, x, y);
  
      x += wordWidth;
    }
  
    // Reset for tables
    x = margin;
    y += 2 * lineHeight;
    doc.setTextColor(0, 0, 0);
    doc.text("TIDE Score Totals", x, y);
    y += lineHeight;
  
    const recalculatedTotals = { T: 0, I: 0, D: 0, E: 0, C: 0 };
    selections.forEach(sel => {
      if (recalculatedTotals.hasOwnProperty(sel.type)) {
        recalculatedTotals[sel.type] += sel.score;
      }
    });
  
    Object.entries(recalculatedTotals).forEach(([key, val]) => {
      doc.text(`${key}: ${val}`, x, y);
      y += lineHeight;
    });
  
    y += lineHeight;
    doc.text("Selections:", x, y);
    y += lineHeight;
  
    selections.forEach(sel => {
      const line = `${sel.type}: "${sel.text}" (Score: ${sel.score})`;
      doc.text(line, x, y);
      y += lineHeight;
      if (y > 280) {
        doc.addPage();
        y = 10;
      }
    });
  
    doc.save("tide-scoring.pdf");
  }
  
  
  
  function getHexColor(type) {
    switch (type) {
      case 'T': return [255, 173, 173]; // pink
      case 'I': return [253, 255, 182]; // yellow
      case 'D': return [202, 255, 191]; // green
      case 'E': return [155, 246, 255]; // blue
      case 'C': return [175, 175, 175]; // grey
      default: return [0, 0, 0];
    }
  }
  
  function getSelectionByIndex(index) {
    return selections.find(sel => index >= sel.start && index < sel.end);
  }


  // ----------------------------
// DOCX Export with docx.js
// ----------------------------
function downloadDOCX() {
    const title = "TIDE Score Report";
    const bodyText = sampleWords.map((word, index) => {
        const sel = getSelectionByIndex(index);
        const color = sel ? getHtmlColor(sel.type) : null;
        return color
          ? `<span style="background-color:${color};">${word}</span>`
          : word;
      }).join(' ');
      
  
    const htmlContent = `
      <html xmlns:o='urn:schemas-microsoft-com:office:office'
            xmlns:w='urn:schemas-microsoft-com:office:word'
            xmlns='http://www.w3.org/TR/REC-html40'>
      <head><meta charset='utf-8'><title>DOCX Export</title></head>
      <body>
        <h2>${title}</h2>
        <p><strong>Essay:</strong></p>
        <p>${bodyText}</p>
        <p><strong>Selections:</strong></p>
        <ul>
          ${selections.map(sel => `<li>${sel.type}: "${sel.text}" (Score: ${sel.score})</li>`).join('')}
        </ul>
        <p><strong>Totals:</strong></p>
        <ul>
            ${Object.entries(calculateTotals()).map(([k,v]) => `<li>${k}: ${v}</li>`).join('')}
        </ul>
      </body>
      </html>
    `;
  
    const blob = new Blob(['\ufeff', htmlContent], {
      type: 'application/msword'
    });
  
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'tide-report.doc';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  
  
  function calculateTotals() {
    const totals = { T: 0, I: 0, D: 0, E: 0, C: 0 };
    selections.forEach(sel => {
      sel.score = parseInt(sel.score) || 1;
      if (totals.hasOwnProperty(sel.type)) {
        totals[sel.type] += sel.score;
      }
    });
    return totals;
  }
  



  
  function getHtmlColor(type) {
    switch (type) {
      case 'T': return '#ffadad';
      case 'I': return '#fdffb6';
      case 'D': return '#caffbf';
      case 'E': return '#9bf6ff';
      case 'C': return '#afafaf';
      default: return null;
    }
  }
  
  
  
  function buildScoreTable(totals) {
    const { Table, TableRow, TableCell, WidthType, Paragraph } = window.docx;
    const row = new TableRow({
      children: Object.values(totals).map(val =>
        new TableCell({
          width: { size: 1000, type: WidthType.DXA },
          children: [new Paragraph(val.toString())],
        })
      )
    });
  
    const header = new TableRow({
      children: Object.keys(totals).map(key =>
        new TableCell({
          width: { size: 1000, type: WidthType.DXA },
          children: [new Paragraph(key)],
        })
      )
    });
  
    return new Table({
      rows: [header, row],
    });
  }
  
  function buildSelectionTable() {
    const { Table, TableRow, TableCell, Paragraph } = window.docx;
    const rows = [
      new TableRow({
        children: ["Element", "Text", "Score"].map(cell =>
          new TableCell({ children: [new Paragraph(cell)] })
        )
      }),
      ...selections.map(sel =>
        new TableRow({
          children: [
            new TableCell({ children: [new Paragraph(sel.type)] }),
            new TableCell({ children: [new Paragraph(sel.text)] }),
            new TableCell({ children: [new Paragraph(sel.score.toString())] }),
          ]
        })
      )
    ];
  
    return new Table({ rows });
  }


  // Counter function for finding how many 7+ char long words there are
  function countLongWords(text) {
    const cleaned = text
        .replace(/[^\w\s]|_/g, "") // remove punctuation
        .replace(/\s+/g, " ");     // normalize spacing
    const words = cleaned.split(" ");
    const longWords = words.filter(word => word.length >= 7);
    document.getElementById("long-word-count").textContent = longWords.length;
}

  




// Make export functions accessible globally (for HTML onclick)
window.downloadPDF = downloadPDF;
window.downloadDOCX = downloadDOCX;

  