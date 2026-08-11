<!-- Bottom Sheets Bar & Status Bar -->
<div id="bottombar">
  <button class="add-sheet-btn" id="addSheetBtn" title="Add sheet">
    <span class="material-symbols-outlined" style="font-size:20px;">add</span>
  </button>
  <button class="add-sheet-btn" title="All sheets">
    <span class="material-symbols-outlined" style="font-size:20px;">menu</span>
  </button>
  <div class="sheets-tabs-container" id="sheetsTabs"></div>

  <button class="btn btn-secondary" id="addRowsBtn" onclick="addRows(50)" title="Add 50 rows" style="margin-left: 12px; font-size: 12px; padding: 2px 10px; border-radius: 12px; cursor: pointer; white-space: nowrap;">+ 50 rows</button>

  <div class="quick-stats" id="quickStats">
    <span>Sum: <strong id="statSum">0</strong></span>
    <span>Average: <strong id="statAvg">0</strong></span>
    <span>Count: <strong id="statCount">0</strong></span>
  </div>
</div>

<!-- Toast Notice -->
<div id="toast"></div>
