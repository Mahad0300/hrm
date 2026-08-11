<!-- Toolbar Container -->
<div id="toolbar-container">
  <div id="toolbar">
    <div class="tb-search-pill" id="searchMenusBtn">
      <span class="material-symbols-outlined" style="font-size:16px;">search</span>
      <span>Menus</span>
    </div>
    <div class="tb-sep"></div>

    <button class="tb-btn" id="undoBtn" title="Undo (Ctrl+Z)"><span class="material-symbols-outlined" style="font-size:18px;">undo</span></button>
    <button class="tb-btn" id="redoBtn" title="Redo (Ctrl+Y)"><span class="material-symbols-outlined" style="font-size:18px;">redo</span></button>
    <button class="tb-btn" id="printBtn" title="Print (Ctrl+P)"><span class="material-symbols-outlined" style="font-size:18px;">print</span></button>
    <button class="tb-btn" id="paintFormatBtn" title="Paint format"><span class="material-symbols-outlined" style="font-size:18px;">format_paint</span></button>

    <div class="tb-sep"></div>

    <div class="tb-select-wrap" title="Zoom">
      <select id="zoomSelect">
        <option value="50">50%</option>
        <option value="75">75%</option>
        <option value="100" selected>100%</option>
        <option value="125">125%</option>
        <option value="150">150%</option>
      </select>
      <span class="material-symbols-outlined" style="font-size:16px;">arrow_drop_down</span>
    </div>

    <div class="tb-sep"></div>

    <button class="tb-btn" id="fmtCurrency" title="Format as currency"><span class="material-symbols-outlined" style="font-size:18px;">attach_money</span></button>
    <button class="tb-btn" id="fmtPercent" title="Format as percent"><span class="material-symbols-outlined" style="font-size:18px;">percent</span></button>
    <button class="tb-btn" id="fmtDecDecrease" title="Decrease decimal places"><span class="material-symbols-outlined" style="font-size:18px;">decimal_decrease</span></button>
    <button class="tb-btn" id="fmtDecIncrease" title="Increase decimal places"><span class="material-symbols-outlined" style="font-size:18px;">decimal_increase</span></button>
    
    <div class="tb-select-wrap" title="More formats">
      <select id="numFormatSelect">
        <option value="plain">123</option>
        <option value="number">Number</option>
        <option value="currency">Currency</option>
        <option value="percent">Percent</option>
        <option value="scientific">Scientific</option>
      </select>
      <span class="material-symbols-outlined" style="font-size:16px;">arrow_drop_down</span>
    </div>

    <div class="tb-sep"></div>

    <!-- Custom Rich Font Picker -->
    <div class="tb-font-picker" id="fontPickerBtn" title="Font">
      <span id="fontPickerLabel">Arial</span>
      <span class="material-symbols-outlined" style="font-size:16px;">arrow_drop_down</span>
    </div>

    <div class="font-size-control">
      <button class="tb-btn" id="fontSizeDec"><span class="material-symbols-outlined" style="font-size:16px;">remove</span></button>
      <input id="fontSizeInput" type="text" value="10" title="Font size">
      <button class="tb-btn" id="fontSizeInc"><span class="material-symbols-outlined" style="font-size:16px;">add</span></button>
    </div>

    <div class="tb-sep"></div>

    <button class="tb-btn" id="boldBtn" title="Bold (Ctrl+B)"><span style="font-weight:bold; font-size:14px; font-family:'Google Sans', sans-serif;">B</span></button>
    <button class="tb-btn" id="italicBtn" title="Italic (Ctrl+I)"><span style="font-style:italic; font-size:14px; font-family:serif; font-weight:bold;">I</span></button>
    <button class="tb-btn" id="strikeBtn" title="Strikethrough"><span style="text-decoration:line-through; font-weight:bold; font-size:14px; font-family:'Google Sans', sans-serif;">S</span></button>
    
    <button class="tb-btn color-bar-btn" id="textColorBtn" title="Text color">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
        <text x="4" y="13" font-family="'Google Sans', sans-serif" font-weight="bold" font-size="14" fill="#444746">A</text>
        <rect id="textColorSwatch" x="3" y="15" width="18" height="3" rx="1" fill="#202124"/>
      </svg>
    </button>
    <button class="tb-btn color-bar-btn" id="fillColorBtn" title="Fill color">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
        <path d="M16.56 8.94L7.62 0 6.21 1.41l2.38 2.38-5.15 5.15c-.59.59-.59 1.54 0 2.12l5.5 5.5c.29.29.68.44 1.06.44s.77-.15 1.06-.44l5.5-5.5c.59-.58.59-1.53 0-2.12zM5.21 10L10 5.21 14.79 10H5.21z" fill="#444746"/>
        <rect id="fillColorSwatch" x="3" y="15" width="18" height="3" rx="1" fill="#ffffff" stroke="#ccc" stroke-width="0.5"/>
      </svg>
    </button>

    <div class="tb-sep"></div>

    <button class="tb-btn" id="bordersBtn" title="Borders">
      <span class="material-symbols-outlined" style="font-size:18px;">border_all</span>
    </button>
    <button class="tb-btn" id="mergeBtn" title="Merge cells">
      <span class="material-symbols-outlined" style="font-size:18px;">call_merge</span>
    </button>

    <div class="tb-sep"></div>

    <button class="tb-btn" id="alignLeft" title="Horizontal align">
      <span class="material-symbols-outlined" style="font-size:18px;">format_align_left</span>
      <span class="material-symbols-outlined" style="font-size:14px;">arrow_drop_down</span>
    </button>
    <button class="tb-btn" id="valignMiddle" title="Vertical align">
      <span class="material-symbols-outlined" style="font-size:18px;">vertical_align_center</span>
      <span class="material-symbols-outlined" style="font-size:14px;">arrow_drop_down</span>
    </button>
    <button class="tb-btn" id="textWrapBtn" title="Text wrapping">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#444746" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h11a3 3 0 0 1 0 6H12M14 15l-3 3 3 3"/></svg>
      <span class="material-symbols-outlined" style="font-size:14px;">arrow_drop_down</span>
    </button>

    <div class="tb-sep"></div>

    <button class="tb-btn" id="insertLinkBtn" title="Insert link"><span class="material-symbols-outlined" style="font-size:18px;">link</span></button>
    <button class="tb-btn" id="insertCommentBtn" title="Insert comment"><span class="material-symbols-outlined" style="font-size:18px;">add_comment</span></button>
    <button class="tb-btn" id="functionsBtn" title="Functions (Σ)"><span style="font-weight:bold; font-size:15px; font-family:serif;">Σ</span></button>
  </div>
</div>
