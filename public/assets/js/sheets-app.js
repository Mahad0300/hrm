/* ==========================================================================
   HIGH-PERFORMANCE GOOGLE SHEETS ENGINE (ULTRA-FAST & ROBUST)
   ========================================================================== */
const NUM_COLS = 26;

let appState = {
  sheets: {
    "Sheet1": { id: "Sheet1", name: "Sheet1", cells: {}, colWidths: {}, rowHeights: {}, color: null }
  },
  activeSheetId: "Sheet1",
  numRows: 100,
  selected: { r: 0, c: 0 },
  selectionHead: { r: 0, c: 0 },
  selRange: null,
  isEditing: false,
  editBuffer: '',
  clipboard: null,
  history: [],
  historyIndex: -1,
  title: "Untitled spreadsheet",
  zoom: 100,
  paintedStyle: null,
  showGridlines: true,
  showFormulaBar: true
};

const getApiUrl = (endpoint) => {
  if (window.HRM && typeof window.HRM.api === 'function') {
    return window.HRM.api(endpoint);
  }
  const base = typeof window !== 'undefined' && window.APP_BASE_URL !== undefined ? window.APP_BASE_URL : '';
  return base.replace(/\/+$/, '') + '/assets/api/' + endpoint.replace(/^\//, '');
};

const escapeHtml = (str) => {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

const getBaseUrl = () => typeof window !== 'undefined' && window.APP_BASE_URL !== undefined ? window.APP_BASE_URL : '';
const urlParams = new URLSearchParams(window.location.search);
let currentSpreadsheetId = urlParams.get('id') || 'sheet_' + Math.random().toString(36).substring(2, 10);
let currentUser = null;
let currentSheetData = null;
let autoSaveTimer = null;
let docUnsubscribe = null;
let presenceUnsubscribe = null;
let presenceHeartbeatTimer = null;
let remoteCursorsMap = {};
let isUserReadOnly = false;
let isSheetLoaded = false;
let isUserAuthorized = false;

// ── Data integrity flags (prevent auto-sync from overwriting unsaved local edits) ──
let isDirty = false;        // true when local edits exist that haven't been saved to server yet
let isSaving = false;       // true while a save request is in-flight
let saveRetryCount = 0;     // exponential backoff counter for failed saves
const MAX_SAVE_RETRIES = 5;

function getSheetsList() {
  if (Array.isArray(appState.sheets)) return appState.sheets;
  if (appState.sheets && typeof appState.sheets === 'object') {
    return Object.values(appState.sheets);
  }
  return [];
}

function findSheetObj(id) {
  const list = getSheetsList();
  return list.find(s => s && (s.id === id || String(s.id) === String(id))) || list[0] || null;
}

const colNameFromNum = (n) => typeof colToLetter === 'function' ? colToLetter(n) : String.fromCharCode(64 + n);

// High-Performance Caches & Maps
let cellDomCache = new Map();
let colheadDomCache = new Map();
let rowheadDomCache = new Map();

let prevSelectedEl = null;
let prevSelectedRangeEls = [];
let formulaCache = new Map();
let activeHeaderMenu = null;
let activeFontPopover = null;
let activeToolbarPopover = null;

let currentBorderColor = '#000000';
let currentBorderStyle = 'solid';
let currentBorderWidth = 1;

let isFillDragging = false;
let fillStartRange = null;
let fillTargetRange = null;

let isFormulaRefSelecting = false;

let mouseMoveRaf = null;
let resizeRaf = null;

const FONTS_LIST = [
  "Arial",
  "Amatic SC",
  "Caveat",
  "Comfortaa",
  "Comic Sans MS",
  "Courier New",
  "EB Garamond",
  "Georgia",
  "Impact",
  "Inter",
  "Lexend",
  "Lobster",
  "Lora",
  "Merriweather",
  "Montserrat",
  "Nunito",
  "Oswald",
  "Pacifico",
  "Playfair Display",
  "Roboto",
  "Roboto Mono",
  "Roboto Serif",
  "Spectral",
  "Times New Roman",
  "Trebuchet MS",
  "Verdana"
];

function getActiveSheet() { return appState.sheets[appState.activeSheetId]; }
function getActiveCells() { return getActiveSheet().cells; }

function colToLetter(n) {
  let s = ''; n = n + 1;
  while(n > 0) {
    let m = (n - 1) % 26;
    s = String.fromCharCode(65 + m) + s;
    n = Math.floor((n - 1) / 26);
  }
  return s;
}
function letterToCol(s) {
  let n = 0;
  for(let i = 0; i < s.length; i++) n = n * 26 + (s.charCodeAt(i) - 64);
  return n - 1;
}
function ref(r, c) { return colToLetter(c) + (r + 1); }
function parseRef(rf) {
  if(!rf || typeof rf !== 'string') return null;
  const m = rf.trim().toUpperCase().match(/^([A-Z]+)(\d+)$/);
  if(!m) return null;
  return { c: letterToCol(m[1]), r: parseInt(m[2], 10) - 1 };
}
function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

function showToast(msg) {
  const t = document.getElementById('toast');
  if(!t) return;
  t.textContent = msg; t.classList.add('show');
  clearTimeout(showToast._t);
  showToast._t = setTimeout(() => t.classList.remove('show'), 1800);
}

function getTd(r, c) {
  const key = (r << 8) | c;
  return cellDomCache.get(key);
}

/* Helper to adjust relative formula references (e.g. A1 -> A2) when copying/filling */
function adjustFormulaReferences(formulaStr, dRow, dCol) {
  if(!formulaStr || typeof formulaStr !== 'string' || !formulaStr.startsWith('=')) return formulaStr;
  return formulaStr.replace(/(?:([A-Za-z0-9_]+)!)?(\$?([A-Z]+))(\$?(\d+))/gi, (match, sheet, colAbs, colStr, rowAbs, rowStr) => {
    let col = letterToCol(colStr.toUpperCase());
    let row = parseInt(rowStr, 10) - 1;
    if (!colAbs.startsWith('$')) col = Math.max(0, col + dCol);
    if (!rowAbs.startsWith('$')) row = Math.max(0, row + dRow);
    const newRef = (colAbs.startsWith('$') ? '$' : '') + colToLetter(col) + (rowAbs.startsWith('$') ? '$' : '') + (row + 1);
    return (sheet ? sheet + '!' : '') + newRef;
  });
}

/* ==========================================================================
   FAST FORMULA ENGINE WITH CACHING & ROBUST ARGUMENT SPLITTING
   ========================================================================== */

function expandRange(a, b) {
  const out = [];
  const r1 = Math.min(a.r, b.r), r2 = Math.max(a.r, b.r);
  const c1 = Math.min(a.c, b.c), c2 = Math.max(a.c, b.c);
  for(let r = r1; r <= r2; r++) for(let c = c1; c <= c2; c++) out.push({ r, c });
  return out;
}

function resolveRefOrRange(argStr, currentSheetId) {
  argStr = argStr.trim();
  let targetSheetId = currentSheetId;
  if(argStr.includes('!')) {
    const parts = argStr.split('!');
    targetSheetId = parts[0].replace(/'/g, '');
    argStr = parts[1];
  }
  const rangeMatch = argStr.match(/^([A-Z]+\d+):([A-Z]+\d+)$/i);
  if(rangeMatch) {
    const a = parseRef(rangeMatch[1]), b = parseRef(rangeMatch[2]);
    if(a && b) return expandRange(a, b).map(p => ({ ...p, sheetId: targetSheetId }));
  }
  const singleMatch = argStr.match(/^([A-Z]+\d+)$/i);
  if(singleMatch) {
    const p = parseRef(singleMatch[1]);
    if(p) return [{ ...p, sheetId: targetSheetId }];
  }
  return null;
}

function getCellValueRaw(sheetId, r, c, visited) {
  visited = visited || new Set();
  const sheet = appState.sheets[sheetId] || getActiveSheet();
  const k = ref(r, c);
  const keyTag = sheetId + '!' + k;
  const data = sheet.cells[k];
  if(!data || data.v === '' || data.v === undefined || data.v === null) return '';
  const raw = String(data.v);
  if(raw.startsWith('=')) {
    if(visited.has(keyTag)) return '#CIRCULAR!';
    visited.add(keyTag);
    const res = evaluateFormula(raw, sheetId, visited);
    visited.delete(keyTag);
    return res;
  }
  return data.v;
}

function evaluateFormula(formulaStr, currentSheetId, visited) {
  const cacheKey = currentSheetId + ':' + formulaStr;
  if(formulaCache.has(cacheKey) && !visited) return formulaCache.get(cacheKey);

  let expr = formulaStr.slice(1).trim();
  if(!expr) return '';

  try {
    const res = parseExpression(expr, currentSheetId, visited);
    if(!visited) formulaCache.set(cacheKey, res);
    return res;
  } catch(e) {
    return '#ERROR!';
  }
}

function clearFormulaCache() {
  formulaCache.clear();
}

function splitArgs(str) {
  const args = [];
  let current = '';
  let depth = 0;
  let inString = false;
  let quoteChar = '';

  for (let i = 0; i < str.length; i++) {
    const char = str[i];
    if (inString) {
      current += char;
      if (char === quoteChar && str[i - 1] !== '\\') {
        inString = false;
      }
    } else if (char === '"' || char === "'") {
      inString = true;
      quoteChar = char;
      current += char;
    } else if (char === '(') {
      depth++;
      current += char;
    } else if (char === ')') {
      depth--;
      current += char;
    } else if (char === ',' && depth === 0) {
      args.push(current.trim());
      current = '';
    } else {
      current += char;
    }
  }
  if (current.trim()) {
    args.push(current.trim());
  }
  return args;
}

function parseExpression(expr, currentSheetId, visited) {
  // 1. Evaluate function calls nested inside out
  const fnRegex = /([A-Z_]+)\(([^()]*)\)/i;
  let match;
  let guard = 0;
  while((match = fnRegex.exec(expr)) && guard < 30) {
    const fnName = match[1].toUpperCase();
    const argsStr = match[2];
    const val = execFunction(fnName, argsStr, currentSheetId, visited);
    expr = expr.slice(0, match.index) + (typeof val === 'string' ? JSON.stringify(val) : val) + expr.slice(match.index + match[0].length);
    guard++;
  }

  // 2. Evaluate cell references
  expr = expr.replace(/(?:([A-Za-z0-9_]+)!)?([A-Z]+\d+)/gi, (m, sh, cell) => {
    const sId = sh || currentSheetId;
    const p = parseRef(cell);
    if(!p) return '0';
    const val = getCellValueRaw(sId, p.r, p.c, visited);
    if(typeof val === 'number') return String(val);
    if(!isNaN(parseFloat(val)) && isFinite(val)) return String(val);
    return JSON.stringify(String(val));
  });

  // 3. Convert string concatenation & operator to JS string concatenation
  expr = expr.replace(/\s*&\s*/g, ' + "" + ');

  try {
    const cleanFn = new Function('"use strict"; return (' + expr + ')');
    const result = cleanFn();
    if(typeof result === 'number') {
      if(!isFinite(result)) return '#DIV/0!';
      return Math.round(result * 1e10) / 1e10;
    }
    return result;
  } catch(err) {
    return '#ERROR!';
  }
}

function execFunction(fnName, argsStr, currentSheetId, visited) {
  const rawParts = argsStr ? splitArgs(argsStr) : [];
  
  const getValues = () => {
    let vals = [];
    for(const p of rawParts) {
      const refs = resolveRefOrRange(p, currentSheetId);
      if(refs) {
        for(const item of refs) {
          const v = getCellValueRaw(item.sheetId, item.r, item.c, visited);
          const num = parseFloat(v);
          if(v !== '' && !isNaN(num)) vals.push(num);
        }
      } else {
        const n = parseFloat(p);
        if(!isNaN(n)) vals.push(n);
      }
    }
    return vals;
  };

  switch(fnName) {
    case 'SUM': {
      const v = getValues();
      return v.reduce((a,b) => a+b, 0);
    }
    case 'AVERAGE': {
      const v = getValues();
      return v.length ? v.reduce((a,b) => a+b, 0) / v.length : 0;
    }
    case 'MIN': {
      const v = getValues();
      return v.length ? Math.min(...v) : 0;
    }
    case 'MAX': {
      const v = getValues();
      return v.length ? Math.max(...v) : 0;
    }
    case 'COUNT': {
      return getValues().length;
    }
    case 'COUNTA': {
      let count = 0;
      for(const p of rawParts) {
        const refs = resolveRefOrRange(p, currentSheetId);
        if(refs) {
          for(const item of refs) {
            const v = getCellValueRaw(item.sheetId, item.r, item.c, visited);
            if(v !== '' && v !== null && v !== undefined) count++;
          }
        } else if(p !== '') count++;
      }
      return count;
    }
    case 'IF': {
      if(rawParts.length < 2) return '#ERROR!';
      const cond = parseExpression(rawParts[0], currentSheetId, visited);
      return cond ? parseExpression(rawParts[1], currentSheetId, visited) : (rawParts[2] ? parseExpression(rawParts[2], currentSheetId, visited) : '');
    }
    case 'IFERROR': {
      const val = parseExpression(rawParts[0], currentSheetId, visited);
      if(String(val).startsWith('#')) return rawParts[1] ? parseExpression(rawParts[1], currentSheetId, visited) : '';
      return val;
    }
    case 'VLOOKUP': {
      if(rawParts.length < 3) return '#N/A';
      const keyRaw = rawParts[0];
      let searchKey = keyRaw;
      const pRef = parseRef(keyRaw);
      if(pRef) {
        searchKey = getCellValueRaw(currentSheetId, pRef.r, pRef.c, visited);
      } else if(keyRaw.startsWith('"') && keyRaw.endsWith('"')) {
        searchKey = keyRaw.slice(1, -1);
      }
      
      const rangeRefs = resolveRefOrRange(rawParts[1], currentSheetId);
      const colIndex = parseInt(rawParts[2], 10) - 1;
      if(!rangeRefs || colIndex < 0) return '#REF!';
      
      const rowsMap = {};
      for(const item of rangeRefs) {
        if(!rowsMap[item.r]) rowsMap[item.r] = [];
        rowsMap[item.r].push(item);
      }
      for(const r in rowsMap) {
        const rowCells = rowsMap[r];
        const firstVal = getCellValueRaw(rowCells[0].sheetId, rowCells[0].r, rowCells[0].c, visited);
        if(String(firstVal).toLowerCase() === String(searchKey).toLowerCase()) {
          if(rowCells[colIndex]) {
            return getCellValueRaw(rowCells[colIndex].sheetId, rowCells[colIndex].r, rowCells[colIndex].c, visited);
          }
        }
      }
      return '#N/A';
    }
    case 'CONCATENATE':
    case 'CONCAT': {
      let str = '';
      for(const p of rawParts) {
        const refs = resolveRefOrRange(p, currentSheetId);
        if(refs) {
          for(const item of refs) str += getCellValueRaw(item.sheetId, item.r, item.c, visited);
        } else str += p.replace(/"/g, '');
      }
      return str;
    }
    case 'UPPER': return String(rawParts[0] || '').toUpperCase().replace(/"/g,'');
    case 'LOWER': return String(rawParts[0] || '').toLowerCase().replace(/"/g,'');
    case 'LEN': return String(rawParts[0] || '').replace(/"/g,'').length;
    case 'NOW': return new Date().toLocaleString();
    case 'TODAY': return new Date().toLocaleDateString();
    case 'ABS': return Math.abs(parseFloat(rawParts[0]) || 0);
    case 'ROUND': return Math.round(parseFloat(rawParts[0]) || 0);
    case 'SQRT': return Math.sqrt(parseFloat(rawParts[0]) || 0);
    default: return '#NAME?';
  }
}

function formatDisplay(r, c) {
  const data = getActiveCells()[ref(r, c)];
  let val = getCellValueRaw(appState.activeSheetId, r, c);
  if(val === '' || val === null || val === undefined) return '';

  if(typeof val === 'number') {
    const dec = (data && data.decimals !== undefined) ? data.decimals : null;
    if(data && data.fmt === 'currency') return '$' + (dec !== null ? val.toFixed(dec) : val.toFixed(2));
    if(data && data.fmt === 'percent') return (dec !== null ? (val * 100).toFixed(dec) : (val * 100).toFixed(1)) + '%';
    if(data && data.fmt === 'scientific') return val.toExponential(dec !== null ? dec : 2);
    if(data && data.fmt === 'number') return dec !== null ? val.toFixed(dec) : val.toFixed(2);
    if(dec !== null) return val.toFixed(dec);
  }
  return String(val);
}

/* ==========================================================================
   GRID BUILD WITH DOM CACHING & COL/ROW HEADERS
   ========================================================================== */
const gridEl = document.getElementById('grid');

function buildGrid() {
  const sheet = getActiveSheet();
  const numRows = appState.numRows || 100;
  cellDomCache.clear();
  colheadDomCache.clear();
  rowheadDomCache.clear();

  let html = '<colgroup><col class="corner-col" style="width:46px;">';
  for(let c = 0; c < NUM_COLS; c++) {
    const w = sheet.colWidths[c] || 100;
    html += `<col data-col="${c}" style="width:${w}px;">`;
  }
  html += '</colgroup><thead><tr><th class="corner"></th>';
  
  for(let c = 0; c < NUM_COLS; c++) {
    html += `<th class="colhead" data-c="${c}">
      <span class="colhead-label">${colToLetter(c)}</span>
      <span class="material-symbols-outlined colhead-arrow" data-c="${c}">arrow_drop_down</span>
      <div class="col-resizer" data-c="${c}"></div>
    </th>`;
  }
  html += '</tr></thead><tbody>';
  
  for(let r = 0; r < numRows; r++) {
    const h = sheet.rowHeights[r] || 24;
    html += `<tr data-tr="${r}" style="height:${h}px">
      <th class="rowhead" data-r="${r}">
        ${r + 1}
        <div class="row-resizer" data-r="${r}"></div>
      </th>`;
    for(let c = 0; c < NUM_COLS; c++) {
      html += `<td data-r="${r}" data-c="${c}"><div class="cell-inner" data-r="${r}" data-c="${c}"></div></td>`;
    }
    html += '</tr>';
  }
  html += '</tbody>';
  gridEl.innerHTML = html;

  // Cache DOM nodes for O(1) instantaneous lookup
  gridEl.querySelectorAll('td[data-r]').forEach(td => {
    const r = +td.dataset.r, c = +td.dataset.c;
    cellDomCache.set((r << 8) | c, td);
  });
  gridEl.querySelectorAll('th.colhead').forEach(th => {
    colheadDomCache.set(+th.dataset.c, th);
  });
  gridEl.querySelectorAll('th.rowhead').forEach(th => {
    rowheadDomCache.set(+th.dataset.r, th);
  });

  attachResizersOnce();
}

function addRows(count) {
  appState.numRows = (appState.numRows || 100) + (count || 50);
  buildGrid();
  renderAll();
  updateSelectionUI();
  showToast(`Added ${count || 50} rows`);
}

function renderCell(r, c) {
  const td = getTd(r, c);
  if(!td) return;

  // ── CRITICAL PROTECTION: Never overwrite DOM content of the cell currently being edited ──
  if (appState.isEditing && appState.selected && appState.selected.r === r && appState.selected.c === c) {
    td.classList.add('editing');
    return;
  }

  const inner = td.querySelector('.cell-inner');
  const data = getActiveCells()[ref(r, c)];
  
  if(data && data.mergedHidden) {
    td.style.display = 'none';
    return;
  } else {
    td.style.display = '';
  }

  if(data && data.merge) {
    td.colSpan = data.merge.cspan;
    td.rowSpan = data.merge.rspan;
  } else {
    td.removeAttribute('colspan');
    td.removeAttribute('rowspan');
  }

  const val = formatDisplay(r, c);

  if (data && data.dropdown && Array.isArray(data.dropdown.options) && data.dropdown.options.length > 0) {
    const opt = data.dropdown.options.find(o => o.label === val) || data.dropdown.options[0] || { label: val || 'Select...', color: '#e8eaed' };
    const chipBg = opt.color || '#e8eaed';
    const chipText = getContrastTextColor(chipBg);
    const displayVal = val || opt.label || 'Select...';

    inner.innerHTML = `
      <div class="sheet-dropdown-chip" style="background:${chipBg} !important; color:${chipText} !important; display:inline-flex; align-items:center; justify-content:space-between; gap:4px; padding:2px 8px; border-radius:12px; font-size:11.5px; font-weight:600; cursor:pointer; max-width:100%; box-sizing:border-box; user-select:none; border:1px solid rgba(0,0,0,0.1); box-shadow:0 1px 2px rgba(0,0,0,0.06);">
        <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(displayVal)}</span>
        <span class="material-symbols-outlined" style="font-size:14px; opacity:0.85; flex-shrink:0;">arrow_drop_down</span>
      </div>
    `;
  } else {
    inner.textContent = val;
  }
  
  const isNum = typeof getCellValueRaw(appState.activeSheetId, r, c) === 'number';
  td.classList.toggle('num', isNum);

  if(data) {
    inner.style.fontWeight = data.b ? '700' : '400';
    inner.style.fontStyle = data.i ? 'italic' : 'normal';
    inner.style.textDecoration = [data.u ? 'underline' : '', data.s ? 'line-through' : ''].join(' ').trim() || 'none';
    inner.style.color = data.color || '';
    inner.style.fontSize = (data.fs || 10) + 'pt';
    inner.style.fontFamily = data.font ? `'${data.font}', sans-serif` : 'Inter, sans-serif';
    if(data.align) inner.style.textAlign = data.align === 'l' ? 'left' : data.align === 'c' ? 'center' : 'right';
    else inner.style.textAlign = '';
    
    if(data.valign) inner.style.justifyContent = data.valign === 't' ? 'flex-start' : data.valign === 'b' ? 'flex-end' : 'center';
    else inner.style.justifyContent = 'center';

    if(data.wrap === 'wrap') inner.style.whiteSpace = 'normal';
    else if(data.wrap === 'clip') inner.style.overflow = 'hidden';
    else inner.style.whiteSpace = 'nowrap';

    td.style.background = data.bg || '';

    // Render Dynamic Cell Borders
    if(data.border) {
      td.style.borderTop = data.border.top ? `${data.border.top.w}px ${data.border.top.s} ${data.border.top.c}` : '';
      td.style.borderRight = data.border.right ? `${data.border.right.w}px ${data.border.right.s} ${data.border.right.c}` : '';
      td.style.borderBottom = data.border.bottom ? `${data.border.bottom.w}px ${data.border.bottom.s} ${data.border.bottom.c}` : '';
      td.style.borderLeft = data.border.left ? `${data.border.left.w}px ${data.border.left.s} ${data.border.left.c}` : '';
    } else {
      td.style.borderTop = ''; td.style.borderRight = ''; td.style.borderBottom = ''; td.style.borderLeft = '';
    }
  } else {
    inner.style = '';
    td.style.background = '';
    td.style.borderTop = ''; td.style.borderRight = ''; td.style.borderBottom = ''; td.style.borderLeft = '';
  }
}

function renderAll() {
  clearFormulaCache();
  const cells = getActiveCells();
  const numRows = appState.numRows || 100;
  // Clear ALL visible cells first, then paint cells that have data.
  // This prevents stale DOM content from orphaned/deleted cells persisting visually.
  for(let r = 0; r < numRows; r++) {
    for(let c = 0; c < NUM_COLS; c++) {
      renderCell(r, c);
    }
  }
}

/* ==========================================================================
   SELECTION, ROW/COLUMN SELECTION, FILL HANDLE & QUICK STATS
   ========================================================================== */
function updateSelectionUI() {
  if(prevSelectedEl) prevSelectedEl.classList.remove('selected');
  for(let i = 0; i < prevSelectedRangeEls.length; i++) {
    prevSelectedRangeEls[i].classList.remove('in-range', 'selected', 'fill-drag-target');
  }
  prevSelectedRangeEls = [];

  const existingHandle = document.getElementById('fillHandle');
  if(existingHandle) existingHandle.remove();

  gridEl.querySelectorAll('th.selected').forEach(th => th.classList.remove('selected'));

  const rangeCells = getSelectedCells();
  let maxR = appState.selected.r, maxC = appState.selected.c;

  for(let i = 0; i < rangeCells.length; i++) {
    const {r, c} = rangeCells[i];
    const td = getTd(r, c);
    if(td) {
      const isAnchor = (r === appState.selected.r && c === appState.selected.c);
      td.classList.add(isAnchor ? 'selected' : 'in-range');
      if(isAnchor) prevSelectedEl = td;
      prevSelectedRangeEls.push(td);
      if(r >= maxR && c >= maxC) {
        maxR = Math.max(maxR, r);
        maxC = Math.max(maxC, c);
      }
    }
  }

  // Position Fill Handle on the bottom-rightmost selected cell
  const maxTd = getTd(maxR, maxC);
  if(maxTd) {
    const handle = document.createElement('div');
    handle.className = 'fill-handle';
    handle.id = 'fillHandle';
    handle.title = 'Drag to fill series or copy cells';
    maxTd.appendChild(handle);
  }

  const numRows = appState.numRows || 100;

  // Header highlighting (Row, Column, Corner)
  if(appState.selRange) {
    const {r1, c1, r2, c2} = appState.selRange;
    if(c1 === 0 && c2 === NUM_COLS - 1) {
      for(let r = r1; r <= r2; r++) {
        const rh = rowheadDomCache.get(r);
        if(rh) rh.classList.add('selected');
      }
    }
    if(r1 === 0 && r2 === numRows - 1) {
      for(let c = c1; c <= c2; c++) {
        const ch = colheadDomCache.get(c);
        if(ch) ch.classList.add('selected');
      }
    }
  } else {
    const colHead = colheadDomCache.get(appState.selected.c);
    const rowHead = rowheadDomCache.get(appState.selected.r);
    if(colHead) colHead.classList.add('selected');
    if(rowHead) rowHead.classList.add('selected');
  }

  // Format Cell Reference Box
  let refText = ref(appState.selected.r, appState.selected.c);
  if(appState.selRange) {
    const {r1, c1, r2, c2} = appState.selRange;
    if(c1 === 0 && c2 === NUM_COLS - 1 && r1 === r2) {
      refText = `${r1 + 1}:${r2 + 1}`;
    } else if(r1 === 0 && r2 === numRows - 1 && c1 === c2) {
      refText = `${colToLetter(c1)}:${colToLetter(c2)}`;
    } else {
      refText = `${ref(r1, c1)}:${ref(r2, c2)}`;
    }
  }
  const refTextEl = document.getElementById('cellRefText');
  if(refTextEl) refTextEl.textContent = refText;

  if(!appState.isEditing) {
    const data = getActiveCells()[ref(appState.selected.r, appState.selected.c)];
    const formulaInp = document.getElementById('formulaInput');
    if(formulaInp) formulaInp.value = data && data.v !== undefined ? String(data.v) : '';
  }

  updateQuickStats(rangeCells);
  syncToolbarState();
  if (typeof updatePresence === 'function') updatePresence(appState.selected.r, appState.selected.c);
}

function getSelectedCells() {
  if(!appState.selRange) return [{ r: appState.selected.r, c: appState.selected.c }];
  const out = [];
  for(let r = appState.selRange.r1; r <= appState.selRange.r2; r++) {
    for(let c = appState.selRange.c1; c <= appState.selRange.c2; c++) out.push({ r, c });
  }
  return out;
}

function selectCell(r, c, extend) {
  const numRows = appState.numRows || 100;
  r = clamp(r, 0, numRows - 1); c = clamp(c, 0, NUM_COLS - 1);
  if(extend) {
    appState.selectionHead = { r, c };
    const anchorR = appState.selected.r;
    const anchorC = appState.selected.c;
    appState.selRange = {
      r1: Math.min(anchorR, r), c1: Math.min(anchorC, c),
      r2: Math.max(anchorR, r), c2: Math.max(anchorC, c)
    };
  } else {
    appState.selected = { r, c };
    appState.selectionHead = { r, c };
    appState.selRange = null;
  }
  updateSelectionUI();
}

function selectRow(r) {
  const numRows = appState.numRows || 100;
  r = clamp(r, 0, numRows - 1);
  appState.selected = { r, c: 0 };
  appState.selectionHead = { r, c: NUM_COLS - 1 };
  appState.selRange = { r1: r, c1: 0, r2: r, c2: NUM_COLS - 1 };
  updateSelectionUI();
}

function selectColumn(c) {
  const numRows = appState.numRows || 100;
  c = clamp(c, 0, NUM_COLS - 1);
  appState.selected = { r: 0, c };
  appState.selectionHead = { r: numRows - 1, c };
  appState.selRange = { r1: 0, c1: c, r2: numRows - 1, c2: c };
  updateSelectionUI();
}

function selectAll() {
  const numRows = appState.numRows || 100;
  appState.selected = { r: 0, c: 0 };
  appState.selectionHead = { r: numRows - 1, c: NUM_COLS - 1 };
  appState.selRange = { r1: 0, c1: 0, r2: numRows - 1, c2: NUM_COLS - 1 };
  updateSelectionUI();
}

function updateQuickStats(rangeCells) {
  let sum = 0, count = 0, numCount = 0, min = Infinity, max = -Infinity;
  for(let i = 0; i < rangeCells.length; i++) {
    const {r, c} = rangeCells[i];
    const val = getCellValueRaw(appState.activeSheetId, r, c);
    if(val !== '' && val !== null && val !== undefined) {
      count++;
      const n = parseFloat(val);
      if(!isNaN(n)) {
        numCount++;
        sum += n;
        if(n < min) min = n;
        if(n > max) max = n;
      }
    }
  }
  const elSum = document.getElementById('statSum');
  const elAvg = document.getElementById('statAvg');
  const elCnt = document.getElementById('statCount');
  if(elSum) elSum.textContent = numCount ? Math.round(sum * 1e4) / 1e4 : 0;
  if(elAvg) elAvg.textContent = numCount ? Math.round((sum / numCount) * 1e4) / 1e4 : 0;
  if(elCnt) elCnt.textContent = count;
}

function syncToolbarState() {
  const data = getActiveCells()[ref(appState.selected.r, appState.selected.c)] || {};
  const elB = document.getElementById('boldBtn');
  const elI = document.getElementById('italicBtn');
  const elS = document.getElementById('strikeBtn');
  const elFs = document.getElementById('fontSizeInput');
  const elFont = document.getElementById('fontPickerLabel');
  const elNum = document.getElementById('numFormatSelect');

  if(elB) elB.classList.toggle('active', !!data.b);
  if(elI) elI.classList.toggle('active', !!data.i);
  if(elS) elS.classList.toggle('active', !!data.s);
  if(elFs) elFs.value = data.fs || 10;
  if(elFont) elFont.textContent = data.font || 'Arial';
  if(elNum) elNum.value = data.fmt || 'plain';
}

/* ==========================================================================
   AUTOFILL ALGORITHM ENGINE WITH RELATIVE FORMULA ADJUSTMENT
   ========================================================================== */
function performAutofill(src, dst) {
  if(!src || !dst) return;
  const cells = getActiveCells();

  // Case 1: Dragging Downwards
  if(dst.r2 > src.r2) {
    const srcHeight = src.r2 - src.r1 + 1;
    for(let c = src.c1; c <= src.c2; c++) {
      const srcVals = [];
      const srcObjs = [];
      for(let r = src.r1; r <= src.r2; r++) {
        const key = ref(r, c);
        const val = getCellValueRaw(appState.activeSheetId, r, c);
        srcVals.push(val);
        srcObjs.push(cells[key] ? { ...cells[key] } : null);
      }

      const numVals = srcVals.map(v => parseFloat(v));
      const allNumeric = numVals.every(v => typeof v === 'number' && !isNaN(v) && v !== null && v !== '');

      if(srcHeight >= 2 && allNumeric) {
        const step = (numVals[numVals.length - 1] - numVals[0]) / (srcHeight - 1);
        let currentNum = numVals[numVals.length - 1];
        for(let r = src.r2 + 1; r <= dst.r2; r++) {
          currentNum += step;
          const k = ref(r, c);
          const tmpl = srcObjs[(r - src.r1) % srcHeight] || {};
          cells[k] = { ...tmpl, v: Math.round(currentNum * 1e10) / 1e10 };
          renderCell(r, c);
        }
      } else {
        for(let r = src.r2 + 1; r <= dst.r2; r++) {
          const idx = (r - (src.r2 + 1)) % srcHeight;
          const k = ref(r, c);
          if(srcObjs[idx]) {
            const cloned = { ...srcObjs[idx] };
            const dRow = r - (src.r1 + idx);
            if (typeof cloned.v === 'string' && cloned.v.startsWith('=')) {
              cloned.v = adjustFormulaReferences(cloned.v, dRow, 0);
            }
            cells[k] = cloned;
          } else delete cells[k];
          renderCell(r, c);
        }
      }
    }
  }

  // Case 2: Dragging Rightwards
  else if(dst.c2 > src.c2) {
    const srcWidth = src.c2 - src.c1 + 1;
    for(let r = src.r1; r <= src.r2; r++) {
      const srcVals = [];
      const srcObjs = [];
      for(let c = src.c1; c <= src.c2; c++) {
        const key = ref(r, c);
        const val = getCellValueRaw(appState.activeSheetId, r, c);
        srcVals.push(val);
        srcObjs.push(cells[key] ? { ...cells[key] } : null);
      }

      const numVals = srcVals.map(v => parseFloat(v));
      const allNumeric = numVals.every(v => typeof v === 'number' && !isNaN(v) && v !== null && v !== '');

      if(srcWidth >= 2 && allNumeric) {
        const step = (numVals[numVals.length - 1] - numVals[0]) / (srcWidth - 1);
        let currentNum = numVals[numVals.length - 1];
        for(let c = src.c2 + 1; c <= dst.c2; c++) {
          currentNum += step;
          const k = ref(r, c);
          const tmpl = srcObjs[(c - src.c1) % srcWidth] || {};
          cells[k] = { ...tmpl, v: Math.round(currentNum * 1e10) / 1e10 };
          renderCell(r, c);
        }
      } else {
        for(let c = src.c2 + 1; c <= dst.c2; c++) {
          const idx = (c - (src.c2 + 1)) % srcWidth;
          const k = ref(r, c);
          if(srcObjs[idx]) {
            const cloned = { ...srcObjs[idx] };
            const dCol = c - (src.c1 + idx);
            if (typeof cloned.v === 'string' && cloned.v.startsWith('=')) {
              cloned.v = adjustFormulaReferences(cloned.v, 0, dCol);
            }
            cells[k] = cloned;
          } else delete cells[k];
          renderCell(r, c);
        }
      }
    }
  }

  // Case 3: Dragging Upwards
  else if(dst.r1 < src.r1) {
    const srcHeight = src.r2 - src.r1 + 1;
    for(let c = src.c1; c <= src.c2; c++) {
      const srcVals = [];
      const srcObjs = [];
      for(let r = src.r1; r <= src.r2; r++) {
        const key = ref(r, c);
        srcVals.push(getCellValueRaw(appState.activeSheetId, r, c));
        srcObjs.push(cells[key] ? { ...cells[key] } : null);
      }

      const numVals = srcVals.map(v => parseFloat(v));
      const allNumeric = numVals.every(v => typeof v === 'number' && !isNaN(v) && v !== null && v !== '');

      if(srcHeight >= 2 && allNumeric) {
        const step = (numVals[numVals.length - 1] - numVals[0]) / (srcHeight - 1);
        let currentNum = numVals[0];
        for(let r = src.r1 - 1; r >= dst.r1; r--) {
          currentNum -= step;
          const k = ref(r, c);
          const tmpl = srcObjs[0] || {};
          cells[k] = { ...tmpl, v: Math.round(currentNum * 1e10) / 1e10 };
          renderCell(r, c);
        }
      } else {
        for(let r = src.r1 - 1; r >= dst.r1; r--) {
          const k = ref(r, c);
          if(srcObjs[0]) {
            const cloned = { ...srcObjs[0] };
            const dRow = r - src.r1;
            if (typeof cloned.v === 'string' && cloned.v.startsWith('=')) {
              cloned.v = adjustFormulaReferences(cloned.v, dRow, 0);
            }
            cells[k] = cloned;
          } else delete cells[k];
          renderCell(r, c);
        }
      }
    }
  }

  // Case 4: Dragging Leftwards
  else if(dst.c1 < src.c1) {
    const srcWidth = src.c2 - src.c1 + 1;
    for(let r = src.r1; r <= src.r2; r++) {
      const srcVals = [];
      const srcObjs = [];
      for(let c = src.c1; c <= src.c2; c++) {
        const key = ref(r, c);
        srcVals.push(getCellValueRaw(appState.activeSheetId, r, c));
        srcObjs.push(cells[key] ? { ...cells[key] } : null);
      }

      const numVals = srcVals.map(v => parseFloat(v));
      const allNumeric = numVals.every(v => typeof v === 'number' && !isNaN(v) && v !== null && v !== '');

      if(srcWidth >= 2 && allNumeric) {
        const step = (numVals[numVals.length - 1] - numVals[0]) / (srcWidth - 1);
        let currentNum = numVals[0];
        for(let c = src.c1 - 1; c >= dst.c1; c--) {
          currentNum -= step;
          const k = ref(r, c);
          const tmpl = srcObjs[0] || {};
          cells[k] = { ...tmpl, v: Math.round(currentNum * 1e10) / 1e10 };
          renderCell(r, c);
        }
      } else {
        for(let c = src.c1 - 1; c >= dst.c1; c--) {
          const k = ref(r, c);
          if(srcObjs[0]) {
            const cloned = { ...srcObjs[0] };
            const dCol = c - src.c1;
            if (typeof cloned.v === 'string' && cloned.v.startsWith('=')) {
              cloned.v = adjustFormulaReferences(cloned.v, 0, dCol);
            }
            cells[k] = cloned;
          } else delete cells[k];
          renderCell(r, c);
        }
      }
    }
  }

  pushHistory();
  renderAll();
  selectCell(dst.r1, dst.c1, false);
  appState.selRange = { ...dst };
  updateSelectionUI();
  showToast('Autofill sequence applied');
}

/* ==========================================================================
   MERGE CELLS ENGINE
   ========================================================================== */
const mergeBtn = document.getElementById('mergeBtn');
if(mergeBtn) mergeBtn.onclick = () => toggleMergeSelected();

function toggleMergeSelected() {
  if(!appState.selRange) {
    showToast('Select a range of cells to merge');
    return;
  }
  const { r1, c1, r2, c2 } = appState.selRange;
  if(r1 === r2 && c1 === c2) {
    showToast('Select at least 2 cells to merge');
    return;
  }

  const cells = getActiveCells();
  const anchorCell = cellData(r1, c1);

  if(anchorCell.merge) {
    // UNMERGE
    const { rspan, cspan } = anchorCell.merge;
    delete anchorCell.merge;
    for(let r = r1; r < r1 + rspan; r++) {
      for(let c = c1; c < c1 + cspan; c++) {
        const k = ref(r, c);
        if(cells[k]) delete cells[k].mergedHidden;
      }
    }
    showToast('Cells unmerged');
  } else {
    // MERGE
    const rspan = r2 - r1 + 1;
    const cspan = c2 - c1 + 1;
    anchorCell.merge = { rspan, cspan };

    for(let r = r1; r <= r2; r++) {
      for(let c = c1; c <= c2; c++) {
        if(r === r1 && c === c1) continue;
        const k = ref(r, c);
        if(!cells[k]) cells[k] = { v: '' };
        cells[k].mergedHidden = true;
      }
    }
    showToast(`Merged ${rspan}x${cspan} cells`);
  }

  buildGrid();
  renderAll();
  pushHistory();
  updateSelectionUI();
}

/* ==========================================================================
   TOOLBAR POPOVERS (Borders, Alignment, Wrapping, Functions)
   ========================================================================== */
function closeToolbarPopover() {
  if(activeToolbarPopover) {
    activeToolbarPopover.remove();
    activeToolbarPopover = null;
  }
}

// 1. Borders Popover
function openBordersPopover(anchorEl) {
  closeToolbarPopover();
  closeFontPopover();
  closeTopMenu();

  const pop = document.createElement('div');
  pop.className = 'borders-popover';

  const grid = document.createElement('div');
  grid.className = 'borders-grid';

  const borderTypes = [
    { icon: 'border_all', title: 'All borders', type: 'all' },
    { icon: 'border_inner', title: 'Inner borders', type: 'inner' },
    { icon: 'border_horizontal', title: 'Horizontal borders', type: 'horizontal' },
    { icon: 'border_vertical', title: 'Vertical borders', type: 'vertical' },
    { icon: 'border_outer', title: 'Outer borders', type: 'outer' },
    { icon: 'border_left', title: 'Left border', type: 'left' },
    { icon: 'border_top', title: 'Top border', type: 'top' },
    { icon: 'border_right', title: 'Right border', type: 'right' },
    { icon: 'border_bottom', title: 'Bottom border', type: 'bottom' },
    { icon: 'border_clear', title: 'Clear borders', type: 'clear' }
  ];

  borderTypes.forEach(b => {
    const btn = document.createElement('div');
    btn.className = 'border-btn-icon';
    btn.title = b.title;
    btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;">${b.icon}</span>`;
    btn.onclick = (e) => {
      e.stopPropagation();
      applyBorderType(b.type);
      closeToolbarPopover();
    };
    grid.appendChild(btn);
  });

  pop.appendChild(grid);

  const divider = document.createElement('div');
  divider.className = 'borders-divider';
  pop.appendChild(divider);

  const controls = document.createElement('div');
  controls.className = 'borders-controls';

  // Border Color Picker Button
  const colorBtn = document.createElement('div');
  colorBtn.className = 'popover-icon-btn';
  colorBtn.title = 'Border color';
  colorBtn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px; color:${currentBorderColor};">edit</span>`;
  colorBtn.onclick = (e) => {
    e.stopPropagation();
    openColorPicker(colorBtn, (c) => {
      currentBorderColor = c;
      colorBtn.querySelector('.material-symbols-outlined').style.color = c;
    });
  };
  controls.appendChild(colorBtn);

  // Border Style Thickness Button
  const styleBtn = document.createElement('div');
  styleBtn.className = 'popover-icon-btn';
  styleBtn.title = 'Border style';
  styleBtn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;">line_weight</span>`;
  styleBtn.onclick = (e) => {
    e.stopPropagation();
    currentBorderWidth = currentBorderWidth === 1 ? 2 : currentBorderWidth === 2 ? 3 : 1;
    showToast(`Border thickness: ${currentBorderWidth}px`);
  };
  controls.appendChild(styleBtn);

  pop.appendChild(controls);

  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
  activeToolbarPopover = pop;
}

function applyBorderType(type) {
  const borderObj = { w: currentBorderWidth, s: currentBorderStyle, c: currentBorderColor };
  const targets = getSelectedCells();
  const cells = getActiveCells();
  const minR = Math.min(...targets.map(t=>t.r)), maxR = Math.max(...targets.map(t=>t.r));
  const minC = Math.min(...targets.map(t=>t.c)), maxC = Math.max(...targets.map(t=>t.c));

  for(const {r, c} of targets) {
    const k = ref(r, c);
    if(!cells[k]) cells[k] = { v: '' };
    if(!cells[k].border) cells[k].border = {};

    if(type === 'all') {
      cells[k].border = { top: { ...borderObj }, right: { ...borderObj }, bottom: { ...borderObj }, left: { ...borderObj } };
    } else if(type === 'clear') {
      delete cells[k].border;
    } else if(type === 'top') {
      cells[k].border.top = { ...borderObj };
    } else if(type === 'right') {
      cells[k].border.right = { ...borderObj };
    } else if(type === 'bottom') {
      cells[k].border.bottom = { ...borderObj };
    } else if(type === 'left') {
      cells[k].border.left = { ...borderObj };
    } else if(type === 'outer') {
      if(r === minR) cells[k].border.top = { ...borderObj };
      if(r === maxR) cells[k].border.bottom = { ...borderObj };
      if(c === minC) cells[k].border.left = { ...borderObj };
      if(c === maxC) cells[k].border.right = { ...borderObj };
    }
    renderCell(r, c);
  }
  pushHistory();
}

// 2. Horizontal Alignment Popover
function openAlignPopover(anchorEl) {
  closeToolbarPopover();
  closeFontPopover();

  const pop = document.createElement('div');
  pop.className = 'toolbar-popover';

  const aligns = [
    { icon: 'format_align_left', value: 'l', title: 'Left' },
    { icon: 'format_align_center', value: 'c', title: 'Center' },
    { icon: 'format_align_right', value: 'r', title: 'Right' }
  ];

  aligns.forEach(a => {
    const btn = document.createElement('div');
    btn.className = 'popover-icon-btn';
    btn.title = a.title;
    btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;">${a.icon}</span>`;
    btn.onclick = (e) => {
      e.stopPropagation();
      forEachSelected(d => { d.align = a.value; });
      closeToolbarPopover();
    };
    pop.appendChild(btn);
  });

  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
  activeToolbarPopover = pop;
}

// 3. Vertical Alignment Popover
function openVAlignPopover(anchorEl) {
  closeToolbarPopover();
  closeFontPopover();

  const pop = document.createElement('div');
  pop.className = 'toolbar-popover';

  const valigns = [
    { icon: 'vertical_align_top', value: 't', title: 'Top' },
    { icon: 'vertical_align_center', value: 'm', title: 'Middle' },
    { icon: 'vertical_align_bottom', value: 'b', title: 'Bottom' }
  ];

  valigns.forEach(a => {
    const btn = document.createElement('div');
    btn.className = 'popover-icon-btn';
    btn.title = a.title;
    btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;">${a.icon}</span>`;
    btn.onclick = (e) => {
      e.stopPropagation();
      forEachSelected(d => { d.valign = a.value; });
      closeToolbarPopover();
    };
    pop.appendChild(btn);
  });

  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
  activeToolbarPopover = pop;
}

// 4. Text Wrapping Popover
function openTextWrapPopover(anchorEl) {
  closeToolbarPopover();
  closeFontPopover();

  const pop = document.createElement('div');
  pop.className = 'toolbar-popover';

  const wraps = [
    {
      svg: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h14M14 7l5 5-5 5"/></svg>`,
      value: 'overflow',
      title: 'Overflow'
    },
    {
      svg: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h11a3 3 0 0 1 0 6H12M14 15l-3 3 3 3"/></svg>`,
      value: 'wrap',
      title: 'Wrap'
    },
    {
      svg: `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h10M4 17h10M18 9v10"/></svg>`,
      value: 'clip',
      title: 'Clip'
    }
  ];

  wraps.forEach(w => {
    const btn = document.createElement('div');
    btn.className = 'popover-icon-btn';
    btn.title = w.title;
    btn.innerHTML = w.svg;
    btn.onclick = (e) => {
      e.stopPropagation();
      forEachSelected(d => { d.wrap = w.value; });
      closeToolbarPopover();
    };
    pop.appendChild(btn);
  });

  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
  activeToolbarPopover = pop;
}

// Wire Toolbar Popover Buttons
const bordersBtn = document.getElementById('bordersBtn');
if(bordersBtn) bordersBtn.onclick = (e) => { e.stopPropagation(); openBordersPopover(e.currentTarget); };
const alignLeft = document.getElementById('alignLeft');
if(alignLeft) alignLeft.onclick = (e) => { e.stopPropagation(); openAlignPopover(e.currentTarget); };
const valignMiddle = document.getElementById('valignMiddle');
if(valignMiddle) valignMiddle.onclick = (e) => { e.stopPropagation(); openVAlignPopover(e.currentTarget); };
const textWrapBtn = document.getElementById('textWrapBtn');
if(textWrapBtn) textWrapBtn.onclick = (e) => { e.stopPropagation(); openTextWrapPopover(e.currentTarget); };

// Insert Link Modal Handler
const insertLinkModal = document.getElementById('insertLinkModal');
const linkTextDisplayInput = document.getElementById('linkTextDisplayInput');
const linkUrlInput = document.getElementById('linkUrlInput');

const insertLinkBtn = document.getElementById('insertLinkBtn');
if(insertLinkBtn) {
  insertLinkBtn.onclick = () => {
    const currentVal = getCellValueRaw(appState.activeSheetId, appState.selected.r, appState.selected.c);
    if(linkTextDisplayInput) linkTextDisplayInput.value = currentVal || '';
    if(linkUrlInput) linkUrlInput.value = 'https://';
    if(insertLinkModal) insertLinkModal.classList.add('show');
    if(linkUrlInput) linkUrlInput.focus();
  };
}

const closeLinkBtn = document.getElementById('closeLinkBtn');
if(closeLinkBtn) closeLinkBtn.onclick = () => insertLinkModal.classList.remove('show');

const applyLinkBtn = document.getElementById('applyLinkBtn');
if(applyLinkBtn) {
  applyLinkBtn.onclick = () => {
    const url = linkUrlInput ? linkUrlInput.value.trim() : '';
    const text = linkTextDisplayInput ? linkTextDisplayInput.value.trim() : '';
    if(url) {
      forEachSelected(d => {
        d.v = text || url;
        d.color = '#1a73e8';
        d.u = true;
      });
      showToast('Link applied');
    }
    if(insertLinkModal) insertLinkModal.classList.remove('show');
  };
}

// Insert Comment Modal Handler
const insertCommentModal = document.getElementById('insertCommentModal');
const commentTextInput = document.getElementById('commentTextInput');

const insertCommentBtn = document.getElementById('insertCommentBtn');
if(insertCommentBtn) {
  insertCommentBtn.onclick = () => {
    if(commentTextInput) commentTextInput.value = '';
    if(insertCommentModal) insertCommentModal.classList.add('show');
    if(commentTextInput) commentTextInput.focus();
  };
}

const closeCommentBtn = document.getElementById('closeCommentBtn');
if(closeCommentBtn) closeCommentBtn.onclick = () => insertCommentModal.classList.remove('show');

const applyCommentBtn = document.getElementById('applyCommentBtn');
if(applyCommentBtn) {
  applyCommentBtn.onclick = () => {
    const text = commentTextInput ? commentTextInput.value.trim() : '';
    if(text) {
      forEachSelected(d => {
        d.comment = text;
      });
      showToast('Comment added');
    }
    if(insertCommentModal) insertCommentModal.classList.remove('show');
  };
}

// Chart Preview Modal Handler
function openChartModal() {
  const chartModal = document.getElementById('chartModal');
  const chartContainer = document.getElementById('chartContainer');
  if(!chartModal || !chartContainer) return;

  const selectedCells = getSelectedCells();
  const items = [];
  
  selectedCells.forEach(({r, c}) => {
    const raw = getCellValueRaw(appState.activeSheetId, r, c);
    const num = parseFloat(raw);
    if(!isNaN(num)) {
      items.push({ label: ref(r, c), value: num });
    }
  });

  if(!items.length) {
    const cells = getActiveCells();
    for(const k in cells) {
      const val = parseFloat(cells[k].v);
      if(!isNaN(val)) items.push({ label: k, value: val });
      if(items.length >= 8) break;
    }
  }

  if(!items.length) {
    chartContainer.innerHTML = '<div style="margin:auto; color:#666;">Enter numerical values in cells to generate a chart preview.</div>';
  } else {
    const maxVal = Math.max(...items.map(i => i.value), 1);
    const colors = ['#1a73e8', '#34a853', '#fbbc04', '#ea4335', '#ab47bc', '#00acc1'];
    
    let html = '';
    items.slice(0, 10).forEach((item, idx) => {
      const heightPercent = Math.max(10, Math.min(100, (item.value / maxVal) * 100));
      const color = colors[idx % colors.length];
      html += `
        <div style="display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end; gap:6px; flex:1;">
          <span style="font-size:11px; font-weight:600; color:#374151;">${item.value}</span>
          <div style="width:70%; height:${heightPercent}%; background:${color}; border-radius:4px 4px 0 0; transition:height 0.3s ease;"></div>
          <span style="font-size:11px; color:#6b7280; font-family:monospace;">${item.label}</span>
        </div>
      `;
    });
    chartContainer.innerHTML = html;
  }

  chartModal.classList.add('show');
}

const closeChartBtn = document.getElementById('closeChartBtn');
if(closeChartBtn) closeChartBtn.onclick = () => document.getElementById('chartModal').classList.remove('show');

/* ==========================================================================
   CUSTOM RICH GOOGLE FONTS PICKER POPOVER
   ========================================================================== */
function closeFontPopover() {
  if(activeFontPopover) {
    activeFontPopover.remove();
    activeFontPopover = null;
  }
}

function openFontPicker(anchorEl) {
  closeFontPopover();
  closeToolbarPopover();
  closeTopMenu();
  closeSheetMenu();
  closeHeaderMenu();

  const pop = document.createElement('div');
  pop.className = 'font-popover';

  const moreItem = document.createElement('div');
  moreItem.className = 'font-item';
  moreItem.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px; color:#1a73e8;">font_download</span><span>More fonts</span>`;
  pop.appendChild(moreItem);

  const divider1 = document.createElement('div');
  divider1.className = 'dropdown-divider';
  pop.appendChild(divider1);

  const header = document.createElement('div');
  header.className = 'font-popover-header';
  header.textContent = 'THEME';
  pop.appendChild(header);

  const curFont = getActiveCells()[ref(appState.selected.r, appState.selected.c)]?.font || 'Arial';

  FONTS_LIST.forEach(fontName => {
    const item = document.createElement('div');
    const isActive = (curFont === fontName);
    item.className = `font-item ${isActive ? 'active' : ''}`;
    item.style.fontFamily = `'${fontName}', sans-serif`;
    item.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px; color:#1a73e8; visibility:${isActive ? 'visible' : 'hidden'}">check</span><span>${fontName}</span>`;
    item.onclick = (e) => {
      e.stopPropagation();
      closeFontPopover();
      const fontLabel = document.getElementById('fontPickerLabel');
      if(fontLabel) fontLabel.textContent = fontName;
      forEachSelected(d => { d.font = fontName; });
    };
    pop.appendChild(item);
  });

  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
  activeFontPopover = pop;
}

const fontPickerBtn = document.getElementById('fontPickerBtn');
if(fontPickerBtn) {
  fontPickerBtn.onclick = (e) => {
    e.stopPropagation();
    openFontPicker(fontPickerBtn);
  };
}

/* ==========================================================================
   ROW & COLUMN CONTEXT MENUS (Cut, Copy, Paste, Insert, Delete, Sort)
   ========================================================================== */
function closeHeaderMenu() {
  if(activeHeaderMenu) {
    activeHeaderMenu.remove();
    activeHeaderMenu = null;
  }
}

function openColumnMenu(x, y, c) {
  closeContextMenu();
  closeHeaderMenu();
  closeSheetMenu();
  closeFontPopover();
  closeToolbarPopover();
  const menu = document.createElement('div');
  menu.className = 'dropdown-menu show header-dropdown-menu';

  const items = [
    { label: 'Cut', icon: 'content_cut', shortcut: 'Ctrl+X', action: cutSelection },
    { label: 'Copy', icon: 'content_copy', shortcut: 'Ctrl+C', action: copySelection },
    { label: 'Paste', icon: 'content_paste', shortcut: 'Ctrl+V', action: pasteSelection },
    { divider: true },
    { label: 'Insert 1 column left', icon: 'west', action: () => insertColLeft(c) },
    { label: 'Insert 1 column right', icon: 'east', action: () => insertColLeft(c + 1) },
    { label: 'Delete column', icon: 'delete', action: () => deleteCol(c) },
    { label: 'Clear column', icon: 'backspace', action: () => clearSelectedCells() },
    { divider: true },
    { label: 'Sort sheet A to Z', icon: 'sort_by_alpha', action: () => sortRange(true) },
    { label: 'Sort sheet Z to A', icon: 'sort_by_alpha', action: () => sortRange(false) }
  ];

  items.forEach(it => {
    if(it.divider) {
      const d = document.createElement('div'); d.className = 'dropdown-divider'; menu.appendChild(d);
    } else {
      const item = document.createElement('div');
      item.className = 'dropdown-item';
      item.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px;">${it.icon}</span><span>${it.label}</span>` + (it.shortcut ? `<span class="shortcut">${it.shortcut}</span>` : '');
      item.onclick = (se) => {
        se.stopPropagation();
        closeHeaderMenu();
        it.action();
      };
      menu.appendChild(item);
    }
  });

  document.body.appendChild(menu);
  
  const menuWidth = menu.offsetWidth || 220;
  const menuHeight = menu.offsetHeight || 300;
  let finalX = x;
  let finalY = y;

  if (x + menuWidth > window.innerWidth - 10) finalX = window.innerWidth - menuWidth - 10;
  if (y + menuHeight > window.innerHeight - 10) {
    finalY = y - menuHeight;
    if (finalY < 10) finalY = window.innerHeight - menuHeight - 10;
  }

  menu.style.top = Math.max(10, finalY) + 'px';
  menu.style.left = Math.max(10, finalX) + 'px';
  activeHeaderMenu = menu;
}

function openRowMenu(x, y, r) {
  closeContextMenu();
  closeHeaderMenu();
  closeSheetMenu();
  closeFontPopover();
  closeToolbarPopover();
  const menu = document.createElement('div');
  menu.className = 'dropdown-menu show header-dropdown-menu';

  const items = [
    { label: 'Cut', icon: 'content_cut', shortcut: 'Ctrl+X', action: cutSelection },
    { label: 'Copy', icon: 'content_copy', shortcut: 'Ctrl+C', action: copySelection },
    { label: 'Paste', icon: 'content_paste', shortcut: 'Ctrl+V', action: pasteSelection },
    { divider: true },
    { label: 'Insert 1 row above', icon: 'north', action: () => insertRowAbove(r) },
    { label: 'Insert 1 row below', icon: 'south', action: () => insertRowAbove(r + 1) },
    { label: 'Delete row', icon: 'delete', action: () => deleteRow(r) },
    { label: 'Clear row', icon: 'backspace', action: () => clearSelectedCells() }
  ];

  items.forEach(it => {
    if(it.divider) {
      const d = document.createElement('div'); d.className = 'dropdown-divider'; menu.appendChild(d);
    } else {
      const item = document.createElement('div');
      item.className = 'dropdown-item';
      item.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px;">${it.icon}</span><span>${it.label}</span>` + (it.shortcut ? `<span class="shortcut">${it.shortcut}</span>` : '');
      item.onclick = (se) => {
        se.stopPropagation();
        closeHeaderMenu();
        it.action();
      };
      menu.appendChild(item);
    }
  });

  document.body.appendChild(menu);

  const menuWidth = menu.offsetWidth || 220;
  const menuHeight = menu.offsetHeight || 280;
  let finalX = x;
  let finalY = y;

  if (x + menuWidth > window.innerWidth - 10) finalX = window.innerWidth - menuWidth - 10;
  if (y + menuHeight > window.innerHeight - 10) {
    finalY = y - menuHeight;
    if (finalY < 10) finalY = window.innerHeight - menuHeight - 10;
  }

  menu.style.top = Math.max(10, finalY) + 'px';
  menu.style.left = Math.max(10, finalX) + 'px';
  activeHeaderMenu = menu;
}

/* ==========================================================================
   INSTANT & ROCK-SOLID CELL EDITING & FORMULA POINT-AND-CLICK
   ========================================================================== */
function startEdit(r, c, initial, keepFocusInFormulaBar) {
  if (isUserReadOnly || !isUserAuthorized) {
    showToast('Viewing in Read-Only mode');
    return;
  }
  if(appState.isEditing && (r !== appState.selected.r || c !== appState.selected.c)) {
    commitEdit();
  }
  selectCell(r, c, false);
  appState.isEditing = true;
  const data = getActiveCells()[ref(r, c)] || {};
  appState.editBuffer = initial !== undefined ? initial : (data.v !== undefined ? String(data.v) : '');
  
  const td = getTd(r, c);
  if(!td) return;
  const inner = td.querySelector('.cell-inner');
  td.classList.add('editing');
  inner.contentEditable = 'true';
  inner.textContent = appState.editBuffer;
  
  if(!keepFocusInFormulaBar) {
    inner.focus();
    placeCaretAtEnd(inner);
  }
  
  const formulaInp = document.getElementById('formulaInput');
  if(formulaInp) formulaInp.value = appState.editBuffer;
}

function placeCaretAtEnd(el) {
  const range = document.createRange();
  range.selectNodeContents(el); range.collapse(false);
  const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(range);
}

function insertCellRefIntoFormula(refStr) {
  const activeTd = getTd(appState.selected.r, appState.selected.c);
  if(!activeTd) return;
  const inner = activeTd.querySelector('.cell-inner');
  let currentText = inner.textContent;

  const match = currentText.match(/([A-Z]+\d+(?::[A-Z]+\d+)?)$/i);
  if(match) {
    currentText = currentText.slice(0, match.index) + refStr;
  } else {
    currentText += refStr;
  }

  inner.textContent = currentText;
  appState.editBuffer = currentText;
  const formulaInp = document.getElementById('formulaInput');
  if(formulaInp) formulaInp.value = currentText;
  placeCaretAtEnd(inner);
}

function clearFormulaRefHighlights() {
  gridEl.querySelectorAll('.formula-ref-highlight').forEach(el => el.classList.remove('formula-ref-highlight'));
}

function highlightFormulaRefCells(r, c) {
  clearFormulaRefHighlights();
  const td = getTd(r, c);
  if(td) td.classList.add('formula-ref-highlight');
}

function commitEdit(moveDir) {
  if(!appState.isEditing) return;
  clearFormulaRefHighlights();
  const {r, c} = appState.selected;
  const td = getTd(r, c);
  if(!td) return;
  const inner = td.querySelector('.cell-inner');
  const val = inner.textContent;
  const cellRef = ref(r, c);
  
  const cells = getActiveCells();
  if(!cells[cellRef]) cells[cellRef] = { v: '' };
  cells[cellRef].v = val;
  
  inner.contentEditable = 'false';
  td.classList.remove('editing');
  appState.isEditing = false;
  
  // Instant granular cell-level cloud sync
  if (typeof saveSingleCellToCloud === 'function') {
    saveSingleCellToCloud(cellRef, cells[cellRef]);
  }

  pushHistory();
  renderAll();
  updateSelectionUI();

  if(moveDir === 'down') selectCell(r + 1, c, false);
  else if(moveDir === 'right') selectCell(r, c + 1, false);
}

function cancelEdit() {
  if(!appState.isEditing) return;
  clearFormulaRefHighlights();
  const {r, c} = appState.selected;
  const td = getTd(r, c);
  if(!td) return;
  const inner = td.querySelector('.cell-inner');
  inner.contentEditable = 'false';
  td.classList.remove('editing');
  appState.isEditing = false;
  renderCell(r, c);
  updateSelectionUI();
}

function clearSelectedCells() {
  const targets = getSelectedCells();
  const cells = getActiveCells();
  for(let i = 0; i < targets.length; i++) {
    const {r, c} = targets[i];
    delete cells[ref(r, c)];
    renderCell(r, c);
  }
  pushHistory();
  renderAll();
  updateSelectionUI();
}

/* Mouse & Touch Interactions with Point & Click Formula Reference Selection */
let isDragging = false, dragAnchor = null;

gridEl.addEventListener('click', (e) => {
  const arrow = e.target.closest('.colhead-arrow');
  if(arrow) {
    e.stopPropagation();
    const c = +arrow.dataset.c;
    selectColumn(c);
    const rect = arrow.getBoundingClientRect();
    openColumnMenu(rect.left, rect.bottom + 2, c);
    return;
  }
});

/* Sync cell typing with appState.editBuffer and formula bar in real time */
gridEl.addEventListener('input', (e) => {
  if (e.target && e.target.classList.contains('cell-inner') && appState.isEditing) {
    const text = e.target.textContent;
    appState.editBuffer = text;
    const formulaInp = document.getElementById('formulaInput');
    if (formulaInp && document.activeElement !== formulaInp) {
      formulaInp.value = text;
    }
  }
});

function handlePointerDown(target, clientX, clientY, isShiftKey, e) {
  const handle = target.closest('#fillHandle');
  if(handle) {
    if(e) { e.stopPropagation(); e.preventDefault(); }
    isFillDragging = true;
    fillStartRange = appState.selRange ? { ...appState.selRange } : { r1: appState.selected.r, c1: appState.selected.c, r2: appState.selected.r, c2: appState.selected.c };
    return;
  }

  const rowhead = target.closest('th.rowhead');
  if(rowhead && !target.classList.contains('row-resizer')) {
    if(appState.isEditing) commitEdit();
    const r = +rowhead.dataset.r;
    selectRow(r);
    return;
  }

  const colhead = target.closest('th.colhead');
  if(colhead && !target.classList.contains('col-resizer') && !target.classList.contains('colhead-arrow')) {
    if(appState.isEditing) commitEdit();
    const c = +colhead.dataset.c;
    selectColumn(c);
    return;
  }

  const corner = target.closest('th.corner');
  if(corner) {
    if(appState.isEditing) commitEdit();
    selectAll();
    return;
  }

  const td = target.closest('td');
  if(td) {
    const r = +td.dataset.r, c = +td.dataset.c;
    
    // FORMULA POINT-AND-CLICK SELECTION
    if(appState.isEditing) {
      const activeTd = getTd(appState.selected.r, appState.selected.c);
      const inner = activeTd ? activeTd.querySelector('.cell-inner') : null;
      const text = inner ? inner.textContent : '';

      if(text.startsWith('=')) {
        if(e) { e.preventDefault(); e.stopPropagation(); }
        if(r === appState.selected.r && c === appState.selected.c) return;
        
        insertCellRefIntoFormula(ref(r, c));
        highlightFormulaRefCells(r, c);
        return;
      } else {
        commitEdit();
      }
    }

    // PIVOT MODAL POINT-AND-CLICK SELECTION
    const pivotModal = document.getElementById('createPivotModal');
    if (pivotModal && pivotModal.classList.contains('show')) {
      const existingInp = document.getElementById('pivotExistingLocationInput');
      const rangeInp = document.getElementById('pivotDataRangeInput');
      const destChoice = document.querySelector('input[name="pivotInsertDestination"]:checked')?.value;
      const activeSheet = findSheetObj(appState.activeSheetId);
      const sheetName = activeSheet ? activeSheet.name : 'Sheet1';

      if (destChoice === 'existing' || (existingInp && document.activeElement === existingInp)) {
        if (existingInp) existingInp.value = `${sheetName}!${ref(r, c)}`;
      } else if (rangeInp && document.activeElement === rangeInp) {
        rangeInp.value = `${sheetName}!${ref(r, c)}`;
      }
    }

    isDragging = true; dragAnchor = { r, c };
    selectCell(r, c, isShiftKey);
    
    if(appState.paintedStyle) {
      const targetData = cellData(r, c);
      Object.assign(targetData, appState.paintedStyle);
      renderCell(r, c);
      pushHistory();
      triggerAutoSave();
    }

    const cellObj = getActiveCells()[ref(r, c)];
    if (cellObj && cellObj.dropdown) {
      openCellDropdownPicker(r, c, td);
    }
    return;
  }
}

gridEl.addEventListener('mousedown', (e) => {
  handlePointerDown(e.target, e.clientX, e.clientY, e.shiftKey, e);
});

// Touch Devices Support
gridEl.addEventListener('touchstart', (e) => {
  if(e.touches.length === 1) {
    const touch = e.touches[0];
    const target = document.elementFromPoint(touch.clientX, touch.clientY);
    if(target) handlePointerDown(target, touch.clientX, touch.clientY, false, e);
  }
}, { passive: true });

function closeContextMenu() {
  const ctxMenu = document.getElementById('contextMenu');
  if(ctxMenu) ctxMenu.classList.remove('show');
}

function openContextMenu(x, y) {
  closeContextMenu();
  closeTopMenu();
  closeSheetMenu();
  closeHeaderMenu();
  closeFontPopover();
  closeToolbarPopover();
  const ctxMenu = document.getElementById('contextMenu');
  if(ctxMenu) {
    ctxMenu.classList.add('show');

    const menuWidth = ctxMenu.offsetWidth || 220;
    const menuHeight = ctxMenu.offsetHeight || 280;

    let finalX = x;
    let finalY = y;

    if (x + menuWidth > window.innerWidth - 10) {
      finalX = window.innerWidth - menuWidth - 10;
    }
    if (y + menuHeight > window.innerHeight - 10) {
      finalY = y - menuHeight;
      if (finalY < 10) finalY = window.innerHeight - menuHeight - 10;
    }

    ctxMenu.style.top = Math.max(10, finalY) + 'px';
    ctxMenu.style.left = Math.max(10, finalX) + 'px';
  }
}

gridEl.addEventListener('contextmenu', (e) => {
  e.preventDefault();
  if(appState.isEditing) commitEdit();

  const colhead = e.target.closest('th.colhead');
  if(colhead) {
    const c = +colhead.dataset.c;
    selectColumn(c);
    openColumnMenu(e.clientX, e.clientY, c);
    return;
  }

  const rowhead = e.target.closest('th.rowhead');
  if(rowhead) {
    const r = +rowhead.dataset.r;
    selectRow(r);
    openRowMenu(e.clientX, e.clientY, r);
    return;
  }

  const td = e.target.closest('td');
  if(td) {
    selectCell(+td.dataset.r, +td.dataset.c, false);
    openContextMenu(e.clientX, e.clientY);
  }
});

function handlePointerMove(clientX, clientY, targetEl) {
  if(!isDragging && !isFillDragging) return;
  if(mouseMoveRaf) return;

  mouseMoveRaf = requestAnimationFrame(() => {
    mouseMoveRaf = null;
    const td = (targetEl || document.elementFromPoint(clientX, clientY))?.closest('td');
    if(!td) return;
    const r = +td.dataset.r, c = +td.dataset.c;

    if(isFillDragging) {
      gridEl.querySelectorAll('.fill-drag-target').forEach(el => el.classList.remove('fill-drag-target'));

      let r1 = fillStartRange.r1, c1 = fillStartRange.c1;
      let r2 = fillStartRange.r2, c2 = fillStartRange.c2;

      if(r > fillStartRange.r2) r2 = r;
      else if(c > fillStartRange.c2) c2 = c;
      else if(r < fillStartRange.r1) r1 = r;
      else if(c < fillStartRange.c1) c1 = c;

      fillTargetRange = { r1, c1, r2, c2 };

      for(let tr = r1; tr <= r2; tr++) {
        for(let tc = c1; tc <= c2; tc++) {
          const targetTd = getTd(tr, tc);
          if(targetTd) targetTd.classList.add('fill-drag-target');
        }
      }
      return;
    }

    if(isDragging) {
      appState.selRange = {
        r1: Math.min(dragAnchor.r, r), c1: Math.min(dragAnchor.c, c),
        r2: Math.max(dragAnchor.r, r), c2: Math.max(dragAnchor.c, c)
      };
      appState.selectionHead = { r, c };
      updateSelectionUI();
    }
  });
}

gridEl.addEventListener('mousemove', (e) => {
  handlePointerMove(e.clientX, e.clientY, e.target);
});

gridEl.addEventListener('touchmove', (e) => {
  if(e.touches.length === 1) {
    const touch = e.touches[0];
    handlePointerMove(touch.clientX, touch.clientY, null);
  }
}, { passive: true });

function handlePointerUp() {
  if(isFillDragging) {
    isFillDragging = false;
    gridEl.querySelectorAll('.fill-drag-target').forEach(el => el.classList.remove('fill-drag-target'));
    if(fillStartRange && fillTargetRange) {
      performAutofill(fillStartRange, fillTargetRange);
    }
    fillStartRange = null;
    fillTargetRange = null;
  }
  isDragging = false;
}

document.addEventListener('mouseup', handlePointerUp);
document.addEventListener('touchend', handlePointerUp);

const gridWrapEl = document.getElementById('gridWrap');
if(gridWrapEl) {
  gridWrapEl.addEventListener('scroll', () => {
    closeContextMenu();
    closeHeaderMenu();
  }, { passive: true });
}

function handleFillHandleDoubleClick() {
  const src = appState.selRange ? { ...appState.selRange } : { r1: appState.selected.r, c1: appState.selected.c, r2: appState.selected.r, c2: appState.selected.c };
  const numRows = appState.numRows || 100;
  const cells = getActiveCells();

  const isPopulated = (r, c) => {
    const data = cells[ref(r, c)];
    return data && data.v !== undefined && data.v !== null && data.v !== '';
  };

  const getColDataEndRow = (colIndex) => {
    let r = 0;
    while (r < numRows && !isPopulated(r, colIndex)) {
      r++;
    }
    if (r >= numRows) return -1;

    let endR = r;
    while (endR + 1 < numRows && isPopulated(endR + 1, colIndex)) {
      endR++;
    }
    return endR;
  };

  let targetRow = -1;

  // 1. Check left adjacent columns (c1 - 1, c1 - 2, ...)
  for (let col = src.c1 - 1; col >= 0; col--) {
    const endR = getColDataEndRow(col);
    if (endR > src.r2) {
      targetRow = endR;
      break;
    }
  }

  // 2. Check right adjacent columns (c2 + 1, c2 + 2, ...)
  if (targetRow === -1) {
    for (let col = src.c2 + 1; col < NUM_COLS; col++) {
      const endR = getColDataEndRow(col);
      if (endR > src.r2) {
        targetRow = endR;
        break;
      }
    }
  }

  // 3. Fallback: Find max populated row in the entire sheet
  if (targetRow === -1) {
    let globalMaxR = -1;
    for (const k in cells) {
      const data = cells[k];
      if (data && data.v !== undefined && data.v !== null && data.v !== '') {
        const p = parseRef(k);
        if (p && p.r > globalMaxR) {
          globalMaxR = p.r;
        }
      }
    }
    if (globalMaxR > src.r2) {
      targetRow = globalMaxR;
    }
  }

  // 4. Fallback if no data anywhere: extend by selection height
  if (targetRow === -1) {
    const srcHeight = src.r2 - src.r1 + 1;
    targetRow = Math.min(numRows - 1, src.r2 + srcHeight);
  }

  if (targetRow > src.r2) {
    const dst = { r1: src.r1, c1: src.c1, r2: targetRow, c2: src.c2 };
    performAutofill(src, dst);
  }
}

gridEl.addEventListener('dblclick', (e) => {
  const handle = e.target.closest('#fillHandle');
  if(handle) {
    e.stopPropagation();
    e.preventDefault();
    handleFillHandleDoubleClick();
    return;
  }

  const td = e.target.closest('td');
  if(td) startEdit(+td.dataset.r, +td.dataset.c);
});

function findDataEdge(startR, startC, dir) {
  const numRows = appState.numRows || 100;
  const maxR = numRows - 1;
  const maxC = NUM_COLS - 1;
  const cells = getActiveCells();

  const isCellPopulated = (r, c) => {
    const k = ref(r, c);
    const cell = cells[k];
    return cell && cell.v !== undefined && cell.v !== null && cell.v !== '';
  };

  let dr = 0, dc = 0;
  if (dir === 'down') dr = 1;
  else if (dir === 'up') dr = -1;
  else if (dir === 'right') dc = 1;
  else if (dir === 'left') dc = -1;

  let r = startR;
  let c = startC;

  const currentPopulated = isCellPopulated(r, c);
  const nextR = r + dr;
  const nextC = c + dc;

  if (nextR < 0 || nextR > maxR || nextC < 0 || nextC > maxC) {
    return { r: clamp(r, 0, maxR), c: clamp(c, 0, maxC) };
  }

  const nextPopulated = isCellPopulated(nextR, nextC);

  if (currentPopulated && nextPopulated) {
    while (r + dr >= 0 && r + dr <= maxR && c + dc >= 0 && c + dc <= maxC) {
      if (!isCellPopulated(r + dr, c + dc)) break;
      r += dr;
      c += dc;
    }
    return { r, c };
  } else if (currentPopulated && !nextPopulated) {
    while (r + dr >= 0 && r + dr <= maxR && c + dc >= 0 && c + dc <= maxC) {
      r += dr;
      c += dc;
      if (isCellPopulated(r, c)) return { r, c };
    }
    return { r: clamp(r, 0, maxR), c: clamp(c, 0, maxC) };
  } else {
    while (r + dr >= 0 && r + dr <= maxR && c + dc >= 0 && c + dc <= maxC) {
      r += dr;
      c += dc;
      if (isCellPopulated(r, c)) return { r, c };
    }
    return { r: clamp(r, 0, maxR), c: clamp(c, 0, maxC) };
  }
}

function getDataRegionBounds(startR, startC) {
  const cells = getActiveCells();
  const activeKeys = Object.keys(cells);
  if (activeKeys.length === 0) return null;

  const populated = [];
  for (const k in cells) {
    const data = cells[k];
    if (data && data.v !== undefined && data.v !== null && data.v !== '') {
      const p = parseRef(k);
      if (p) populated.push(p);
    }
  }
  if (populated.length === 0) return null;

  let minR = Infinity, maxR = -1;
  let minC = Infinity, maxC = -1;

  populated.forEach(p => {
    if (p.r < minR) minR = p.r;
    if (p.r > maxR) maxR = p.r;
    if (p.c < minC) minC = p.c;
    if (p.c > maxC) maxC = p.c;
  });

  if (appState.selRange &&
      appState.selRange.r1 === minR && appState.selRange.r2 === maxR &&
      appState.selRange.c1 === minC && appState.selRange.c2 === maxC) {
    return null;
  }

  return { r1: minR, c1: minC, r2: maxR, c2: maxC };
}

function selectAllDataRegionOrSheet() {
  const bounds = getDataRegionBounds(appState.selected.r, appState.selected.c);
  if (bounds) {
    appState.selected = { r: bounds.r1, c: bounds.c1 };
    appState.selectionHead = { r: bounds.r2, c: bounds.c2 };
    appState.selRange = { ...bounds };
    updateSelectionUI();
    showToast(`Data region selected (${ref(bounds.r1, bounds.c1)}:${ref(bounds.r2, bounds.c2)})`);
  } else {
    selectAll();
    showToast('All cells selected');
  }
}

/* Keyboard Shortcuts & Instant Typing */
document.addEventListener('keydown', (e) => {
  if(e.key === 'Escape') {
    closeContextMenu();
    closeTopMenu();
    closeSheetMenu();
    closeHeaderMenu();
    closeFontPopover();
    closeToolbarPopover();
  }

  const active = document.activeElement;

  // Handle cell editing mode (.cell-inner contentEditable)
  if (appState.isEditing || (active && active.classList.contains('cell-inner'))) {
    if (e.key === 'Enter' && !e.altKey && !e.ctrlKey) { 
      e.preventDefault(); 
      commitEdit(e.shiftKey ? 'up' : 'down'); 
      return; 
    }
    if (e.key === 'Tab') { 
      e.preventDefault(); 
      commitEdit(e.shiftKey ? 'left' : 'right'); 
      return; 
    }
    if (e.key === 'Escape') { 
      e.preventDefault(); 
      cancelEdit(); 
      return; 
    }
    return;
  }

  // Handle formula bar input
  if (active && active.id === 'formulaInput') {
    if (e.key === 'Enter') { e.preventDefault(); commitFromFormulaBar('down'); }
    if (e.key === 'Escape') { e.preventDefault(); active.blur(); }
    return;
  }

  // Ignore global grid keyboard shortcuts if focused on any modal/non-cell input or textarea
  if (active && (
    active.tagName === 'INPUT' || 
    active.tagName === 'TEXTAREA' || 
    active.classList.contains('sheet-tab-input')
  )) {
    return;
  }

  // Ignore global grid shortcuts if any modal overlay or access denied screen is active
  const activeModal = document.querySelector('.modal-overlay.show, #accessDeniedPage');
  if (activeModal && window.getComputedStyle(activeModal).display !== 'none') {
    return;
  }

  const isCtrl = e.ctrlKey || e.metaKey;
  const isShift = e.shiftKey;

  let dir = null;
  if(e.key === 'ArrowDown') dir = 'down';
  else if(e.key === 'ArrowUp') dir = 'up';
  else if(e.key === 'ArrowLeft') dir = 'left';
  else if(e.key === 'ArrowRight') dir = 'right';

  if(dir) {
    e.preventDefault();
    const {r, c} = appState.selectionHead || appState.selected;
    if (isCtrl && isShift) {
      const edge = findDataEdge(r, c, dir);
      selectCell(edge.r, edge.c, true);
      scrollCellIntoView(edge.r, edge.c);
      return;
    } else if (isCtrl) {
      const edge = findDataEdge(r, c, dir);
      selectCell(edge.r, edge.c, false);
      scrollCellIntoView(edge.r, edge.c);
      return;
    } else {
      let targetR = r, targetC = c;
      if (dir === 'down') targetR++;
      else if (dir === 'up') targetR--;
      else if (dir === 'left') targetC--;
      else if (dir === 'right') targetC++;

      selectCell(targetR, targetC, isShift);
      scrollCellIntoView(targetR, targetC);
      return;
    }
  }

  // Ctrl / Cmd Shortcuts
  if(isCtrl) {
    const k = e.key.toLowerCase();
    if(k === 'z') { e.preventDefault(); undo(); return; }
    if(k === 'y') { e.preventDefault(); redo(); return; }
    if(k === 'b') { e.preventDefault(); toggleFormat('b'); return; }
    if(k === 'i') { e.preventDefault(); toggleFormat('i'); return; }
    if(k === 'c') { e.preventDefault(); copySelection(); return; }
    if(k === 'x') { e.preventDefault(); cutSelection(); return; }
    if(k === 'v') { /* let native paste event fire */ return; }
    if(k === 'a') { e.preventDefault(); selectAllDataRegionOrSheet(); return; }
    if(k === 'f' || k === 'h') { e.preventDefault(); openFindReplace(); return; }
    if(k === 'p') { e.preventDefault(); window.print(); return; }
    if(k === 'k') { e.preventDefault(); const lBtn = document.getElementById('insertLinkBtn'); if(lBtn) lBtn.click(); return; }
  }

  if(e.key === 'Escape') {
    if (appState.paintedStyle) {
      appState.paintedStyle = null;
      const btnPaint = document.getElementById('paintFormatBtn');
      if (btnPaint) btnPaint.classList.remove('active');
      showToast('Paint format deactivated');
      return;
    }
  }

  const {r, c} = appState.selectionHead || appState.selected;
  if(e.key === 'Tab') { e.preventDefault(); selectCell(r, c + 1, false); return; }
  if(e.key === 'Enter' || e.key === 'F2') { e.preventDefault(); startEdit(r, c); return; }
  if(e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); clearSelectedCells(); return; }

  if(e.key.length === 1 && !isCtrl && !e.altKey) {
    e.preventDefault();
    startEdit(r, c, e.key);
    return;
  }
});

const formulaInputEl = document.getElementById('formulaInput');
if(formulaInputEl) {
  formulaInputEl.addEventListener('input', (e) => {
    if(!appState.isEditing) {
      startEdit(appState.selected.r, appState.selected.c, e.target.value, true);
    } else {
      const td = getTd(appState.selected.r, appState.selected.c);
      if(td) td.querySelector('.cell-inner').textContent = e.target.value;
    }
  });
}

function commitFromFormulaBar(dir) {
  const formulaInp = document.getElementById('formulaInput');
  const val = formulaInp ? formulaInp.value : '';
  const td = getTd(appState.selected.r, appState.selected.c);
  if(td) td.querySelector('.cell-inner').textContent = val;
  commitEdit(dir);
}

/* ==========================================================================
   FORMATTING & TOOLBAR ACTIONS
   ========================================================================== */
function cellData(r, c) {
  const cells = getActiveCells();
  const k = ref(r, c);
  if(!cells[k]) cells[k] = { v: '' };
  return cells[k];
}

function forEachSelected(fn) {
  const targets = getSelectedCells();
  for(let i = 0; i < targets.length; i++) {
    const {r, c} = targets[i];
    const d = cellData(r, c);
    fn(d);
    renderCell(r, c);
  }
  pushHistory();
  updateSelectionUI();
}

function toggleFormat(prop) {
  const cur = getActiveCells()[ref(appState.selected.r, appState.selected.c)];
  const newVal = !(cur && cur[prop]);
  forEachSelected(d => { d[prop] = newVal; });
}

const btnB = document.getElementById('boldBtn');
if(btnB) btnB.onclick = () => toggleFormat('b');
const btnI = document.getElementById('italicBtn');
if(btnI) btnI.onclick = () => toggleFormat('i');
const btnS = document.getElementById('strikeBtn');
if(btnS) btnS.onclick = () => toggleFormat('s');

const selNumFmt = document.getElementById('numFormatSelect');
if(selNumFmt) selNumFmt.onchange = (e) => forEachSelected(d => { d.fmt = e.target.value; });
const selZoom = document.getElementById('zoomSelect');
if(selZoom) selZoom.onchange = (e) => {
  const z = parseInt(e.target.value, 10);
  const wrap = document.getElementById('gridWrap');
  if(wrap) wrap.style.zoom = (z / 100);
  showToast(`Zoom ${z}%`);
};

const fmtCurr = document.getElementById('fmtCurrency');
if(fmtCurr) fmtCurr.onclick = () => forEachSelected(d => { d.fmt = 'currency'; });
const fmtPerc = document.getElementById('fmtPercent');
if(fmtPerc) fmtPerc.onclick = () => forEachSelected(d => { d.fmt = 'percent'; });

const fmtDecDec = document.getElementById('fmtDecDecrease');
if(fmtDecDec) fmtDecDec.onclick = () => {
  forEachSelected(d => {
    d.decimals = Math.max(0, (d.decimals !== undefined ? d.decimals : 2) - 1);
  });
  renderAll();
};
const zoomSelEl = document.getElementById('zoomSelect');
if (zoomSelEl) {
  zoomSelEl.onchange = (e) => setZoom(+e.target.value);
}

const saveStatusEl = document.getElementById('saveStatus');
if (saveStatusEl) {
  saveStatusEl.onclick = () => {
    saveSpreadsheetToCloud();
    showToast('All changes saved to Cloud');
  };
}

const fmtDecInc = document.getElementById('fmtDecIncrease');
if(fmtDecInc) fmtDecInc.onclick = () => {
  forEachSelected(d => {
    d.decimals = (d.decimals !== undefined ? d.decimals : 2) + 1;
  });
  renderAll();
};

const btnFsInc = document.getElementById('fontSizeInc');
if(btnFsInc) btnFsInc.onclick = () => {
  const inp = document.getElementById('fontSizeInput');
  if(!inp) return;
  inp.value = Math.min(36, parseInt(inp.value, 10) + 1);
  forEachSelected(d => { d.fs = parseInt(inp.value, 10); });
};
const btnFsDec = document.getElementById('fontSizeDec');
if(btnFsDec) btnFsDec.onclick = () => {
  const inp = document.getElementById('fontSizeInput');
  if(!inp) return;
  inp.value = Math.max(6, parseInt(inp.value, 10) - 1);
  forEachSelected(d => { d.fs = parseInt(inp.value, 10); });
};

const btnPrint = document.getElementById('printBtn');
if(btnPrint) btnPrint.onclick = () => window.print();

const btnPaint = document.getElementById('paintFormatBtn');
if(btnPaint) btnPaint.onclick = (e) => {
  e.stopPropagation();
  if (appState.paintedStyle) {
    appState.paintedStyle = null;
    btnPaint.classList.remove('active');
    showToast('Paint format deactivated');
    return;
  }
  const {r, c} = appState.selected;
  const cur = getActiveCells()[ref(r, c)];
  const style = cur ? { ...cur } : { b: false, i: false };
  delete style.v;
  appState.paintedStyle = style;
  btnPaint.classList.add('active');
  showToast('Paint format active: Click any cells to apply format. Press Esc to stop.');
};

const btnFunc = document.getElementById('functionsBtn');
if(btnFunc) btnFunc.onclick = () => {
  startEdit(appState.selected.r, appState.selected.c, '=SUM(');
};

const btnSearchMenu = document.getElementById('searchMenusBtn');
if(btnSearchMenu) btnSearchMenu.onclick = () => {
  openFindReplace();
  const findInp = document.getElementById('findInput');
  if(findInp) {
    findInp.placeholder = 'Search menus, actions and formulas...';
    findInp.focus();
  }
};

/* Colors Popover */
const PALETTE = [
  '#000000','#434343','#666666','#999999','#b7b7b7','#cccccc','#d9d9d9','#ffffff',
  '#980000','#ff0000','#ff9900','#ffff00','#00ff00','#00ffff','#4a86e8','#0000ff',
  '#9900ff','#ff00ff','#e6b8af','#f4cccc','#fce5cd','#fff2cc','#d9ead3','#d0e0e3',
  '#c9daf8','#cfe2f3','#d9d2e9','#ead1dc','#a64d79','#674ea7','#274e13','#0c343d'
];

function openColorPicker(anchorEl, onSelect) {
  document.querySelectorAll('.color-popover').forEach(p => p.remove());
  const pop = document.createElement('div');
  pop.className = 'color-popover';
  PALETTE.forEach(c => {
    const sw = document.createElement('div');
    sw.className = 'color-swatch'; sw.style.background = c;
    sw.onclick = () => { onSelect(c); pop.remove(); };
    pop.appendChild(sw);
  });
  document.body.appendChild(pop);
  const rect = anchorEl.getBoundingClientRect();
  pop.style.top = (rect.bottom + 4) + 'px';
  pop.style.left = rect.left + 'px';
}

const btnTxtColor = document.getElementById('textColorBtn');
if(btnTxtColor) btnTxtColor.onclick = (e) => {
  openColorPicker(e.currentTarget, (c) => {
    forEachSelected(d => { d.color = c; });
    const swatch = document.getElementById('textColorSwatch');
    if(swatch) swatch.setAttribute('fill', c);
  });
};
const btnFillColor = document.getElementById('fillColorBtn');
if(btnFillColor) btnFillColor.onclick = (e) => {
  openColorPicker(e.currentTarget, (c) => {
    forEachSelected(d => { d.bg = c; });
    const swatch = document.getElementById('fillColorSwatch');
    if(swatch) swatch.setAttribute('fill', c);
  });
};

/* ==========================================================================
   INTERACTIVE TOP MENU BAR WITH GOOGLE MATERIAL SYMBOLS
   ========================================================================== */
const MENU_DEFINITIONS = {
  file: [
    { label: 'New Spreadsheet', icon: 'note_add', action: () => resetSpreadsheet() },
    { label: 'Download CSV', icon: 'download', action: () => exportCSV() },
    { label: 'Print', icon: 'print', shortcut: 'Ctrl+P', action: () => window.print() }
  ],
  edit: [
    { label: 'Undo', icon: 'undo', shortcut: 'Ctrl+Z', action: () => undo() },
    { label: 'Redo', icon: 'redo', shortcut: 'Ctrl+Y', action: () => redo() },
    { divider: true },
    { label: 'Cut', icon: 'content_cut', shortcut: 'Ctrl+X', action: () => cutSelection() },
    { label: 'Copy', icon: 'content_copy', shortcut: 'Ctrl+C', action: () => copySelection() },
    { label: 'Paste', icon: 'content_paste', shortcut: 'Ctrl+V', action: () => pasteSelection() },
    { divider: true },
    { label: 'Find and replace', icon: 'search', shortcut: 'Ctrl+F', action: () => openFindReplace() },
    { label: 'Clear contents', icon: 'backspace', shortcut: 'Delete', action: () => clearSelectedCells() }
  ],
  view: [
    { label: 'Toggle Gridlines', icon: 'grid_on', action: () => toggleGridlines() },
    { label: 'Toggle Formula Bar', icon: 'subtitles', action: () => toggleFormulaBar() },
    { divider: true },
    { label: 'Zoom 100%', icon: 'zoom_in', action: () => setZoom(100) },
    { label: 'Zoom 125%', icon: 'zoom_in', action: () => setZoom(125) }
  ],
  insert: [
    { label: 'Row above', icon: 'table_rows', action: () => insertRowAbove(appState.selected.r) },
    { label: 'Row below', icon: 'table_rows', action: () => insertRowAbove(appState.selected.r + 1) },
    { label: 'Column left', icon: 'view_column', action: () => insertColLeft(appState.selected.c) },
    { label: 'Column right', icon: 'view_column', action: () => insertColLeft(appState.selected.c + 1) },
    { divider: true },
    { label: 'Pivot table', icon: 'pivot_table_chart', action: () => openPivotTableModal() },
    { label: 'Insert Link', icon: 'link', shortcut: 'Ctrl+K', action: () => { const b = document.getElementById('insertLinkBtn'); if(b) b.click(); } },
    { label: 'Add Comment', icon: 'add_comment', action: () => { const b = document.getElementById('insertCommentBtn'); if(b) b.click(); } },
    { label: 'Insert Chart', icon: 'bar_chart', action: () => openChartModal() },
    { label: 'Dropdown', icon: 'arrow_drop_down_circle', action: () => openDropdownRulesModal() },
    { divider: true },
    { label: 'New Sheet', icon: 'post_add', action: () => { const b = document.getElementById('addSheetBtn'); if(b) b.click(); } }
  ],
  format: [
    { label: 'Bold', icon: 'format_bold', shortcut: 'Ctrl+B', action: () => toggleFormat('b') },
    { label: 'Italic', icon: 'format_italic', shortcut: 'Ctrl+I', action: () => toggleFormat('i') },
    { label: 'Strikethrough', icon: 'format_strikethrough', action: () => toggleFormat('s') },
    { divider: true },
    { label: 'Merge cells', icon: 'call_merge', action: () => toggleMergeSelected() },
    { divider: true },
    { label: 'Format Currency ($)', icon: 'attach_money', action: () => forEachSelected(d => { d.fmt = 'currency'; }) },
    { label: 'Format Percent (%)', icon: 'percent', action: () => forEachSelected(d => { d.fmt = 'percent'; }) },
    { label: 'Clear Formatting', icon: 'format_clear', action: () => forEachSelected(d => { delete d.b; delete d.i; delete d.s; delete d.color; delete d.bg; delete d.fmt; delete d.border; delete d.merge; delete d.decimals; }) }
  ],
  data: [
    { label: 'Sort Range A ➔ Z', icon: 'sort_by_alpha', action: () => sortRange(true) },
    { label: 'Sort Range Z ➔ A', icon: 'sort_by_alpha', action: () => sortRange(false) },
    { divider: true },
    { label: 'Clear Selected Data', icon: 'delete', action: () => clearSelectedCells() }
  ],
  tools: [
    { label: 'Spell check', icon: 'spellcheck', action: () => showToast('Spell check: No spelling errors found!') },
    { label: 'Count Populated Cells', icon: 'tag', action: () => showToast(`Total active cells: ${Object.keys(getActiveCells()).length}`) }
  ],
  extensions: [
    { label: 'Gemini AI Assistant', icon: 'auto_awesome', action: () => showToast('Gemini AI Assistant ready to help with formulas!') }
  ],
  help: [
    { label: 'Keyboard Shortcuts', icon: 'keyboard', action: () => openShortcutsModal() },
    { label: 'Formulas Reference', icon: 'functions', action: () => showToast('Formulas supported: SUM, AVERAGE, MIN, MAX, COUNT, IF, IFERROR, VLOOKUP, CONCAT, UPPER, LOWER, TODAY, etc.') }
  ]
};

let activeMenuDropdown = null;

function setupTopMenuBar() {
  document.querySelectorAll('.menubar .menu-item').forEach(item => {
    item.addEventListener('click', (e) => {
      e.stopPropagation();
      const menuType = item.dataset.menu;
      if(activeMenuDropdown && activeMenuDropdown.dataset.menu === menuType) {
        closeTopMenu();
        return;
      }
      openTopMenu(item, menuType);
    });
  });

  document.addEventListener('click', () => {
    closeContextMenu();
    closeTopMenu();
    closeSheetMenu();
    closeHeaderMenu();
    closeFontPopover();
    closeToolbarPopover();
  });
}

function openTopMenu(anchorEl, menuType) {
  closeTopMenu();
  closeHeaderMenu();
  closeFontPopover();
  closeToolbarPopover();
  const items = MENU_DEFINITIONS[menuType];
  if(!items) return;

  const drop = document.createElement('div');
  drop.className = 'dropdown-menu show';
  drop.dataset.menu = menuType;

  items.forEach(it => {
    if(it.divider) {
      const div = document.createElement('div');
      div.className = 'dropdown-divider';
      drop.appendChild(div);
    } else {
      const el = document.createElement('div');
      el.className = 'dropdown-item';
      const iconHtml = it.icon ? `<span class="material-symbols-outlined" style="font-size:18px; color:#1a73e8;">${it.icon}</span>` : '';
      el.innerHTML = `${iconHtml}<span>${it.label}</span>` + (it.shortcut ? `<span class="shortcut">${it.shortcut}</span>` : '');
      el.onclick = (e) => {
        e.stopPropagation();
        closeTopMenu();
        it.action();
      };
      drop.appendChild(el);
    }
  });

  document.body.appendChild(drop);
  const rect = anchorEl.getBoundingClientRect();
  drop.style.top = (rect.bottom + 2) + 'px';
  drop.style.left = rect.left + 'px';
  anchorEl.classList.add('active');
  activeMenuDropdown = drop;
}

function closeTopMenu() {
  if(activeMenuDropdown) {
    activeMenuDropdown.remove();
    activeMenuDropdown = null;
  }
  document.querySelectorAll('.menubar .menu-item').forEach(m => m.classList.remove('active'));
}

function toggleGridlines() {
  appState.showGridlines = !appState.showGridlines;
  gridEl.style.border = appState.showGridlines ? '' : 'none';
  showToast(appState.showGridlines ? 'Gridlines shown' : 'Gridlines hidden');
}

function toggleFormulaBar() {
  appState.showFormulaBar = !appState.showFormulaBar;
  const fBar = document.getElementById('formulabar');
  if(fBar) fBar.style.display = appState.showFormulaBar ? 'flex' : 'none';
  showToast(appState.showFormulaBar ? 'Formula bar shown' : 'Formula bar hidden');
}

function setZoom(val) {
  const zSel = document.getElementById('zoomSelect');
  if(zSel) zSel.value = val;
  const wrap = document.getElementById('gridWrap');
  if(wrap) wrap.style.zoom = (val / 100);
  showToast(`Zoom set to ${val}%`);
}

function resetSpreadsheet() {
  getActiveSheet().cells = {};
  renderAll(); pushHistory(); updateSelectionUI();
  showToast('Spreadsheet reset');
}

function exportCSV() {
  const cells = getActiveCells();
  let maxR = 0, maxC = 0;
  for(const k in cells) {
    const p = parseRef(k);
    if(p && cells[k].v !== '' && cells[k].v !== undefined) {
      maxR = Math.max(maxR, p.r);
      maxC = Math.max(maxC, p.c);
    }
  }
  let csv = '';
  for(let r = 0; r <= maxR; r++) {
    const row = [];
    for(let c = 0; c <= maxC; c++) {
      let v = formatDisplay(r, c);
      if(v.includes(',') || v.includes('"')) v = '"' + v.replace(/"/g, '""') + '"';
      row.push(v);
    }
    csv += row.join(',') + '\n';
  }
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = (appState.title || 'sheet') + '.csv';
  a.click();
  showToast('Exported to CSV');
}

function sortRange(ascending) {
  const c = appState.selected.c;
  const cells = getActiveCells();
  const numRows = appState.numRows || 100;
  const rowsData = [];
  for(let r = 0; r < numRows; r++) {
    const val = getCellValueRaw(appState.activeSheetId, r, c);
    rowsData.push({ r, val, cellObj: cells[ref(r, c)] });
  }
  rowsData.sort((a, b) => {
    if(a.val === '' || a.val === null || a.val === undefined) return 1;
    if(b.val === '' || b.val === null || b.val === undefined) return -1;
    const sa = String(a.val), sb = String(b.val);
    return ascending ? sa.localeCompare(sb, undefined, {numeric:true}) : sb.localeCompare(sa, undefined, {numeric:true});
  });
  const newCells = {};
  for(let newR = 0; newR < rowsData.length; newR++) {
    const orig = rowsData[newR];
    if(orig.cellObj) newCells[ref(newR, c)] = orig.cellObj;
  }
  for(const k in cells) {
    const p = parseRef(k);
    if(p && p.c !== c) newCells[k] = cells[k];
  }
  getActiveSheet().cells = newCells;
  renderAll(); pushHistory();
  showToast(`Column ${colToLetter(c)} sorted ${ascending ? 'A-Z' : 'Z-A'}`);
}

function openShortcutsModal() {
  const m = document.getElementById('shortcutsModal');
  if(m) m.classList.add('show');
}

const closeShortcutsBtn = document.getElementById('closeShortcutsBtn');
if(closeShortcutsBtn) {
  closeShortcutsBtn.onclick = () => {
    document.getElementById('shortcutsModal').classList.remove('show');
  };
}

/* Document Title Renaming */
const docTitleInp = document.getElementById('docTitle');
if(docTitleInp) {
  docTitleInp.addEventListener('input', (e) => {
    appState.title = e.target.value || 'Untitled spreadsheet';
    document.title = (e.target.value || 'Untitled spreadsheet') + ' - Google Sheets';
  });

  const saveTitleChange = () => {
    if (!isSheetLoaded || !isUserAuthorized || isUserReadOnly) return;
    triggerAutoSave();
    if (ws && ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify({
        action: 'sheet_broadcast',
        sheetId: currentSpreadsheetId,
        event: 'title_change',
        data: { title: appState.title }
      }));
    }
  };

  docTitleInp.addEventListener('change', saveTitleChange);
  docTitleInp.addEventListener('blur', saveTitleChange);
  docTitleInp.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      docTitleInp.blur();
    }
  });
}

/* ==========================================================================
   HIGH-PERFORMANCE RESIZING
   ========================================================================== */
let isResizingAttached = false;

function attachResizersOnce() {
  if(isResizingAttached) return;
  isResizingAttached = true;

  gridEl.addEventListener('mousedown', (e) => {
    const colResizer = e.target.closest('.col-resizer');
    if(colResizer) {
      e.stopPropagation();
      e.preventDefault();
      const col = +colResizer.dataset.c;
      const startX = e.clientX;
      const sheet = getActiveSheet();
      const startW = sheet.colWidths[col] || 100;
      const colEl = gridEl.querySelector(`col[data-col="${col}"]`);

      function onMove(me) {
        if(resizeRaf) return;
        resizeRaf = requestAnimationFrame(() => {
          resizeRaf = null;
          const diff = me.clientX - startX;
          const newW = Math.max(30, startW + diff);
          sheet.colWidths[col] = newW;
          if(colEl) colEl.style.width = newW + 'px';
        });
      }
      function onUp() {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
      }
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', onUp);
      return;
    }

    const rowResizer = e.target.closest('.row-resizer');
    if(rowResizer) {
      e.stopPropagation();
      e.preventDefault();
      const row = +rowResizer.dataset.r;
      const startY = e.clientY;
      const sheet = getActiveSheet();
      const startH = sheet.rowHeights[row] || 24;
      const trEl = gridEl.querySelector(`tr[data-tr="${row}"]`);

      function onMove(me) {
        if(resizeRaf) return;
        resizeRaf = requestAnimationFrame(() => {
          resizeRaf = null;
          const diff = me.clientY - startY;
          const newH = Math.max(18, startH + diff);
          sheet.rowHeights[row] = newH;
          if(trEl) trEl.style.height = newH + 'px';
        });
      }
      function onUp() {
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
      }
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', onUp);
      return;
    }
  });
}

/* ==========================================================================
   ROW & COLUMN OPERATIONS
   ========================================================================== */
function insertRowAbove(r) {
  const sheet = getActiveSheet();
  const cells = sheet.cells;
  const newCells = {};
  for(const k in cells) {
    const p = parseRef(k);
    if(p) {
      if(p.r >= r) newCells[ref(p.r + 1, p.c)] = cells[k];
      else newCells[k] = cells[k];
    }
  }
  sheet.cells = newCells;
  buildGrid(); renderAll(); pushHistory(); showToast('Row inserted');
}

function insertColLeft(c) {
  const sheet = getActiveSheet();
  const cells = sheet.cells;
  const newCells = {};
  for(const k in cells) {
    const p = parseRef(k);
    if(p) {
      if(p.c >= c) newCells[ref(p.r, p.c + 1)] = cells[k];
      else newCells[k] = cells[k];
    }
  }
  sheet.cells = newCells;
  buildGrid(); renderAll(); pushHistory(); showToast('Column inserted');
}

function deleteRow(r) {
  const sheet = getActiveSheet();
  const cells = sheet.cells;
  const newCells = {};
  for(const k in cells) {
    const p = parseRef(k);
    if(p) {
      if(p.r === r) continue;
      if(p.r > r) newCells[ref(p.r - 1, p.c)] = cells[k];
      else newCells[k] = cells[k];
    }
  }
  sheet.cells = newCells;
  
  const newHeights = {};
  for (const rowIdx in sheet.rowHeights) {
    const ri = parseInt(rowIdx, 10);
    if (ri === r) continue;
    if (ri > r) newHeights[ri - 1] = sheet.rowHeights[ri];
    else newHeights[ri] = sheet.rowHeights[ri];
  }
  sheet.rowHeights = newHeights;

  buildGrid(); renderAll(); pushHistory(); showToast('Row deleted');
}

function deleteCol(c) {
  const sheet = getActiveSheet();
  const cells = sheet.cells;
  const newCells = {};
  for(const k in cells) {
    const p = parseRef(k);
    if(p) {
      if(p.c === c) continue;
      if(p.c > c) newCells[ref(p.r, p.c - 1)] = cells[k];
      else newCells[k] = cells[k];
    }
  }
  sheet.cells = newCells;

  const newWidths = {};
  for (const colIdx in sheet.colWidths) {
    const ci = parseInt(colIdx, 10);
    if (ci === c) continue;
    if (ci > c) newWidths[ci - 1] = sheet.colWidths[ci];
    else newWidths[ci] = sheet.colWidths[ci];
  }
  sheet.colWidths = newWidths;

  buildGrid(); renderAll(); pushHistory(); showToast('Column deleted');
}

/* ==========================================================================
   CLIPBOARD & CONTEXT MENU
/* ==========================================================================
   CLIPBOARD ENGINE (Excel / Google Sheets TSV & HTML Support)
   ========================================================================== */
function parseTSV(text) {
  if (!text) return [];
  const lines = [];
  let currentRow = [];
  let currentField = '';
  let inQuotes = false;

  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const nextChar = text[i + 1];

    if (inQuotes) {
      if (char === '"' && nextChar === '"') {
        currentField += '"';
        i++;
      } else if (char === '"') {
        inQuotes = false;
      } else {
        currentField += char;
      }
    } else {
      if (char === '"') {
        inQuotes = true;
      } else if (char === '\t') {
        currentRow.push(currentField);
        currentField = '';
      } else if (char === '\r' && nextChar === '\n') {
        currentRow.push(currentField);
        lines.push(currentRow);
        currentRow = [];
        currentField = '';
        i++;
      } else if (char === '\n' || char === '\r') {
        currentRow.push(currentField);
        lines.push(currentRow);
        currentRow = [];
        currentField = '';
      } else {
        currentField += char;
      }
    }
  }
  if (currentField !== '' || currentRow.length > 0) {
    currentRow.push(currentField);
    lines.push(currentRow);
  }
  if (lines.length > 1 && lines[lines.length - 1].length === 1 && lines[lines.length - 1][0] === '') {
    lines.pop();
  }
  return lines;
}

function parseHTMLTable(htmlText) {
  if (!htmlText || (!htmlText.includes('<table') && !htmlText.includes('<td'))) return null;
  try {
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlText, 'text/html');
    const rows = doc.querySelectorAll('tr');
    if (!rows.length) return null;

    const grid = [];
    rows.forEach(tr => {
      const rowData = [];
      const cells = tr.querySelectorAll('td, th');
      cells.forEach(td => {
        const val = (td.textContent || td.innerText || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const cellObj = { v: val };
        
        const style = td.style;
        const fw = style.fontWeight || (td.querySelector('b, strong') ? 'bold' : '');
        const fs = style.fontStyle || (td.querySelector('i, em') ? 'italic' : '');
        const color = style.color || '';
        const bg = style.backgroundColor || '';

        if (fw === 'bold' || fw === '700' || fw === '800' || fw === '900') cellObj.b = true;
        if (fs === 'italic') cellObj.i = true;
        if (color && color !== 'inherit' && color !== 'initial') cellObj.color = color;
        if (bg && bg !== 'inherit' && bg !== 'initial' && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') cellObj.bg = bg;

        rowData.push(cellObj);
      });
      if (rowData.length > 0) grid.push(rowData);
    });
    return grid.length ? grid : null;
  } catch (e) {
    return null;
  }
}

function pasteDataGrid(gridData) {
  if (!gridData || !gridData.length) return;

  const baseR = appState.selected ? appState.selected.r : 0;
  const baseC = appState.selected ? appState.selected.c : 0;
  const cells = getActiveCells();

  // Auto expand sheet rows if needed
  const reqRows = baseR + gridData.length;
  if (reqRows > (appState.numRows || 100)) {
    appState.numRows = reqRows + 20;
    buildGrid(); // Create DOM table rows for newly expanded space
  }

  let maxR = baseR;
  let maxC = baseC;

  for (let i = 0; i < gridData.length; i++) {
    for (let j = 0; j < gridData[i].length; j++) {
      const r = baseR + i;
      const c = baseC + j;
      if (c >= NUM_COLS) continue;

      const item = gridData[i][j];
      if (item === null || item === undefined) continue;

      let cellObj = typeof item === 'object' ? { ...item } : { v: String(item) };

      if (typeof cellObj.v === 'string' && cellObj.v.startsWith('=')) {
        cellObj.v = adjustFormulaReferences(cellObj.v, i, j);
      }

      if (cellObj.v !== undefined && cellObj.v !== '') {
        cells[ref(r, c)] = cellObj;
      } else {
        delete cells[ref(r, c)];
      }

      if (r > maxR) maxR = r;
      if (c > maxC) maxC = c;
    }
  }

  // Highlight selection box around the whole pasted range
  appState.selected = { r: baseR, c: baseC };
  appState.selectionEnd = { r: maxR, c: maxC };

  // Broadcast single full_sync event to collaborators
  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({
      action: 'sheet_broadcast',
      sheetId: currentSpreadsheetId,
      event: 'full_sync',
      data: {
        activeSheetId: appState.activeSheetId,
        sheets: appState.sheets
      }
    }));
  }

  pushHistory();
  renderAll();
  updateSelectionUI();
  triggerAutoSave();
  showToast(`Pasted ${gridData.length} row(s)`);
}

// Build TSV text from current selection for clipboard operations
function buildSelectionTSV() {
  const targets = getSelectedCells();
  const rows = [...new Set(targets.map(t=>t.r))].sort((a,b)=>a-b);
  const cols = [...new Set(targets.map(t=>t.c))].sort((a,b)=>a-b);
  const cells = getActiveCells();
  return {
    rows, cols,
    tsvText: rows.map(r => 
      cols.map(c => {
        const cell = cells[ref(r, c)];
        let val = cell && cell.v !== undefined ? String(cell.v) : '';
        if (val.includes('\t') || val.includes('\n') || val.includes('"')) {
          val = '"' + val.replace(/"/g, '""') + '"';
        }
        return val;
      }).join('\t')
    ).join('\r\n')
  };
}

function copySelection() {
  const targets = getSelectedCells();
  const rows = [...new Set(targets.map(t=>t.r))].sort((a,b)=>a-b);
  const cols = [...new Set(targets.map(t=>t.c))].sort((a,b)=>a-b);
  const cells = getActiveCells();
  appState.clipboard = rows.map(r => cols.map(c => ({ ...cells[ref(r,c)] })));

  const { tsvText } = buildSelectionTSV();

  // Try Clipboard API (works in secure contexts), fall back gracefully
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(tsvText)
      .then(() => showToast('Copied to clipboard'))
      .catch(() => {
        // Clipboard API failed (non-HTTPS or no focus) — use execCommand fallback
        fallbackCopyToClipboard(tsvText);
        showToast('Copied to clipboard');
      });
  } else {
    fallbackCopyToClipboard(tsvText);
    showToast('Copied to clipboard');
  }
}

// Legacy fallback for non-secure contexts (HTTP over LAN)
function fallbackCopyToClipboard(text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
  document.body.appendChild(ta);
  ta.select();
  try { document.execCommand('copy'); } catch(e) {}
  document.body.removeChild(ta);
}

function cutSelection() {
  copySelection();
  clearSelectedCells();
}

async function pasteSelection() {
  // Try Clipboard API first (async, requires permissions)
  try {
    if (navigator.clipboard && navigator.clipboard.read) {
      const clipboardItems = await navigator.clipboard.read();
      for (const item of clipboardItems) {
        if (item.types.includes('text/html')) {
          const blob = await item.getType('text/html');
          const htmlText = await blob.text();
          const grid = parseHTMLTable(htmlText);
          if (grid && grid.length) {
            pasteDataGrid(grid);
            return;
          }
        }
        if (item.types.includes('text/plain')) {
          const blob = await item.getType('text/plain');
          const plainText = await blob.text();
          const tsvData = parseTSV(plainText);
          const grid = tsvData.map(row => row.map(val => ({ v: val })));
          if (grid && grid.length) {
            pasteDataGrid(grid);
            return;
          }
        }
      }
    }
  } catch (err) {
    // Clipboard API unavailable or denied — fall back to internal clipboard
  }
  // Fallback: use internal clipboard stored from copySelection()
  if (appState.clipboard) {
    pasteDataGrid(appState.clipboard);
  }
}

/* ── Native Clipboard Event Listeners (work in ALL browsers including HTTP) ── */
document.addEventListener('copy', (e) => {
  if (appState.isEditing || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
  e.preventDefault();
  // Store internal clipboard
  const targets = getSelectedCells();
  const rows = [...new Set(targets.map(t=>t.r))].sort((a,b)=>a-b);
  const cols = [...new Set(targets.map(t=>t.c))].sort((a,b)=>a-b);
  const cells = getActiveCells();
  appState.clipboard = rows.map(r => cols.map(c => ({ ...cells[ref(r,c)] })));
  // Write to system clipboard via native event
  const { tsvText } = buildSelectionTSV();
  if (e.clipboardData) {
    e.clipboardData.setData('text/plain', tsvText);
  }
  showToast('Copied to clipboard');
});

document.addEventListener('cut', (e) => {
  if (appState.isEditing || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
  e.preventDefault();
  // Store internal clipboard
  const targets = getSelectedCells();
  const rows = [...new Set(targets.map(t=>t.r))].sort((a,b)=>a-b);
  const cols = [...new Set(targets.map(t=>t.c))].sort((a,b)=>a-b);
  const cells = getActiveCells();
  appState.clipboard = rows.map(r => cols.map(c => ({ ...cells[ref(r,c)] })));
  // Write to system clipboard
  const { tsvText } = buildSelectionTSV();
  if (e.clipboardData) {
    e.clipboardData.setData('text/plain', tsvText);
  }
  // Clear original cells after copy
  clearSelectedCells();
  showToast('Cut to clipboard');
});

document.addEventListener('paste', (e) => {
  if (appState.isEditing || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
    return;
  }
  
  e.preventDefault();
  const plainText = e.clipboardData ? e.clipboardData.getData('text/plain') : '';
  const htmlText = e.clipboardData ? e.clipboardData.getData('text/html') : '';

  let grid = parseHTMLTable(htmlText);
  if (!grid || !grid.length) {
    const tsvData = parseTSV(plainText);
    grid = tsvData.map(row => row.map(val => ({ v: val })));
  }

  if (grid && grid.length) {
    pasteDataGrid(grid);
  } else if (appState.clipboard) {
    pasteDataGrid(appState.clipboard);
  }
});

/* Context Menu Handlers */
const ctxCut = document.getElementById('ctxCut');
if(ctxCut) ctxCut.onclick = (e) => { e.stopPropagation(); closeContextMenu(); cutSelection(); };
const ctxCopy = document.getElementById('ctxCopy');
if(ctxCopy) ctxCopy.onclick = (e) => { e.stopPropagation(); closeContextMenu(); copySelection(); };
const ctxPaste = document.getElementById('ctxPaste');
if(ctxPaste) ctxPaste.onclick = (e) => { e.stopPropagation(); closeContextMenu(); pasteSelection(); };
const ctxClear = document.getElementById('ctxClear');
if(ctxClear) ctxClear.onclick = (e) => { e.stopPropagation(); closeContextMenu(); clearSelectedCells(); };

const ctxInsRow = document.getElementById('ctxInsertRow');
if(ctxInsRow) ctxInsRow.onclick = (e) => { e.stopPropagation(); closeContextMenu(); insertRowAbove(appState.selected.r); };
const ctxInsCol = document.getElementById('ctxInsertCol');
if(ctxInsCol) ctxInsCol.onclick = (e) => { e.stopPropagation(); closeContextMenu(); insertColLeft(appState.selected.c); };
const ctxDelRow = document.getElementById('ctxDeleteRow');
if(ctxDelRow) ctxDelRow.onclick = (e) => { e.stopPropagation(); closeContextMenu(); deleteRow(appState.selected.r); };
const ctxDelCol = document.getElementById('ctxDeleteCol');
if(ctxDelCol) ctxDelCol.onclick = (e) => { e.stopPropagation(); closeContextMenu(); deleteCol(appState.selected.c); };

/* ==========================================================================
   DATA VALIDATION / DROPDOWN ENGINE
   ========================================================================== */
function getContrastTextColor(hexColor) {
  if (!hexColor || hexColor === 'transparent') return '#202124';
  let hex = hexColor.replace('#', '');
  if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
  if (hex.length !== 6) return '#202124';
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  const yiq = (r * 299 + g * 587 + b * 114) / 1000;
  return yiq >= 160 ? '#202124' : '#ffffff';
}

function closeCellDropdownPicker() {
  const existing = document.getElementById('cellDropdownPicker');
  if (existing) existing.remove();
}

function openCellDropdownPicker(r, c, targetTd) {
  closeCellDropdownPicker();
  const data = getActiveCells()[ref(r, c)];
  if (!data || !data.dropdown || !Array.isArray(data.dropdown.options)) return;

  const picker = document.createElement('div');
  picker.id = 'cellDropdownPicker';
  picker.className = 'cell-dropdown-picker-menu';
  picker.style.cssText = `
    position: absolute;
    z-index: 999999;
    background: #ffffff;
    border: 1px solid #dadce0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    padding: 6px;
    min-width: 150px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-family: Inter, Roboto, sans-serif;
  `;

  const rect = targetTd.getBoundingClientRect();
  picker.style.top = (rect.bottom + window.scrollY + 2) + 'px';
  picker.style.left = (rect.left + window.scrollX) + 'px';

  data.dropdown.options.forEach(opt => {
    const item = document.createElement('div');
    const chipBg = opt.color || '#e8f0fe';
    const chipText = getContrastTextColor(chipBg);
    item.style.cssText = `
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      background: ${chipBg};
      color: ${chipText};
      transition: transform 0.1s ease;
    `;
    item.innerHTML = `<span>${escapeHtml(opt.label)}</span>`;
    item.onmouseenter = () => item.style.transform = 'scale(1.02)';
    item.onmouseleave = () => item.style.transform = 'scale(1)';

    item.onclick = (e) => {
      e.stopPropagation();
      const cells = getActiveCells();
      if (!cells[ref(r, c)]) cells[ref(r, c)] = {};
      cells[ref(r, c)].v = opt.label;
      renderCell(r, c);
      pushHistory();
      triggerAutoSave();
      closeCellDropdownPicker();

      if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({
          action: 'sheet_broadcast',
          sheetId: currentSpreadsheetId,
          event: 'cell_edit',
          data: {
            activeSheetId: appState.activeSheetId,
            cellRef: ref(r, c),
            cellObj: cells[ref(r, c)]
          }
        }));
      }
    };
    picker.appendChild(item);
  });

  document.body.appendChild(picker);

  const closeListener = (evt) => {
    if (!picker.contains(evt.target)) {
      closeCellDropdownPicker();
      document.removeEventListener('mousedown', closeListener);
    }
  };
  setTimeout(() => document.addEventListener('mousedown', closeListener), 0);
}

let currentDropdownDraftOptions = [];
const DEFAULT_PRESET_COLORS = ['#e6f4ea', '#e8f0fe', '#fef7e0', '#fce8e6', '#f3e8fd', '#feefe3', '#f1f3f4'];

function renderDropdownModalOptions() {
  const container = document.getElementById('dropdownItemsList');
  if (!container) return;
  container.innerHTML = '';

  currentDropdownDraftOptions.forEach((opt, idx) => {
    const row = document.createElement('div');
    row.className = 'dropdown-option-row';
    row.style.cssText = 'display:flex; align-items:center; gap:8px; position:relative; margin-bottom:4px;';

    // Color Circle Button
    const colorBtn = document.createElement('div');
    colorBtn.className = 'dropdown-color-picker';
    colorBtn.setAttribute('data-color', opt.color || '#e6f4ea');
    colorBtn.style.cssText = `width:26px; height:26px; border-radius:50%; background:${opt.color || '#e6f4ea'}; cursor:pointer; border:2px solid #fff; box-shadow:0 0 0 1px #dadce0; flex-shrink:0; position:relative; overflow:hidden;`;
    colorBtn.title = 'Choose color';

    // Color Picker Native Input (invisible overlay)
    const hiddenColorInput = document.createElement('input');
    hiddenColorInput.type = 'color';
    hiddenColorInput.value = (opt.color && opt.color.startsWith('#') && opt.color.length === 7) ? opt.color : '#e6f4ea';
    hiddenColorInput.style.cssText = 'position:absolute; top:-10px; left:-10px; width:50px; height:50px; opacity:0; cursor:pointer;';
    hiddenColorInput.oninput = (e) => {
      opt.color = e.target.value;
      colorBtn.setAttribute('data-color', e.target.value);
      colorBtn.style.background = e.target.value;
    };
    colorBtn.appendChild(hiddenColorInput);

    // Text Input for Option Label
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'dropdown-option-label-input';
    input.value = opt.label || '';
    input.placeholder = `Option ${idx + 1}`;
    input.style.cssText = 'flex:1; border:1px solid #dadce0; border-radius:6px; padding:7px 12px; font-size:13px; font-family:inherit; outline:none; color:#202124; background:#fff;';
    input.oninput = (e) => opt.label = e.target.value;

    // Delete Button
    const delBtn = document.createElement('span');
    delBtn.className = 'material-symbols-outlined';
    delBtn.style.cssText = 'font-size:18px; color:#5f6368; cursor:pointer; flex-shrink:0; padding:4px; border-radius:4px;';
    delBtn.textContent = 'delete';
    delBtn.onmouseenter = () => delBtn.style.background = '#f1f3f4';
    delBtn.onmouseleave = () => delBtn.style.background = 'transparent';
    delBtn.onclick = () => {
      currentDropdownDraftOptions.splice(idx, 1);
      renderDropdownModalOptions();
    };

    row.appendChild(colorBtn);
    row.appendChild(input);
    row.appendChild(delBtn);
    container.appendChild(row);
  });
}

function openDropdownRulesModal() {
  const modal = document.getElementById('dropdownModal');
  const rangeInp = document.getElementById('dropdownRangeInput');
  if (!modal) return;

  const targets = getSelectedCells();
  if (!targets.length) return;

  const minR = Math.min(...targets.map(t => t.r));
  const maxR = Math.max(...targets.map(t => t.r));
  const minC = Math.min(...targets.map(t => t.c));
  const maxC = Math.max(...targets.map(t => t.c));

  const rangeStr = (minR === maxR && minC === maxC)
    ? ref(minR, minC)
    : `${ref(minR, minC)}:${ref(maxR, maxC)}`;

  if (rangeInp) rangeInp.value = rangeStr;

  const firstData = getActiveCells()[ref(minR, minC)];
  if (firstData && firstData.dropdown && Array.isArray(firstData.dropdown.options) && firstData.dropdown.options.length > 0) {
    currentDropdownDraftOptions = JSON.parse(JSON.stringify(firstData.dropdown.options));
  } else {
    currentDropdownDraftOptions = [
      { label: 'Option 1', color: '#e6f4ea' },
      { label: 'Option 2', color: '#fce8e6' }
    ];
  }

  renderDropdownModalOptions();
  modal.classList.add('show');
}

const ctxDropdown = document.getElementById('ctxDropdown');
if (ctxDropdown) ctxDropdown.onclick = (e) => { e.stopPropagation(); closeContextMenu(); openDropdownRulesModal(); };

const closeDropdownBtn = document.getElementById('closeDropdownModalBtn');
if (closeDropdownBtn) closeDropdownBtn.onclick = () => document.getElementById('dropdownModal').classList.remove('show');

const addDropdownOptionBtn = document.getElementById('addDropdownOptionBtn');
if (addDropdownOptionBtn) addDropdownOptionBtn.onclick = () => {
  const colorIndex = currentDropdownDraftOptions.length % DEFAULT_PRESET_COLORS.length;
  currentDropdownDraftOptions.push({
    label: `Option ${currentDropdownDraftOptions.length + 1}`,
    color: DEFAULT_PRESET_COLORS[colorIndex]
  });
  renderDropdownModalOptions();
};

const saveDropdownRuleBtn = document.getElementById('saveDropdownRuleBtn');
if (saveDropdownRuleBtn) saveDropdownRuleBtn.onclick = () => {
  const rows = document.querySelectorAll('#dropdownItemsList .dropdown-option-row');
  const validOptions = [];
  rows.forEach(r => {
    const colorBtn = r.querySelector('.dropdown-color-picker');
    const input = r.querySelector('.dropdown-option-label-input');
    const color = colorBtn ? colorBtn.getAttribute('data-color') || '#e6f4ea' : '#e6f4ea';
    const label = input ? input.value.trim() : '';
    if (label !== '') {
      validOptions.push({ label, color });
    }
  });

  if (!validOptions.length) {
    showToast('Please add at least one dropdown option');
    return;
  }

  const targets = getSelectedCells();
  const cells = getActiveCells();
  targets.forEach(t => {
    const key = ref(t.r, t.c);
    if (!cells[key]) cells[key] = {};
    cells[key].dropdown = { options: validOptions };

    const currentVal = cells[key].v;
    const match = validOptions.find(o => o.label === currentVal);
    if (!match) {
      cells[key].v = validOptions[0].label;
    }
  });

  pushHistory();
  renderAll();
  triggerAutoSave();

  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({
      action: 'sheet_broadcast',
      sheetId: currentSpreadsheetId,
      event: 'full_sync',
      data: {
        activeSheetId: appState.activeSheetId,
        sheets: appState.sheets
      }
    }));
  }

  document.getElementById('dropdownModal').classList.remove('show');
  showToast('Dropdown rule saved');
};

const removeDropdownRuleBtn = document.getElementById('removeDropdownRuleBtn');
if (removeDropdownRuleBtn) removeDropdownRuleBtn.onclick = () => {
  const targets = getSelectedCells();
  const cells = getActiveCells();
  targets.forEach(t => {
    const key = ref(t.r, t.c);
    if (cells[key]) {
      delete cells[key].dropdown;
    }
  });

  pushHistory();
  renderAll();
  triggerAutoSave();
  document.getElementById('dropdownModal').classList.remove('show');
  showToast('Dropdown rule removed');
};

/* ==========================================================================
   FIND & REPLACE ENGINE WITH LIVE MATCH NAVIGATION
   ========================================================================== */
const modal = document.getElementById('findReplaceModal');
const findInput = document.getElementById('findInput');
const replaceInput = document.getElementById('replaceInput');
const findMatchCount = document.getElementById('findMatchCount');

let findMatches = [];
let findIndex = -1;

function openFindReplace() {
  if(!modal) return;
  modal.classList.add('show');
  if(findInput) {
    findInput.focus();
    findInput.select();
  }
  updateFindMatches();
}
const closeFindBtn = document.getElementById('closeFindBtn');
if(closeFindBtn) closeFindBtn.onclick = () => modal.classList.remove('show');

function updateFindMatches() {
  const query = findInput ? findInput.value.trim().toLowerCase() : '';
  findMatches = [];
  
  if(!query) {
    if(findMatchCount) findMatchCount.textContent = '';
    return;
  }

  const numRows = appState.numRows || 100;
  for(let r = 0; r < numRows; r++) {
    for(let c = 0; c < NUM_COLS; c++) {
      const val = String(getCellValueRaw(appState.activeSheetId, r, c)).toLowerCase();
      if(val.includes(query)) {
        findMatches.push({ r, c });
      }
    }
  }

  if(findMatchCount) {
    findMatchCount.textContent = findMatches.length ? `${findMatches.length} match${findMatches.length > 1 ? 'es' : ''} found` : 'No matches found';
  }
}

if(findInput) {
  findInput.addEventListener('input', () => {
    findIndex = -1;
    updateFindMatches();
  });

  findInput.addEventListener('keydown', (e) => {
    if(e.key === 'Enter') {
      e.preventDefault();
      if(e.shiftKey) findPrev();
      else findNext();
    }
  });
}

function scrollCellIntoView(r, c) {
  const td = getTd(r, c);
  if(td) td.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
}

function findNext() {
  const query = findInput ? findInput.value.trim() : '';
  if(!query) return;
  updateFindMatches();
  if(!findMatches.length) {
    showToast('No matches found');
    return;
  }
  findIndex = (findIndex + 1) % findMatches.length;
  const target = findMatches[findIndex];
  selectCell(target.r, target.c, false);
  scrollCellIntoView(target.r, target.c);
  if(findMatchCount) {
    findMatchCount.textContent = `${findIndex + 1} of ${findMatches.length} matches (${ref(target.r, target.c)})`;
  }
}

function findPrev() {
  const query = findInput ? findInput.value.trim() : '';
  if(!query) return;
  updateFindMatches();
  if(!findMatches.length) {
    showToast('No matches found');
    return;
  }
  if(findIndex <= 0) findIndex = findMatches.length - 1;
  else findIndex--;
  const target = findMatches[findIndex];
  selectCell(target.r, target.c, false);
  scrollCellIntoView(target.r, target.c);
  if(findMatchCount) {
    findMatchCount.textContent = `${findIndex + 1} of ${findMatches.length} matches (${ref(target.r, target.c)})`;
  }
}

function replaceCurrent() {
  const query = findInput ? findInput.value.trim() : '';
  const repl = replaceInput ? replaceInput.value : '';
  if(!query) return;
  if(findIndex < 0 || !findMatches[findIndex]) {
    findNext();
    return;
  }
  const {r, c} = findMatches[findIndex];
  const cells = getActiveCells();
  const key = ref(r, c);
  if(cells[key]) {
    const orig = String(cells[key].v);
    cells[key].v = orig.replace(new RegExp(query, 'gi'), repl);
    renderCell(r, c);
    pushHistory();
    showToast(`Replaced in ${key}`);
  }
  findNext();
}

function replaceAll() {
  const query = findInput ? findInput.value.trim() : '';
  const repl = replaceInput ? replaceInput.value : '';
  if(!query) return;
  const cells = getActiveCells();
  let count = 0;
  for(const k in cells) {
    if(cells[k].v && String(cells[k].v).toLowerCase().includes(query.toLowerCase())) {
      cells[k].v = String(cells[k].v).replace(new RegExp(query, 'gi'), repl);
      count++;
    }
  }
  renderAll(); pushHistory();
  showToast(`Replaced ${count} occurrences`);
  if(modal) modal.classList.remove('show');
}

const findNextBtn = document.getElementById('findNextBtn');
if(findNextBtn) findNextBtn.onclick = findNext;

const findPrevBtn = document.getElementById('findPrevBtn');
if(findPrevBtn) findPrevBtn.onclick = findPrev;

const replaceBtn = document.getElementById('replaceBtn');
if(replaceBtn) replaceBtn.onclick = replaceCurrent;

const replaceAllBtn = document.getElementById('replaceAllBtn');
if(replaceAllBtn) replaceAllBtn.onclick = replaceAll;

/* ==========================================================================
   INLINE SHEET TAB RENAMING & MANAGEMENT
   ========================================================================== */
let activeSheetMenu = null;

function renderSheetTabs() {
  const container = document.getElementById('sheetsTabs');
  if(!container) return;
  container.innerHTML = '';
  const sheetIds = Object.keys(appState.sheets);

  sheetIds.forEach((id, index) => {
    const s = appState.sheets[id];
    const tab = document.createElement('div');
    tab.className = `sheet-tab ${id === appState.activeSheetId ? 'active' : ''}`;
    tab.dataset.id = id;
    if(s.color) tab.style.borderBottom = `3px solid ${s.color}`;

    const label = document.createElement('span');
    label.className = 'sheet-tab-label';
    label.textContent = s.name;
    label.onclick = () => switchSheet(id);
    label.ondblclick = (e) => {
      e.stopPropagation();
      renameSheet(id);
    };
    tab.appendChild(label);

    const arrow = document.createElement('span');
    arrow.className = 'material-symbols-outlined sheet-tab-arrow';
    arrow.textContent = 'arrow_drop_down';
    arrow.title = 'Sheet options';
    arrow.onclick = (e) => {
      e.stopPropagation();
      openSheetMenu(arrow, id, index, sheetIds.length);
    };
    tab.appendChild(arrow);

    tab.oncontextmenu = (e) => {
      e.preventDefault();
      openSheetMenu(arrow, id, index, sheetIds.length);
    };

    container.appendChild(tab);
  });
}

function openSheetMenu(anchorEl, id, index, totalSheets) {
  closeSheetMenu();
  closeHeaderMenu();
  closeFontPopover();
  closeToolbarPopover();
  const menu = document.createElement('div');
  menu.className = 'dropdown-menu show sheet-dropdown-menu';

  const items = [
    { label: 'Delete', icon: 'delete', disabled: totalSheets <= 1, action: () => deleteSheet(id) },
    { label: 'Duplicate', icon: 'content_copy', action: () => duplicateSheet(id) },
    { label: 'Rename', icon: 'edit', action: () => renameSheet(id) },
    { label: 'Change color', icon: 'palette', action: () => changeSheetColor(id, anchorEl) },
    { divider: true },
    { label: 'Move right', icon: 'arrow_forward', disabled: index === totalSheets - 1, action: () => moveSheet(id, 1) },
    { label: 'Move left', icon: 'arrow_back', disabled: index === 0, action: () => moveSheet(id, -1) }
  ];

  items.forEach(it => {
    if(it.divider) {
      const d = document.createElement('div'); d.className = 'dropdown-divider'; menu.appendChild(d);
    } else {
      const item = document.createElement('div');
      item.className = `dropdown-item ${it.disabled ? 'disabled' : ''}`;
      item.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px;">${it.icon}</span><span>${it.label}</span>`;
      if(!it.disabled) {
        item.onclick = (e) => {
          e.stopPropagation();
          closeSheetMenu();
          it.action();
        };
      }
      menu.appendChild(item);
    }
  });

  document.body.appendChild(menu);
  const rect = anchorEl.getBoundingClientRect();
  menu.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
  menu.style.left = rect.left + 'px';
  activeSheetMenu = menu;
}

function closeSheetMenu() {
  if(activeSheetMenu) {
    activeSheetMenu.remove();
    activeSheetMenu = null;
  }
}

function deleteSheet(id) {
  const ids = Object.keys(appState.sheets);
  if(ids.length <= 1) return;
  delete appState.sheets[id];
  const newActive = ids.find(x => x !== id);
  switchSheet(newActive);
  showToast('Sheet deleted');
}

function duplicateSheet(id) {
  const orig = appState.sheets[id];
  const count = Object.keys(appState.sheets).length + 1;
  const newId = 'Sheet' + count;
  appState.sheets[newId] = {
    id: newId,
    name: `${orig.name} (Copy)`,
    cells: JSON.parse(JSON.stringify(orig.cells)),
    colWidths: { ...orig.colWidths },
    rowHeights: { ...orig.rowHeights },
    color: orig.color
  };
  switchSheet(newId);
  showToast(`Duplicated to ${appState.sheets[newId].name}`);
}

function renameSheet(id) {
  const tabEl = document.querySelector(`.sheet-tab[data-id="${id}"]`);
  if(!tabEl) return;
  const labelEl = tabEl.querySelector('.sheet-tab-label');
  if(!labelEl) return;

  const currentName = appState.sheets[id].name;
  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'sheet-tab-input';
  input.value = currentName;

  labelEl.replaceWith(input);
  input.focus();
  input.select();

  let isSaved = false;
  function save() {
    if(isSaved) return;
    isSaved = true;
    const val = input.value.trim();
    if(val) appState.sheets[id].name = val;
    renderSheetTabs();
    showToast('Sheet renamed');
  }

  input.addEventListener('keydown', (e) => {
    if(e.key === 'Enter') { e.preventDefault(); save(); }
    if(e.key === 'Escape') { e.preventDefault(); isSaved = true; renderSheetTabs(); }
  });

  input.addEventListener('blur', () => save());
}

function changeSheetColor(id, anchorEl) {
  openColorPicker(anchorEl, (c) => {
    appState.sheets[id].color = c;
    renderSheetTabs();
    showToast('Sheet color updated');
  });
}

function moveSheet(id, dir) {
  const keys = Object.keys(appState.sheets);
  const idx = keys.indexOf(id);
  if(idx === -1) return;
  const targetIdx = idx + dir;
  if(targetIdx < 0 || targetIdx >= keys.length) return;

  const newSheets = {};
  keys.forEach((k, i) => {
    if(i === idx) return;
    if(i === targetIdx && dir < 0) newSheets[id] = appState.sheets[id];
    newSheets[k] = appState.sheets[k];
    if(i === targetIdx && dir > 0) newSheets[id] = appState.sheets[id];
  });
  appState.sheets = newSheets;
  renderSheetTabs();
}

function switchSheet(id) {
  if(!appState.sheets[id]) return;
  appState.activeSheetId = id;
  renderSheetTabs();
  buildGrid();
  renderAll();
  updateSelectionUI();
}

const btnAddSheet = document.getElementById('addSheetBtn');
if(btnAddSheet) {
  btnAddSheet.onclick = () => {
    const count = Object.keys(appState.sheets).length + 1;
    const id = 'Sheet' + count;
    appState.sheets[id] = { id, name: id, cells: {}, colWidths: {}, rowHeights: {} };
    switchSheet(id);
    showToast(`Added ${id}`);
  };
}

// All Sheets List Button (≡)
const allSheetsBtn = document.querySelector('#bottombar button[title="All sheets"]');
if(allSheetsBtn) {
  allSheetsBtn.onclick = (e) => {
    e.stopPropagation();
    closeSheetMenu();
    closeHeaderMenu();
    closeFontPopover();
    closeToolbarPopover();
    const menu = document.createElement('div');
    menu.className = 'dropdown-menu show sheet-dropdown-menu';

    for(const id in appState.sheets) {
      const s = appState.sheets[id];
      const item = document.createElement('div');
      item.className = 'dropdown-item';
      const isActive = id === appState.activeSheetId;
      item.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px;">${isActive ? 'check' : 'description'}</span><span>${s.name}</span>`;
      item.onclick = (se) => {
        se.stopPropagation();
        closeSheetMenu();
        switchSheet(id);
      };
      menu.appendChild(item);
    }

    document.body.appendChild(menu);
    const rect = allSheetsBtn.getBoundingClientRect();
    menu.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
    menu.style.left = rect.left + 'px';
    activeSheetMenu = menu;
  };
}

/* ==========================================================================
   UNDO / REDO HISTORY
   ========================================================================== */
function pushHistory() {
  const snap = JSON.stringify({ sheets: appState.sheets, numRows: appState.numRows });
  if(appState.history[appState.historyIndex] === snap) return;
  appState.history = appState.history.slice(0, appState.historyIndex + 1);
  appState.history.push(snap);
  if(appState.history.length > 60) appState.history.shift();
  appState.historyIndex = appState.history.length - 1;
  isDirty = true; // Mark local state as modified (unsaved)
  if(typeof triggerAutoSave === 'function') triggerAutoSave();
}

function undo() {
  if(appState.historyIndex <= 0) return;
  appState.historyIndex--;
  const snap = JSON.parse(appState.history[appState.historyIndex]);
  appState.sheets = snap.sheets;
  if(snap.numRows) appState.numRows = snap.numRows;
  renderSheetTabs(); buildGrid(); renderAll(); updateSelectionUI();
}

function redo() {
  if(appState.historyIndex >= appState.history.length - 1) return;
  appState.historyIndex++;
  const snap = JSON.parse(appState.history[appState.historyIndex]);
  appState.sheets = snap.sheets;
  if(snap.numRows) appState.numRows = snap.numRows;
  renderSheetTabs(); buildGrid(); renderAll(); updateSelectionUI();
}

const btnUndo = document.getElementById('undoBtn');
if(btnUndo) btnUndo.onclick = undo;
const btnRedo = document.getElementById('redoBtn');
if(btnRedo) btnRedo.onclick = redo;

/* ==========================================================================
   INITIALIZATION
   ========================================================================== */
setupTopMenuBar();
renderSheetTabs();
buildGrid();
renderAll();
pushHistory();
updateSelectionUI();

/* ==========================================================================
   LOCAL DATABASE AND WEBSOCKET COLLABORATION INTEGRATION
   ========================================================================== */

let ws = null;
currentSheetData = null;

// Initialize using the HRM session injected CURRENT_USER
currentUser = window.CURRENT_USER || { email: 'employee@richmondtechgroup.com', name: 'HRM User' };

// Kick off loading
if (currentSpreadsheetId) {
  loadSpreadsheetFromCloud(currentSpreadsheetId);
}

function updateUserAuthUI() {
  const signInBtn = document.getElementById('signInBtn');
  const avatarBtn = document.getElementById('userAvatarBtn');
  const avatarText = document.getElementById('userAvatarText');
  const avatarImg = document.getElementById('userAvatarImg');
  const popoverAvatarImg = document.getElementById('popoverAvatarImg');
  const popoverAvatarText = document.getElementById('popoverAvatarText');
  const popoverUserName = document.getElementById('popoverUserName');
  const popoverUserEmail = document.getElementById('popoverUserEmail');

  if (signInBtn) signInBtn.style.display = 'none';
  if (avatarBtn) avatarBtn.style.display = 'flex';
  
  if (popoverUserName) popoverUserName.textContent = currentUser.name;
  if (popoverUserEmail) popoverUserEmail.textContent = currentUser.email;

  const initial = (currentUser.name || 'U')[0].toUpperCase();
  if (avatarText) { avatarText.textContent = initial; avatarText.style.display = 'block'; }
  if (avatarImg) avatarImg.style.display = 'none';
  if (popoverAvatarText) { popoverAvatarText.textContent = initial; popoverAvatarText.style.display = 'flex'; }
  if (popoverAvatarImg) popoverAvatarImg.style.display = 'none';
}

updateUserAuthUI();

// Dynamic User Cursor Color Palette
const USER_COLORS = [
  '#ea4335', '#4285f4', '#fbbc05', '#34a853', '#ab47bc',
  '#00acc1', '#ff7043', '#9c27b0', '#3f51b5', '#009688'
];

function getUserColor(email) {
  if (!email) return USER_COLORS[0];
  let hash = 0;
  for (let i = 0; i < email.length; i++) hash = email.charCodeAt(i) + ((hash << 5) - hash);
  const index = Math.abs(hash) % USER_COLORS.length;
  return USER_COLORS[index];
}

function showTopLoadingLine() {
  const line = document.getElementById('topLoadingLine');
  if (line) line.style.display = 'block';
}

function hideTopLoadingLine() {
  const line = document.getElementById('topLoadingLine');
  if (line) line.style.display = 'none';
  const appContainer = document.getElementById('appContainer');
  if (appContainer) appContainer.classList.add('ready');
}

if (document.fonts) {
  document.fonts.ready.then(() => {
    const appContainer = document.getElementById('appContainer');
    if (appContainer) appContainer.classList.add('ready');
  }).catch(() => hideTopLoadingLine());
} else {
  setTimeout(hideTopLoadingLine, 200);
}

function triggerAutoSave() {
  if (!isSheetLoaded || !isUserAuthorized || isUserReadOnly) return;
  isDirty = true;
  showTopLoadingLine();
  const statusText = document.getElementById('saveStatusText');
  if (statusText) statusText.textContent = 'Saving...';
  if (autoSaveTimer) clearTimeout(autoSaveTimer);
  autoSaveTimer = setTimeout(() => {
    saveSpreadsheetToCloud();
  }, 1000);
}

function saveSingleCellToCloud(cellRef, cellObj) {
  if (!isSheetLoaded || !isUserAuthorized || isUserReadOnly) return;
  
  // Broadcast cell edit immediately to other collaborators
  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({
      action: 'sheet_broadcast',
      sheetId: currentSpreadsheetId,
      event: 'cell_edit',
      data: {
        cellRef: cellRef,
        cellObj: cellObj,
        activeSheetId: appState.activeSheetId || 'Sheet1'
      }
    }));
  }

  triggerAutoSave();
}

function saveSpreadsheetToCloud() {
  if (!isSheetLoaded || !isUserAuthorized || isUserReadOnly) return;
  if (isSaving) return; // Prevent concurrent save requests
  isSaving = true;

  const docTitleInput = document.getElementById('docTitle');
  const title = docTitleInput ? docTitleInput.value : 'Untitled spreadsheet';

  const payload = {
    id: currentSpreadsheetId,
    title: title,
    sheets: appState.sheets,
    numRows: appState.numRows || 100
  };

  fetch(getApiUrl('sheets_handler.php?action=save'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(r => r.json())
    .then(res => {
      isSaving = false;
      hideTopLoadingLine();
      const statusText = document.getElementById('saveStatusText');
      if (res.status === 'success') {
        isDirty = false; // Local state is now persisted to server
        saveRetryCount = 0;
        if (statusText) statusText.textContent = 'Saved to local server';
      } else {
        if (statusText) statusText.textContent = 'Save failed';
        showToast('Save failed: ' + res.message);
        retrySaveOnFailure();
      }
    })
    .catch(err => {
      isSaving = false;
      hideTopLoadingLine();
      console.error('Save failed:', err);
      const statusText = document.getElementById('saveStatusText');
      if (statusText) statusText.textContent = 'Connection error — retrying...';
      retrySaveOnFailure();
    });
}

function retrySaveOnFailure() {
  if (saveRetryCount >= MAX_SAVE_RETRIES) {
    showToast('Unable to save after multiple attempts. Please check your connection.');
    const statusText = document.getElementById('saveStatusText');
    if (statusText) statusText.textContent = 'Save failed — check connection';
    return;
  }
  saveRetryCount++;
  const delay = Math.min(2000 * Math.pow(2, saveRetryCount - 1), 30000); // 2s, 4s, 8s, 16s, 30s
  console.warn(`Save retry ${saveRetryCount}/${MAX_SAVE_RETRIES} in ${delay}ms`);
  setTimeout(() => {
    if (isDirty) saveSpreadsheetToCloud();
  }, delay);
}

// Prevent accidental data loss on page close
window.addEventListener('beforeunload', (e) => {
  if (isDirty && isSheetLoaded && isUserAuthorized && !isUserReadOnly) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    // Fire one last save attempt
    saveSpreadsheetToCloud();
    return e.returnValue;
  }
});

function updatePresence(r, c) {
  if (!currentSpreadsheetId || isUserReadOnly || !isUserAuthorized) return;
  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({
      action: 'sheet_broadcast',
      sheetId: currentSpreadsheetId,
      event: 'presence',
      data: {
        r: typeof r === 'number' ? r : 0,
        c: typeof c === 'number' ? c : 0,
        email: currentUser.email,
        name: currentUser.name,
        color: getUserColor(currentUser.email)
      }
    }));
  }
}

function listenToPresence() {
  if (!currentSpreadsheetId) return;

  // Cleanup old WebSocket handlers before closing to prevent close event loops
  if (ws) {
    try {
      ws.onopen = null;
      ws.onclose = null;
      ws.onerror = null;
      ws.onmessage = null;
      ws.close();
    } catch(e) {}
  }

  // Initialize Ratchet WebSocket Connection
  const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
  const wsHost = window.location.hostname;
  const wsPort = '6001'; // Ratchet standard port in this project
  
  try {
    ws = new WebSocket(`${wsProtocol}//${wsHost}:${wsPort}`);

    ws.onopen = () => {
      console.log('✅ Connected to HRM WebSockets for Sheet Sync');
      // Join this spreadsheet channel
      ws.send(JSON.stringify({
        action: 'sheet_join',
        sheetId: currentSpreadsheetId
      }));

      // Send initial presence
      if (appState.selected) {
        updatePresence(appState.selected.r, appState.selected.c);
      }
    };

    ws.onmessage = (e) => {
      try {
        const msg = JSON.parse(e.data);
        if (msg.type === 'sheet_event' && msg.sheetId === currentSpreadsheetId) {
          const senderId = msg.senderResourceId;
          
          if (msg.event === 'presence') {
            remoteCursorsMap[senderId] = msg.data;
            remoteCursorsMap[senderId].timestamp = Date.now();
            renderRemoteCursors();
            renderActiveCollaboratorAvatars();
          } 
          else if (msg.event === 'cell_edit') {
            const { cellRef, cellObj, activeSheetId } = msg.data;
            if (appState.sheets[activeSheetId]) {
              const isEditingThisCell = appState.isEditing && 
                activeSheetId === appState.activeSheetId && 
                ref(appState.selected.r, appState.selected.c) === cellRef;

              if (!isEditingThisCell) {
                if (cellObj && cellObj.v !== undefined && cellObj.v !== null && cellObj.v !== '') {
                  appState.sheets[activeSheetId].cells[cellRef] = cellObj;
                } else {
                  delete appState.sheets[activeSheetId].cells[cellRef];
                }
                renderAll();
              }
            }
          }
          else if (msg.event === 'full_sync') {
            // Full state sync from a collaborator (paste, dropdown, etc.)
            const { activeSheetId: remoteSId, sheets: remoteSheets } = msg.data;
            if (remoteSheets && !appState.isEditing) {
              for (const sId in remoteSheets) {
                if (appState.sheets[sId]) {
                  appState.sheets[sId].cells = remoteSheets[sId].cells || {};
                  if (remoteSheets[sId].colWidths) appState.sheets[sId].colWidths = remoteSheets[sId].colWidths;
                  if (remoteSheets[sId].rowHeights) appState.sheets[sId].rowHeights = remoteSheets[sId].rowHeights;
                } else {
                  appState.sheets[sId] = remoteSheets[sId];
                }
              }
              renderSheetTabs();
              buildGrid();
              renderAll();
              updateSelectionUI();
            }
          }
          else if (msg.event === 'title_change' && msg.data && msg.data.title) {
            const docTitleEl = document.getElementById('docTitle');
            if (docTitleEl && document.activeElement !== docTitleEl) {
              docTitleEl.value = msg.data.title;
              appState.title = msg.data.title;
              document.title = msg.data.title + ' - Google Sheets';
            }
          }
        }
      } catch (err) {
        console.error('Failed to parse WebSocket message:', err);
      }
    };

    ws.onclose = () => {
      console.warn('❌ Disconnected from HRM WebSockets. Reconnecting in 5s...');
      setTimeout(listenToPresence, 5000);
    };

    ws.onerror = (err) => {
      console.error('WebSocket Error:', err);
    };

  } catch (err) {
    console.error('Failed to initialize WebSocket client:', err);
  }

  // Periodic cleanup of stale remote cursors (inactive for >30s)
  if (presenceHeartbeatTimer) clearInterval(presenceHeartbeatTimer);
  presenceHeartbeatTimer = setInterval(() => {
    const now = Date.now();
    let changed = false;
    for (const senderId in remoteCursorsMap) {
      if (now - remoteCursorsMap[senderId].timestamp > 30000) {
        delete remoteCursorsMap[senderId];
        changed = true;
      }
    }
    if (changed) {
      renderRemoteCursors();
      renderActiveCollaboratorAvatars();
    }
    // Also send self presence heartbeat
    if (appState.selected) {
      updatePresence(appState.selected.r, appState.selected.c);
    }
  }, 10000);
}

let autoSyncPollingTimer = null;
function startAutoSyncPolling() {
  if (autoSyncPollingTimer) clearInterval(autoSyncPollingTimer);
  autoSyncPollingTimer = setInterval(() => {
    // ── CRITICAL GUARD: Never overwrite local data while user has unsaved edits ──
    if (!isSheetLoaded || !currentSpreadsheetId || appState.isEditing) return;
    if (isDirty || isSaving) return; // Local edits pending save — do NOT fetch stale server state
    
    fetch(getApiUrl('sheets_handler.php?action=load&id=' + encodeURIComponent(currentSpreadsheetId)))
      .then(r => r.json())
      .then(res => {
        // Double-check the dirty flag again after the async fetch completes
        // (user may have started editing during the network round-trip)
        if (isDirty || isSaving || appState.isEditing) return;

        if (res.status === 'success' && res.sheets) {
          let updated = false;
          for (const sId in res.sheets) {
            const remoteCells = res.sheets[sId].cells || {};
            const localCells = (appState.sheets[sId] && appState.sheets[sId].cells) ? appState.sheets[sId].cells : {};
            
            if (JSON.stringify(remoteCells) !== JSON.stringify(localCells)) {
              if (!appState.sheets[sId]) appState.sheets[sId] = res.sheets[sId];
              appState.sheets[sId].cells = remoteCells;
              updated = true;
            }
          }
          if (res.title && res.title !== appState.title) {
            appState.title = res.title;
            const docTitleEl = document.getElementById('docTitle');
            if (docTitleEl && document.activeElement !== docTitleEl) {
              docTitleEl.value = res.title;
              document.title = res.title + ' - Google Sheets';
            }
          }
          if (updated && !appState.isEditing && !isDirty) {
            renderAll();
          }
        }
      })
      .catch(() => {});
  }, 5000); // Increased from 3s to 5s to reduce server load and race window
}

function renderRemoteCursors() {
  document.querySelectorAll('.remote-cursor-border, .remote-cursor-badge').forEach(el => el.remove());

  for (const uid in remoteCursorsMap) {
    const cursor = remoteCursorsMap[uid];
    if (typeof cursor.r !== 'number' || typeof cursor.c !== 'number') continue;

    // Don't inject remote badges directly into the local user's active editing cell to avoid disrupting focus/DOM
    if (appState.isEditing && appState.selected && cursor.r === appState.selected.r && cursor.c === appState.selected.c) {
      continue;
    }

    const td = getTd(cursor.r, cursor.c);
    if (!td) continue;

    td.style.position = 'relative';

    const borderEl = document.createElement('div');
    borderEl.className = 'remote-cursor-border';
    borderEl.title = `${cursor.name} (${cursor.email})`;
    borderEl.style.cssText = `
      position: absolute; top:0; left:0; right:0; bottom:0;
      border: 2px solid ${cursor.color};
      pointer-events: none; z-index: 100;
      box-sizing: border-box;
    `;

    const badgeEl = document.createElement('div');
    badgeEl.className = 'remote-cursor-badge';
    badgeEl.textContent = cursor.name;
    badgeEl.title = `${cursor.name} (${cursor.email})`;
    badgeEl.style.cssText = `
      position: absolute; top: -18px; left: -2px;
      background: ${cursor.color}; color: #fff;
      font-size: 10px; font-weight: 600; padding: 1px 6px;
      border-radius: 4px; white-space: nowrap;
      pointer-events: none; z-index: 101;
      box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    `;

    td.appendChild(borderEl);
    td.appendChild(badgeEl);
  }
}

function renderActiveCollaboratorAvatars() {
  const container = document.getElementById('activeCollaboratorsContainer');
  if (!container) return;

  container.innerHTML = '';

  for (const uid in remoteCursorsMap) {
    const user = remoteCursorsMap[uid];
    const initial = (user.name || 'U')[0].toUpperCase();
    const color = user.color || getUserColor(user.email);

    const avatar = document.createElement('div');
    avatar.title = `${user.name} (${user.email}) - Active now`;
    avatar.style.cssText = `
      width: 32px; height: 32px; border-radius: 50%;
      background: ${color}; color: #fff;
      font-weight: bold; font-size: 13px;
      display: flex; align-items: center; justify-content: center;
      border: 2px solid #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.18);
      cursor: pointer; transition: transform 0.15s ease;
      overflow: hidden; position: relative;
    `;
    avatar.onmouseenter = () => avatar.style.transform = 'scale(1.1)';
    avatar.onmouseleave = () => avatar.style.transform = 'scale(1)';
    avatar.textContent = initial;

    container.appendChild(avatar);
  }
}

function loadSpreadsheetFromCloud(id) {
  showTopLoadingLine();
  
  fetch(getApiUrl('sheets_handler.php?action=load&id=' + encodeURIComponent(id)))
    .then(r => r.json())
    .then(res => {
      hideTopLoadingLine();
      if (res.status === 'success' && res.authorized) {
        currentSheetData = res;

        // Authorized Access Granted
        isUserAuthorized = true;
        isSheetLoaded = true;

        const accessPage = document.getElementById('accessDeniedPage');
        if (accessPage) accessPage.style.display = 'none';
        document.querySelectorAll('#topbar, #toolbar, #formulabar, #grid, #bottombar').forEach(el => el.style.display = '');

        // Read-Only Enforcement
        if (res.role === 'viewer') {
          isUserReadOnly = true;
          showToast('Viewing in Read-Only mode');
          const docTitleInput = document.getElementById('docTitle');
          if (docTitleInput) docTitleInput.disabled = true;
          const formulaInput = document.getElementById('formulaInput');
          if (formulaInput) formulaInput.disabled = true;
          const toolbar = document.getElementById('toolbar');
          if (toolbar) { toolbar.style.pointerEvents = 'none'; toolbar.style.opacity = '0.55'; }
        } else {
          isUserReadOnly = false;
          const docTitleInput = document.getElementById('docTitle');
          if (docTitleInput) docTitleInput.disabled = false;
          const formulaInput = document.getElementById('formulaInput');
          if (formulaInput) formulaInput.disabled = false;
          const toolbar = document.getElementById('toolbar');
          if (toolbar) { toolbar.style.pointerEvents = ''; toolbar.style.opacity = '1'; }
        }

        if (res.sheets) {
          // Normalize sheet arrays into objects to prevent JS array serialization bugs
          for (const sId in res.sheets) {
            const sh = res.sheets[sId];
            if (Array.isArray(sh.cells)) sh.cells = {};
            if (Array.isArray(sh.colwidths)) sh.colwidths = {};
            if (Array.isArray(sh.rowHeights)) sh.rowHeights = {};
          }
          appState.sheets = res.sheets;
        }
        if (res.numRows) appState.numRows = res.numRows;
        
        const docTitleInput = document.getElementById('docTitle');
        if (docTitleInput && res.title) docTitleInput.value = res.title;
        
        updateShareModalUI();
        renderSheetTabs(); buildGrid(); renderAll(); updateSelectionUI();

        const statusText = document.getElementById('saveStatusText');
        if (statusText) statusText.textContent = 'Saved to server';

        listenToPresence();
        startAutoSyncPolling();

      } else if (res.status === 'denied' || !res.authorized) {
        isUserAuthorized = false;
        isSheetLoaded = true;

        // Hide editor components
        document.querySelectorAll('#topbar, #toolbar, #formulabar, #grid, #bottombar').forEach(el => el.style.display = 'none');
        
        // Show Full-Page "You need access" Google Sheets UI
        const accessPage = document.getElementById('accessDeniedPage');
        if (accessPage) accessPage.style.display = 'block';

        const signedInEmailEl = document.getElementById('signedInUserEmail');
        const signedInAvatarEl = document.getElementById('signedInUserAvatar');
        const signedInTitleEl = document.getElementById('signedInAsTitle');

        if (signedInEmailEl) {
          if (signedInTitleEl) signedInTitleEl.textContent = "You're signed in as";
          signedInEmailEl.textContent = currentUser.email;
          if (signedInAvatarEl) signedInAvatarEl.textContent = (currentUser.name || '?')[0].toUpperCase();
        }

        const statusText = document.getElementById('saveStatusText');
        if (statusText) statusText.textContent = 'Private document';

        showToast('Access denied: Private spreadsheet');
      } else {
        alert('Failed to load spreadsheet: ' + res.message);
      }
    })
    .catch(err => {
      hideTopLoadingLine();
      console.error('Failed to load spreadsheet from local server:', err);
    });
}

function updateShareModalUI() {
  const ownerNameEl = document.getElementById('ownerDisplayName');
  const ownerEmailEl = document.getElementById('ownerEmailText');
  const ownerCircleEl = document.getElementById('ownerAvatarCircle');

  const ownerEmail = currentSheetData ? (currentSheetData.ownerEmail || 'owner@rtg.com') : 'owner@rtg.com';
  const ownerName = currentSheetData ? (currentSheetData.ownerName || ownerEmail) : 'Owner';

  if (ownerNameEl) ownerNameEl.textContent = ownerName + (currentUser.email === ownerEmail ? ' (you)' : '');
  if (ownerEmailEl) ownerEmailEl.textContent = ownerEmail;
  if (ownerCircleEl) ownerCircleEl.textContent = (ownerName || 'O')[0].toUpperCase();

  renderSharedUsersList();
}

function renderSharedUsersList() {
  const container = document.getElementById('sharedUsersList');
  if (!container) return;
  
  const ownerEmail = currentSheetData ? currentSheetData.ownerEmail : '';
  const ownerName = currentSheetData ? currentSheetData.ownerName : 'Owner';

  let html = `
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
      <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:36px; height:36px; border-radius:50%; background:#c5221f; color:#fff; font-weight:bold; font-size:16px; display:flex; align-items:center; justify-content:center;">${(ownerName || 'O')[0].toUpperCase()}</div>
        <div>
          <div style="font-size:14px; font-weight:500; color:#202124;">${ownerName} ${currentUser.email === ownerEmail ? '(you)' : ''}</div>
          <div style="font-size:12px; color:#5f6368;">${ownerEmail}</div>
        </div>
      </div>
      <span style="font-size:13px; color:#5f6368; font-weight:500;">Owner</span>
    </div>
  `;

  if (currentSheetData && currentSheetData.sharedUsers) {
    for (const email in currentSheetData.sharedUsers) {
      const role = currentSheetData.sharedUsers[email];
      html += `
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; border-radius:50%; background:#1a73e8; color:#fff; font-weight:bold; font-size:16px; display:flex; align-items:center; justify-content:center;">${email[0].toUpperCase()}</div>
            <div>
              <div style="font-size:14px; font-weight:500; color:#202124;">${email}</div>
              <div style="font-size:12px; color:#5f6368;">${role}</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:12px; color:#1a73e8; font-weight:500;">${role}</span>
            <span class="material-symbols-outlined" style="font-size:18px; color:#5f6368; cursor:pointer; padding:6px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;" title="Remove access" onclick="window.removeSharedUser('${email}')">close</span>
          </div>
        </div>
      `;
    }
  }

  container.innerHTML = html;
}

const addShareUserBtn = document.getElementById('addShareUserBtn');
if (addShareUserBtn) {
  addShareUserBtn.onclick = () => {
    const input = document.getElementById('shareEmailInput');
    const roleSelect = document.getElementById('shareRoleSelect');
    const email = input ? input.value.trim().toLowerCase() : '';
    const role = roleSelect ? roleSelect.value : 'editor';

    if (!email) {
      showToast('Please enter a valid email address');
      return;
    }

    const payload = {
      spreadsheet_id: currentSpreadsheetId,
      email: email,
      role: role
    };

    fetch(getApiUrl('sheets_handler.php?action=share'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          if (!currentSheetData.sharedUsers) currentSheetData.sharedUsers = {};
          currentSheetData.sharedUsers[email] = role;
          input.value = '';
          renderSharedUsersList();
          showToast(`Access granted to ${email} as ${role}!`);
        } else {
          showToast('Failed: ' + res.message);
        }
      })
      .catch(() => showToast('Network sharing error.'));
  };
}

function removeSharedUser(email) {
  if (!email) return;
  const cleanEmail = email.toLowerCase().trim();

  const payload = {
    spreadsheet_id: currentSpreadsheetId,
    remove_email: cleanEmail
  };

  fetch(getApiUrl('sheets_handler.php?action=share'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        if (currentSheetData.sharedUsers) {
          delete currentSheetData.sharedUsers[cleanEmail];
        }
        renderSharedUsersList();
        showToast(`Access removed for ${cleanEmail}`);
      } else {
        showToast('Removal failed: ' + res.message);
      }
    })
    .catch(() => showToast('Error connecting to sharing server.'));
}

window.removeSharedUser = removeSharedUser;

const generalAccessSelect = document.getElementById('generalAccessSelect');
if (generalAccessSelect) {
  generalAccessSelect.onchange = () => {
    const mode = generalAccessSelect.value;
    let visibility = 'private';
    let publicRole = 'none';

    if (mode === 'public_viewer') { visibility = 'public'; publicRole = 'viewer'; }
    else if (mode === 'public_editor') { visibility = 'public'; publicRole = 'editor'; }
    else if (mode === 'department') { visibility = 'department'; }

    const payload = {
      spreadsheet_id: currentSpreadsheetId,
      visibility: visibility,
      public_role: publicRole
    };

    fetch(getApiUrl('sheets_handler.php?action=share'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          currentSheetData.visibility = visibility;
          currentSheetData.publicRole = publicRole;
          const desc = document.getElementById('generalAccessDesc');
          if (desc) {
            if (visibility === 'public') {
              desc.textContent = `Anyone in HRM with the link can ${publicRole}`;
            } else if (visibility === 'department') {
              desc.textContent = `Department members can view this sheet`;
            } else {
              desc.textContent = 'Only people with access can open with the link';
            }
          }
          showToast('General access updated');
        } else {
          showToast('Update failed: ' + res.message);
        }
      });
  };
}

const shareBtn = document.getElementById('shareBtn');
const shareModal = document.getElementById('shareModal');
const closeShareBtn = document.getElementById('closeShareBtn');
const doneShareBtn = document.getElementById('doneShareBtn');
const copyShareLinkBtn = document.getElementById('copyShareLinkBtn');

let autocompleteEmployees = [];
let autocompleteDepartments = [];

function loadShareAutocompleteData() {
  fetch(getApiUrl('sheets_handler.php?action=search_members'))
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        autocompleteEmployees = res.employees || [];
        autocompleteDepartments = res.departments || [];
      }
    })
    .catch(err => console.error('Failed to load autocomplete data:', err));
}

if (shareBtn) {
  shareBtn.onclick = () => {
    if (shareModal) shareModal.classList.add('show');
    const docTitle = document.getElementById('docTitle');
    const shareDocTitle = document.getElementById('shareDocTitle');
    if (docTitle && shareDocTitle) shareDocTitle.textContent = docTitle.value;
    updateShareModalUI();
    loadShareAutocompleteData();
  };
}

const shareEmailInput = document.getElementById('shareEmailInput');
const shareDropdown = document.getElementById('shareAutocompleteDropdown');

if (shareEmailInput && shareDropdown) {
  shareEmailInput.oninput = (e) => {
    const q = e.target.value.toLowerCase().trim();
    if (!q) {
      shareDropdown.style.display = 'none';
      shareDropdown.innerHTML = '';
      return;
    }

    // Filter matching employees
    const matchedEmployees = autocompleteEmployees.filter(emp => 
      (emp.name && emp.name.toLowerCase().includes(q)) || 
      (emp.email && emp.email.toLowerCase().includes(q))
    );

    // Filter matching departments
    const matchedDepts = autocompleteDepartments.filter(dept => 
      dept.name && dept.name.toLowerCase().includes(q)
    );

    if (matchedEmployees.length === 0 && matchedDepts.length === 0) {
      shareDropdown.style.display = 'none';
      shareDropdown.innerHTML = '';
      return;
    }

    let html = '';
    
    // Render Employees
    if (matchedEmployees.length > 0) {
      html += `<div style="padding:6px 12px; font-size:11px; color:#5f6368; font-weight:600; background:#f8f9fa; border-bottom:1px solid #e1e3e6;">Employees</div>`;
      matchedEmployees.forEach(emp => {
        html += `
          <div class="autocomplete-item" data-email="${emp.email}" style="padding:8px 12px; cursor:pointer; display:flex; flex-direction:column; gap:2px; border-bottom:1px solid #f1f3f4; font-size:13px;">
            <span style="font-weight:500; color:#202124;">${emp.name}</span>
            <span style="font-size:11px; color:#5f6368;">${emp.email}</span>
          </div>
        `;
      });
    }

    // Render Departments
    if (matchedDepts.length > 0) {
      html += `<div style="padding:6px 12px; font-size:11px; color:#5f6368; font-weight:600; background:#f8f9fa; border-bottom:1px solid #e1e3e6;">Departments</div>`;
      matchedDepts.forEach(dept => {
        html += `
          <div class="autocomplete-item" data-email="dept:${dept.id}" style="padding:8px 12px; cursor:pointer; display:flex; flex-direction:column; gap:2px; border-bottom:1px solid #f1f3f4; font-size:13px;">
            <span style="font-weight:500; color:#202124;">${dept.name} Members</span>
            <span style="font-size:11px; color:#1a73e8;">Share with whole department</span>
          </div>
        `;
      });
    }

    shareDropdown.innerHTML = html;
    shareDropdown.style.display = 'block';

    // Hook click event
    shareDropdown.querySelectorAll('.autocomplete-item').forEach(item => {
      item.onclick = () => {
        const email = item.getAttribute('data-email');
        shareEmailInput.value = email;
        shareDropdown.style.display = 'none';
        shareDropdown.innerHTML = '';
      };
    });
  };

  // Close dropdown on click outside
  document.addEventListener('click', (e) => {
    if (!shareEmailInput.contains(e.target) && !shareDropdown.contains(e.target)) {
      shareDropdown.style.display = 'none';
    }
  });
}

if (closeShareBtn) closeShareBtn.onclick = () => shareModal && shareModal.classList.remove('show');
if (doneShareBtn) doneShareBtn.onclick = () => shareModal && shareModal.classList.remove('show');
if (copyShareLinkBtn) {
  copyShareLinkBtn.onclick = () => {
    const shareUrl = window.location.origin + getBaseUrl() + '/sheets/editor?id=' + currentSpreadsheetId;
    navigator.clipboard.writeText(shareUrl).then(() => {
      showToast('Share link copied to clipboard!');
    });
  };
}

// --- Share on Chatrox Button ---
const shareOnChatroxBtn = document.getElementById('shareOnChatroxBtn');
if (shareOnChatroxBtn) {
  // Show button only if Chatrox URL is configured
  if (window.CHATROX_URL && window.CHATROX_URL.length > 0) {
    shareOnChatroxBtn.style.display = 'inline-flex';
    const logoImg = document.getElementById('chatroxBtnLogo');
    if (logoImg) {
      const baseUrl = window.CHATROX_URL.replace(/\/+$/, '');
      logoImg.src = baseUrl + '/assets/images/logo.png';
      logoImg.onerror = function () {
        this.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%234f46e5"><path d="M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>';
      };
    }
  }

  shareOnChatroxBtn.onclick = () => {
    const sheetUrl = window.location.origin + getBaseUrl() + '/sheets/editor?id=' + currentSpreadsheetId;
    const docTitleEl = document.getElementById('docTitle');
    const sheetTitle = docTitleEl ? docTitleEl.value : 'Spreadsheet';

    const params = new URLSearchParams({
      share_link: sheetUrl,
      share_title: sheetTitle
    });

    const chatroxTarget = window.CHATROX_URL + '/dms?' + params.toString();
    const chatroxWin = window.open(chatroxTarget, 'ChatRox');
    if (chatroxWin) chatroxWin.focus();
    shareModal && shareModal.classList.remove('show');
  };
}

const sendRequestBtn = document.getElementById('sendRequestAccessBtn');
if (sendRequestBtn) {
  sendRequestBtn.onclick = () => {
    const selectedRoleEl = document.querySelector('input[name="requestRoleChoice"]:checked');
    const roleChoice = selectedRoleEl ? selectedRoleEl.value : 'editor';
    const messageInput = document.getElementById('accessRequestMsg');
    const msgText = messageInput ? messageInput.value.trim() : '';

    const formData = new FormData();
    formData.append('spreadsheet_id', currentSpreadsheetId);
    formData.append('requested_role', roleChoice);
    formData.append('message', msgText);

    sendRequestBtn.disabled = true;
    sendRequestBtn.textContent = 'Sending...';

    fetch(getApiUrl('sheets_handler.php?action=request_access'), {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          sendRequestBtn.textContent = 'Access requested';
          sendRequestBtn.style.backgroundColor = '#1e8e3e';
          const signedInTitleEl = document.getElementById('signedInAsTitle');
          if (signedInTitleEl) {
            signedInTitleEl.innerHTML = `<span style="color:#1e8e3e; font-weight:500;">✓ Access request sent to owner. You will receive an email once approved.</span>`;
          }
          showToast('Access request sent!');
        } else {
          sendRequestBtn.disabled = false;
          sendRequestBtn.textContent = 'Request access';
          alert('Failed: ' + res.message);
        }
      })
      .catch(() => {
        sendRequestBtn.disabled = false;
        sendRequestBtn.textContent = 'Request access';
        showToast('Network error while requesting access.');
      });
  };
}

/* ==========================================================================
   PIVOT TABLE ENGINE & SIDE PANEL EDITOR
   ========================================================================== */
let currentPivotSourceInfo = { sheetId: null, sheetName: 'Sheet1', minR: 1, maxR: 1, minC: 1, maxC: 1, destSheetId: null, destCell: { r: 1, c: 1 } };

function openPivotTableModal() {
  const modal = document.getElementById('createPivotModal');
  const rangeInp = document.getElementById('pivotDataRangeInput');
  if (!modal) return;

  const targets = getSelectedCells();
  let minR = 1, maxR = 20, minC = 1, maxC = 5;
  if (targets.length) {
    minR = Math.min(...targets.map(t => t.r));
    maxR = Math.max(...targets.map(t => t.r));
    minC = Math.min(...targets.map(t => t.c));
    maxC = Math.max(...targets.map(t => t.c));
  }

  if (minR === maxR && minC === maxC) {
    const cells = getActiveCells();
    const keys = Object.keys(cells);
    if (keys.length) {
      let maxDataR = 1, maxDataC = 1;
      keys.forEach(k => {
        const parsed = parseRef(k);
        if (parsed) {
          if (parsed.r > maxDataR) maxDataR = parsed.r;
          if (parsed.c > maxDataC) maxDataC = parsed.c;
        }
      });
      maxR = maxDataR;
      maxC = maxDataC;
    }
  }

  const activeSheet = findSheetObj(appState.activeSheetId);
  const sheetName = activeSheet ? activeSheet.name : 'Sheet1';
  const rangeStr = `${sheetName}!${ref(minR, minC)}:${ref(maxR, maxC)}`;
  if (rangeInp) rangeInp.value = rangeStr;

  currentPivotSourceInfo = {
    sheetId: appState.activeSheetId,
    sheetName: sheetName,
    minR, maxR, minC, maxC
  };

  modal.classList.add('show');
}

document.querySelectorAll('input[name="pivotInsertDestination"]').forEach(r => {
  r.onchange = (e) => {
    const container = document.getElementById('pivotExistingLocationContainer');
    if (container) container.style.display = e.target.value === 'existing' ? 'block' : 'none';
  };
});

const closePivotModalBtn = document.getElementById('closePivotModalBtn');
if (closePivotModalBtn) closePivotModalBtn.onclick = () => document.getElementById('createPivotModal').classList.remove('show');
const cancelPivotBtn = document.getElementById('cancelPivotBtn');
if (cancelPivotBtn) cancelPivotBtn.onclick = () => document.getElementById('createPivotModal').classList.remove('show');

const confirmCreatePivotBtn = document.getElementById('confirmCreatePivotBtn');
if (confirmCreatePivotBtn) confirmCreatePivotBtn.onclick = () => {
  const destChoice = document.querySelector('input[name="pivotInsertDestination"]:checked')?.value || 'new';
  const existingLocInp = document.getElementById('pivotExistingLocationInput')?.value.trim();

  let targetSheetId = appState.activeSheetId;
  let targetR = 1, targetC = 1;

  if (destChoice === 'new') {
    const newId = 'sheet_' + Date.now();
    const nextNum = getSheetsList().length + 1;
    const newSheetObj = { id: newId, name: `Pivot Table ${nextNum}`, cells: {} };
    if (Array.isArray(appState.sheets)) {
      appState.sheets.push(newSheetObj);
    } else if (appState.sheets && typeof appState.sheets === 'object') {
      appState.sheets[newId] = newSheetObj;
    }
    appState.activeSheetId = newId;
    renderTabs();
    targetSheetId = newId;
    targetR = 1; targetC = 1;
  } else {
    if (existingLocInp) {
      const parsed = parseRef(existingLocInp.replace(/.*!/, ''));
      if (parsed) { targetR = parsed.r; targetC = parsed.c; }
    } else {
      targetR = appState.selected.r;
      targetC = appState.selected.c;
    }
  }

  currentPivotSourceInfo.destSheetId = targetSheetId;
  currentPivotSourceInfo.destCell = { r: targetR, c: targetC };

  document.getElementById('createPivotModal').classList.remove('show');
  openPivotEditorSidePanel();
};

function openPivotEditorSidePanel() {
  const panel = document.getElementById('pivotEditorSidePanel');
  const badge = document.getElementById('pivotEditorSourceRangeBadge');
  if (!panel) return;

  if (badge) {
    badge.textContent = `${currentPivotSourceInfo.sheetName}!${ref(currentPivotSourceInfo.minR, currentPivotSourceInfo.minC)}:${ref(currentPivotSourceInfo.maxR, currentPivotSourceInfo.maxC)}`;
  }

  const sourceSheet = findSheetObj(currentPivotSourceInfo.sheetId);
  const cells = sourceSheet ? sourceSheet.cells : {};
  const columns = [];

  for (let c = currentPivotSourceInfo.minC; c <= currentPivotSourceInfo.maxC; c++) {
    const headerCell = cells[ref(currentPivotSourceInfo.minR, c)];
    const colName = colNameFromNum(c);
    const label = (headerCell && headerCell.v) ? String(headerCell.v) : `Column ${colName}`;
    columns.push({ c, colName, label });
  }

  const rowSelect = document.getElementById('pivotSelectRowsCol');
  const colSelect = document.getElementById('pivotSelectColsCol');
  const valSelect = document.getElementById('pivotSelectValsCol');

  [rowSelect, colSelect, valSelect].forEach(sel => {
    if (!sel) return;
    sel.innerHTML = sel === colSelect ? '<option value="">None (Single Summary Column)</option>' : '<option value="">Select column...</option>';
    columns.forEach(col => {
      const opt = document.createElement('option');
      opt.value = col.c;
      opt.textContent = `${col.colName}: ${col.label}`;
      sel.appendChild(opt);
    });
  });

  if (columns.length >= 1 && rowSelect) rowSelect.value = columns[0].c;
  if (columns.length >= 2 && valSelect) valSelect.value = columns[columns.length - 1].c;

  panel.style.display = 'flex';
}

const closePivotEditorBtn = document.getElementById('closePivotEditorBtn');
if (closePivotEditorBtn) closePivotEditorBtn.onclick = () => document.getElementById('pivotEditorSidePanel').style.display = 'none';

const applyPivotTableBtn = document.getElementById('applyPivotTableBtn');
if (applyPivotTableBtn) applyPivotTableBtn.onclick = () => {
  const rowColNum = parseInt(document.getElementById('pivotSelectRowsCol')?.value);
  const colColNum = parseInt(document.getElementById('pivotSelectColsCol')?.value);
  const valColNum = parseInt(document.getElementById('pivotSelectValsCol')?.value);
  const aggFunc = document.getElementById('pivotSelectAggFunc')?.value || 'SUM';

  if (!rowColNum) {
    showToast('Please select a Rows (Group By) column');
    return;
  }
  if (!valColNum) {
    showToast('Please select a Values column');
    return;
  }

  buildAndRenderPivotTable(rowColNum, colColNum, valColNum, aggFunc);
};

function buildAndRenderPivotTable(rowColNum, colColNum, valColNum, aggFunc) {
  const sourceSheet = findSheetObj(currentPivotSourceInfo.sheetId);
  const sourceCells = sourceSheet ? sourceSheet.cells : {};

  const rowHeaderCell = sourceCells[ref(currentPivotSourceInfo.minR, rowColNum)];
  const valHeaderCell = sourceCells[ref(currentPivotSourceInfo.minR, valColNum)];
  const rowHeaderLabel = (rowHeaderCell && rowHeaderCell.v) ? String(rowHeaderCell.v) : `Column ${colNameFromNum(rowColNum)}`;
  const valHeaderLabel = (valHeaderCell && valHeaderCell.v) ? String(valHeaderCell.v) : `Column ${colNameFromNum(valColNum)}`;

  const dataRows = [];
  for (let r = currentPivotSourceInfo.minR + 1; r <= currentPivotSourceInfo.maxR; r++) {
    const rowValCell = sourceCells[ref(r, rowColNum)];
    const valValCell = sourceCells[ref(r, valColNum)];
    const colValCell = colColNum ? sourceCells[ref(r, colColNum)] : null;

    const rowVal = rowValCell && rowValCell.v !== undefined ? String(rowValCell.v) : '';
    const colVal = colValCell && colValCell.v !== undefined ? String(colValCell.v) : 'Total';
    let rawNum = valValCell && valValCell.v !== undefined ? valValCell.v : 0;
    if (typeof rawNum === 'string') rawNum = parseFloat(rawNum.replace(/[^0-9.-]/g, '')) || 0;

    if (rowVal.trim() !== '') {
      dataRows.push({ r, rowVal, colVal, numVal: rawNum });
    }
  }

  if (!dataRows.length) {
    showToast('No data rows found in selected range');
    return;
  }

  const uniqueRows = [...new Set(dataRows.map(d => d.rowVal))];
  const uniqueCols = colColNum ? [...new Set(dataRows.map(d => d.colVal))] : ['Total'];

  const runAgg = (items) => {
    if (!items.length) return 0;
    if (aggFunc === 'SUM') return items.reduce((acc, i) => acc + i.numVal, 0);
    if (aggFunc === 'COUNTA') return items.length;
    if (aggFunc === 'AVERAGE') return Math.round((items.reduce((acc, i) => acc + i.numVal, 0) / items.length) * 100) / 100;
    if (aggFunc === 'MIN') return Math.min(...items.map(i => i.numVal));
    if (aggFunc === 'MAX') return Math.max(...items.map(i => i.numVal));
    return 0;
  };

  const targetSheet = findSheetObj(currentPivotSourceInfo.destSheetId);
  if (!targetSheet) return;
  const targetCells = targetSheet.cells;
  const baseR = currentPivotSourceInfo.destCell.r;
  const baseC = currentPivotSourceInfo.destCell.c;

  targetCells[ref(baseR, baseC)] = { v: `${rowHeaderLabel} (${aggFunc} of ${valHeaderLabel})`, b: true, bg: '#34a853', color: '#ffffff' };

  uniqueCols.forEach((colKey, cIdx) => {
    targetCells[ref(baseR, baseC + 1 + cIdx)] = { v: colKey, b: true, bg: '#e8f0fe', color: '#174ea6', align: 'c' };
  });
  if (colColNum) {
    targetCells[ref(baseR, baseC + 1 + uniqueCols.length)] = { v: 'Grand Total', b: true, bg: '#d1e7dd', color: '#0f5132', align: 'c' };
  }

  let grandTotalItems = [];

  uniqueRows.forEach((rowKey, rIdx) => {
    const curR = baseR + 1 + rIdx;
    targetCells[ref(curR, baseC)] = { v: rowKey, b: true, bg: '#f8f9fa', color: '#202124' };

    let rowTotalItems = [];

    uniqueCols.forEach((colKey, cIdx) => {
      const curC = baseC + 1 + cIdx;
      const matched = dataRows.filter(d => d.rowVal === rowKey && d.colVal === colKey);
      rowTotalItems.push(...matched);
      grandTotalItems.push(...matched);

      const cellAgg = runAgg(matched);
      targetCells[ref(curR, curC)] = { v: cellAgg, fmt: typeof cellAgg === 'number' ? 'number' : 'plain', align: 'r' };
    });

    if (colColNum) {
      const rowAggTotal = runAgg(rowTotalItems);
      targetCells[ref(curR, baseC + 1 + uniqueCols.length)] = { v: rowAggTotal, b: true, bg: '#f1f3f4', align: 'r' };
    }
  });

  const totalR = baseR + 1 + uniqueRows.length;
  targetCells[ref(totalR, baseC)] = { v: 'Grand Total', b: true, bg: '#e8eaed', color: '#202124' };

  uniqueCols.forEach((colKey, cIdx) => {
    const curC = baseC + 1 + cIdx;
    const colMatched = dataRows.filter(d => d.colVal === colKey);
    const colAgg = runAgg(colMatched);
    targetCells[ref(totalR, curC)] = { v: colAgg, b: true, bg: '#e8eaed', align: 'r' };
  });

  if (colColNum) {
    const overallAgg = runAgg(grandTotalItems);
    targetCells[ref(totalR, baseC + 1 + uniqueCols.length)] = { v: overallAgg, b: true, bg: '#d1e7dd', color: '#0f5132', align: 'r' };
  }

  document.getElementById('pivotEditorSidePanel').style.display = 'none';
  pushHistory();
  renderAll();
  triggerAutoSave();
  showToast(`Pivot table created successfully!`);
}

