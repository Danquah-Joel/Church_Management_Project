<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Church Register</title>
<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
echo '<meta name="csrf-token" content="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) . '">';
?>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --gold: #E8C44A;
    --gold-dark: #B8963E;
    --gold-light: rgba(232,196,74,0.18);
    --gold-pale: transparent;
    --deep: #0D1B5E;
    --ink: #0D1B5E;
    --muted: #3a4a8a;
    --border: rgba(13,27,94,0.25);
    --surface: rgba(255,255,255,0.88);
    --white: #FFFFFF;
  }

  body {
    background-image: url('image/gold.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            background-color: #f8fafc1b;
            min-height: 100vh;
            position: relative;
  }

  .page-wrapper { max-width: 980px; margin: 0 auto; }

  /* Header */
  .back-nav {
    max-width: 980px;
    margin: 0 auto;
    padding: 1rem 2rem 0;
  }
  .back-nav a {
    font-family: 'Jost', sans-serif;
    font-size: 0.82rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s;
  }
  .back-nav a:hover { color: #E8C44A; }

  .header { text-align: center; padding: 1.5rem 2rem 2rem; }
  .cross-ornament { font-size: 2rem; color: #E8C44A; display: block; margin-bottom: 0.75rem; letter-spacing: 0.5rem; text-shadow: 0 2px 6px rgba(0,0,0,0.3); }
  .church-name { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; font-weight: 600; color: #FFFFFF; line-height: 1.1; margin-bottom: 0.3rem; text-shadow: 0 2px 8px rgba(0,0,0,0.45); }
  .form-title { font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; font-weight: 400; font-style: italic; color: #E8E0FF; margin-bottom: 1.5rem; text-shadow: 0 1px 4px rgba(0,0,0,0.4); }
  .divider { display: flex; align-items: center; gap: 12px; margin: 0 auto; max-width: 320px; }
  .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.5); }
  .divider-diamond { width: 7px; height: 7px; background: #E8C44A; transform: rotate(45deg); flex-shrink: 0; }

  /* Tab Nav */
  .tab-nav {
    display: flex;
    gap: 0;
    margin-top: 2rem;
    border-bottom: 2px solid rgba(255,255,255,0.4);
    background: rgba(13,27,94,0.55);
    border-radius: 4px 4px 0 0;
    padding: 0 0.5rem;
  }

  .tab-btn {
    font-family: 'Jost', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.65);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1.75rem;
    cursor: pointer;
    margin-bottom: -2px;
    transition: color 0.2s, border-color 0.2s;
  }

  .tab-btn.active { color: #E8C44A; border-bottom-color: #E8C44A; }
  .tab-btn:hover:not(.active) { color: #ffffff; }

  .tab-badge {
    display: inline-block;
    background: var(--gold);
    color: var(--white);
    font-size: 0.65rem;
    font-weight: 500;
    border-radius: 10px;
    padding: 1px 6px;
    margin-left: 6px;
    vertical-align: middle;
  }

  /* Panels */
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }

  /* Form Card */
  .form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2.5rem;
    margin-top: 1.5rem;
    position: relative;
  }
  .form-card::before {
    content: '';
    position: absolute;
    top: 6px; left: 6px; right: 6px; bottom: 6px;
    border: 0.5px solid var(--border);
    border-radius: 2px;
    pointer-events: none;
  }

  /* Section labels */
  .section-label {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #0D1B5E;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    font-style: italic;
    margin-bottom: 1rem;
    margin-top: 2rem;
    padding-bottom: 0.4rem;
    border-bottom: 0.5px solid var(--border);
  }
  .section-label:first-of-type { margin-top: 0; }

  /* Grid */
  .field-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
  .field-row.two { grid-template-columns: 1fr 1fr; }
  .field-row.three { grid-template-columns: 1fr 1fr 1fr; }
  .field-row.one { grid-template-columns: 1fr; }
  .field { display: flex; flex-direction: column; gap: 5px; }

  label {
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
  }

  input[type="text"], input[type="email"], input[type="tel"],
  input[type="date"], input[type="number"], select, textarea {
    font-family: 'Jost', sans-serif;
    font-size: 0.95rem;
    color: var(--ink);
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 0.55rem 0.75rem;
    width: 100%;
    transition: border-color 0.2s;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
  }
  input:focus, select:focus, textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(184,150,62,0.12);
  }
  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%237A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2.2rem;
    cursor: pointer;
  }
  textarea { resize: vertical; min-height: 80px; line-height: 1.6; }

  /* Checkboxes */
  .checkbox-group { display: flex; flex-wrap: wrap; gap: 0.6rem 1.4rem; margin-top: 4px; }
  .checkbox-item { display: flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.88rem; color: var(--ink); user-select: none; }
  .checkbox-item input[type="checkbox"] {
    width: 16px; height: 16px;
    border: 1px solid var(--border); border-radius: 2px;
    background: var(--white); appearance: none; -webkit-appearance: none;
    cursor: pointer; flex-shrink: 0;
    transition: background 0.15s, border-color 0.15s; position: relative;
  }
  .checkbox-item input[type="checkbox"]:checked { background: var(--gold); border-color: var(--gold); }
  .checkbox-item input[type="checkbox"]:checked::after {
    content: ''; position: absolute;
    left: 4px; top: 1px; width: 5px; height: 9px;
    border: 2px solid white; border-top: none; border-left: none;
    transform: rotate(45deg);
  }

  /* Radio */
  .radio-group { display: flex; flex-wrap: wrap; gap: 0.6rem 1.4rem; margin-top: 4px; }
  .radio-item { display: flex; align-items: center; gap: 7px; cursor: pointer; font-size: 0.88rem; color: var(--ink); user-select: none; }
  .radio-item input[type="radio"] {
    width: 16px; height: 16px;
    border: 1px solid var(--border); border-radius: 50%;
    background: var(--white); appearance: none; -webkit-appearance: none;
    cursor: pointer; flex-shrink: 0;
    transition: background 0.15s, border-color 0.15s; position: relative;
  }
  .radio-item input[type="radio"]:checked { border-color: var(--gold); background: var(--white); }
  .radio-item input[type="radio"]:checked::after {
    content: ''; position: absolute;
    top: 3px; left: 3px; width: 8px; height: 8px;
    background: var(--gold); border-radius: 50%;
  }

  /* Counter */
  .counter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
  .counter-card { background: var(--gold-light); border: 1px solid var(--border); border-radius: 2px; padding: 0.7rem 0.75rem; }
  .counter-card label { display: block; margin-bottom: 6px; }
  .counter-card input[type="number"] { background: var(--white); text-align: center; font-size: 1.1rem; font-weight: 500; padding: 0.4rem; }
  .total-row {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 10px; padding: 0.6rem 0.75rem;
    background: #0D1B5E; border-radius: 2px;
    color: var(--white); font-size: 0.88rem;
    letter-spacing: 0.04em; margin-bottom: 1rem;
  }
  .total-row span { font-size: 1.2rem; font-weight: 500; color: #F5DFA0; }

  /* Buttons */
  .form-actions { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 0.5px solid var(--border); flex-wrap: wrap; }
  .form-actions-right { display: flex; gap: 1rem; }
  .btn-view-records {
    font-family: 'Jost', sans-serif; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--white); background: var(--deep); border: none; border-radius: 2px;
    padding: 0.65rem 1.5rem; cursor: pointer; transition: background 0.2s, transform 0.15s;
    text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
  }
  .btn-view-records:hover { background: var(--gold-dark); transform: translateY(-1px); color: var(--white); }
  .btn-reset {
    font-family: 'Jost', sans-serif; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--muted); background: transparent; border: 1px solid var(--border); border-radius: 2px;
    padding: 0.65rem 1.5rem; cursor: pointer; transition: color 0.2s, border-color 0.2s;
  }
  .btn-reset:hover { color: var(--ink); border-color: var(--ink); }
  .btn-submit {
    font-family: 'Jost', sans-serif; font-size: 0.82rem; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--white); background: var(--gold); border: none; border-radius: 2px;
    padding: 0.65rem 2rem; cursor: pointer; transition: background 0.2s;
  }
  .btn-submit:hover { background: #9E7D2F; }

  .success-banner {
    display: none; background: #EAF5EB; border: 1px solid #78C17A;
    border-radius: 2px; padding: 1rem 1.25rem;
    text-align: center; color: #2B6B2D; font-size: 0.95rem; margin-top: 1.5rem;
  }
  .success-banner.visible { display: block; }

  /* ===== ATTENDANCE SHEET ===== */
  .sheet-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2rem 2rem;
    margin-top: 1.5rem;
    position: relative;
  }
  .sheet-card::before {
    content: '';
    position: absolute;
    top: 6px; left: 6px; right: 6px; bottom: 6px;
    border: 0.5px solid var(--border);
    border-radius: 2px;
    pointer-events: none;
  }

  .sheet-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }

  .sheet-toolbar input[type="text"] {
    flex: 1;
    min-width: 180px;
    font-size: 0.88rem;
    padding: 0.5rem 0.75rem;
  }

  .sheet-toolbar select {
    min-width: 160px;
    font-size: 0.85rem;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
  }

  .btn-export {
    font-family: 'Jost', sans-serif;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--white);
    background: var(--deep);
    border: none;
    border-radius: 2px;
    padding: 0.55rem 1.2rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s;
  }
  .btn-export:hover { background: #3a3520; }

  .btn-clear-sheet {
    font-family: 'Jost', sans-serif;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #933;
    background: transparent;
    border: 1px solid #c9a0a0;
    border-radius: 2px;
    padding: 0.55rem 1.2rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s, color 0.2s;
  }
  .btn-clear-sheet:hover { background: #f9ecec; }

  /* Stats row */
  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.6rem;
    margin-bottom: 1.5rem;
  }
  .stat-box {
    background: var(--gold-light);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 0.6rem 0.75rem;
    text-align: center;
  }
  .stat-box .stat-val {
    font-size: 1.5rem;
    font-weight: 500;
    color: var(--deep);
    display: block;
    line-height: 1.2;
  }
  .stat-box .stat-lbl {
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted);
    display: block;
    margin-top: 2px;
  }

  /* Table */
  .table-wrap { overflow-x: auto; }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.84rem;
  }

  thead tr {
    background: #0D1B5E;
    color: var(--white);
  }

  thead th {
    padding: 0.65rem 0.9rem;
    text-align: left;
    font-weight: 500;
    font-size: 0.72rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
  }

  thead th:hover { background: #3a3520; }

  thead th .sort-icon { margin-left: 4px; opacity: 0.5; font-style: normal; }
  thead th.sorted .sort-icon { opacity: 1; }

  tbody tr { border-bottom: 0.5px solid var(--border); transition: background 0.12s; }
  tbody tr:hover { background: var(--gold-light); }
  tbody tr:last-child { border-bottom: none; }

  tbody td {
    padding: 0.6rem 0.9rem;
    color: var(--ink);
    vertical-align: middle;
    white-space: nowrap;
  }

  .badge {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 10px;
  }
  .badge-member { background: #E3F0FF; color: #1A558F; }
  .badge-visitor { background: #FFF4E0; color: #8A5C00; }
  .badge-convert { background: #E8F5E9; color: #2E6B31; }

  .btn-del-row {
    background: transparent;
    border: none;
    color: #b44;
    cursor: pointer;
    font-size: 1rem;
    padding: 2px 6px;
    border-radius: 2px;
    transition: background 0.15s;
  }
  .btn-del-row:hover { background: #fdecea; }

  .empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--muted);
  }
  .empty-state .cross { font-size: 2rem; display: block; margin-bottom: 0.5rem; color: var(--border); }
  .empty-state p { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1.05rem; }

  /* Scripture footer */
  .scripture {
    text-align: center; margin-top: 2.5rem;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; color: rgba(255,255,255,0.8);
    font-size: 0.95rem; line-height: 1.6;
    text-shadow: 0 1px 4px rgba(0,0,0,0.4);
  }

  @media (max-width: 600px) {
    .field-row.two, .field-row.three { grid-template-columns: 1fr; }
    .form-card, .sheet-card { padding: 1.5rem 1.25rem; }
    .church-name { font-size: 1.8rem; }
    .tab-btn { padding: 0.65rem 1rem; font-size: 0.72rem; }
  }
</style>
</head>
<body>
<div class="back-nav">
  <a href="../Home_Page.html">&#8592; Back to Home Page</a>
</div>
<div class="page-wrapper">

  <div class="header">
    <h1 class="church-name">Church Of Pentecost</h1>
    <p class="form-title">Sunday Service Register</p>
    <div class="divider"><div class="divider-diamond"></div></div>
  </div>

  <!-- TAB NAV -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('form', this)">Entry Form</button>
    <button class="tab-btn" onclick="switchTab('sheet', this)">
      Attendance Sheet <span class="tab-badge" id="record-count">0</span>
    </button>
  </div>

  <!-- FORM PANEL -->
  <div id="panel-form" class="tab-panel active">
    <div class="form-card">
      <form id="attendanceForm" novalidate>

        <p class="section-label">Service Information</p>
        <div class="field-row two">
          <div class="field">
            <label for="service-date">Service Date</label>
            <input type="date" id="service-date" name="service_date" required>
          </div>
          <div class="field">
            <label for="service-type">Service Type</label>
            <select id="service-type" name="service_type">
              <option value="">Select service…</option>
              <option>Sunday Morning Service</option>
              <option>Sunday Evening Service</option>
              <option>Wednesday Bible Study</option>
              <option>Youth Service</option>
              <option>Children's Service</option>
              <option>Special Programme</option>
              <option>National Week Programme</option>
              <option>National Pemem Week</option>
              <option>National Women's Week</option>
              <option>National Youth Week Programme</option>
              <option>Area Week</option>
              <option>Area Programme</option>
              <option>District Week</option>
              <option>Local Week</option>
            </select>
          </div>
        </div>
        <div class="field-row two">
          <div class="field">
            <label for="service-time">Service Time</label>
            <input type="text" id="service-time" name="service_time" placeholder="e.g. 9:00 AM">
          </div>
          <div class="field">
            <label for="minister">Officiant / Minister</label>
            <input type="text" id="minister" name="minister" placeholder="Name of minister">
          </div>
        </div>

        <p class="section-label">Attendee Details</p>
        <div class="field-row two">
          <div class="field">
            <label for="first-name">First Name *</label>
            <input type="text" id="first-name" name="first_name" placeholder="First name" required>
          </div>
          <div class="field">
            <label for="last-name">Last Name *</label>
            <input type="text" id="last-name" name="last_name" placeholder="Last name" required>
          </div>
        </div>
        <div class="field-row two">
          <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="+233 …">
          </div>
          <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@email.com">
          </div>
        </div>
        <div class="field-row two">
          <div class="field">
            <label>Member Status</label>
            <div class="radio-group">
              <label class="radio-item"><input type="radio" name="member_status" value="member"> Member</label>
              <label class="radio-item"><input type="radio" name="member_status" value="visitor"> Visitor</label>
              <label class="radio-item"><input type="radio" name="member_status" value="new_convert"> New Convert</label>
            </div>
          </div>
          <div class="field">
            <label for="department">Department / Cell</label>
            <select id="department" name="department">
              <option value="">Select…</option>
              <option>General Congregation</option>
              <option>Youth Ministry</option>
              <option>Children's Ministry</option>
              <option>Choir / Music Team</option>
              <option>Ushering</option>
              <option>Prayer Team</option>
              <option>Media & Tech</option>
              <option>Welfare & Outreach</option>
            </select>
          </div>
        </div>

        <p class="section-label">Congregation Count</p>
        <div class="counter-row">
          <div class="counter-card"><label for="cnt-apostles">Apostles</label><input type="number" id="cnt-apostles" name="cnt_apostles" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-apostles-wife">Apostle's Wife</label><input type="number" id="cnt-apostles-wife" name="cnt_apostles_wife" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-pastors">Pastors</label><input type="number" id="cnt-pastors" name="cnt_pastors" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-pastors-wife">Pastor's Wife</label><input type="number" id="cnt-pastors-wife" name="cnt_pastors_wife" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-elders">Elders</label><input type="number" id="cnt-elders" name="cnt_elders" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-dcn">DCN</label><input type="number" id="cnt-dcn" name="cnt_dcn" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-dcns">DCNS</label><input type="number" id="cnt-dcns" name="cnt_dcns" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-men">Men</label><input type="number" id="cnt-men" name="cnt_men" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-women">Women</label><input type="number" id="cnt-women" name="cnt_women" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-youth">Youth</label><input type="number" id="cnt-youth" name="cnt_youth" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-children">Children</label><input type="number" id="cnt-children" name="cnt_children" min="0" value="0" oninput="updateTotals()"></div>
          <div class="counter-card"><label for="cnt-visitors">Visitors</label><input type="number" id="cnt-visitors" name="cnt_visitors" min="0" value="0" oninput="updateTotals()"></div>
        </div>
        <div class="total-row" style="margin-bottom:0.5rem;">
          Adults &amp; Leaders (excl. Children):
          <span id="adult-count" style="font-size:1.2rem;font-weight:500;color:#F5DFA0;">0</span>
        </div>
        <div class="total-row">
          Total Attendance (all):
          <span id="total-count" style="font-size:1.2rem;font-weight:500;color:#F5DFA0;">0</span>
        </div>

        <p class="section-label">Programmes / Activities</p>
        <div class="field-row one">
          <div class="field">
            <label>Activities Held Today</label>
            <div class="checkbox-group">
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Worship & Praise"> Worship & Praise</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Sermon"> Sermon</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Holy Communion"> Holy Communion</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Offering/Tithe"> Offering / Tithe</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Special Prayer"> Special Prayer</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Announcements"> Announcements</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Baby Dedication"> Baby Dedication</label>
              <label class="checkbox-item"><input type="checkbox" name="activity" value="Baptism"> Baptism</label>
            </div>
          </div>
        </div>

        <div class="field-row two" style="margin-top:1rem">
          <div class="field">
            <label for="offering-amount">Offering Amount (GHS)</label>
            <input type="number" id="offering-amount" name="offering_amount" min="0" placeholder="0.00" step="0.01">
          </div>
          <div class="field">
            <label for="tithe-amount">Tithe Amount (GHS)</label>
            <input type="number" id="tithe-amount" name="tithe_amount" min="0" placeholder="0.00" step="0.01">
          </div>
        </div>

        <p class="section-label">Communion Participants</p>
        <div class="counter-row">
          <div class="counter-card"><label for="com-officers">Officers</label><input type="number" id="com-officers" name="com_officers" min="0" value="0" oninput="updateCommunion()"></div>
          <div class="counter-card"><label for="com-male">Male</label><input type="number" id="com-male" name="com_male" min="0" value="0" oninput="updateCommunion()"></div>
          <div class="counter-card"><label for="com-female">Female</label><input type="number" id="com-female" name="com_female" min="0" value="0" oninput="updateCommunion()"></div>
        </div>
        <div class="total-row">
          Total Communion Participants:
          <span id="communion-total" style="font-size:1.2rem;font-weight:500;color:#F5DFA0;">0</span>
        </div>

        <p class="section-label">Bible Studies Participants</p>
        <div class="counter-row">
          <div class="counter-card"><label for="bs-male">Male</label><input type="number" id="bs-male" name="bs_male" min="0" value="0" oninput="updateBibleStudies()"></div>
          <div class="counter-card"><label for="bs-female">Female</label><input type="number" id="bs-female" name="bs_female" min="0" value="0" oninput="updateBibleStudies()"></div>
        </div>
        <div class="total-row">
          Total Bible Studies Participants:
          <span id="bs-total" style="font-size:1.2rem;font-weight:500;color:#F5DFA0;">0</span>
        </div>

        <p class="section-label">Prayer Requests & Notes</p>
        <div class="field-row one">
          <div class="field">
            <label for="prayer-request">Prayer Request (optional)</label>
            <textarea id="prayer-request" name="prayer_request" placeholder="Share any prayer requests or praises…"></textarea>
          </div>
        </div>
        <div class="field-row one">
          <div class="field">
            <label for="notes">Service Notes / Remarks</label>
            <textarea id="notes" name="notes" placeholder="Any additional notes for the record…" style="min-height:65px"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <a href="Church_Register_Records.html" class="btn-view-records">
            &#128196; View Register Records
          </a>
          <div class="form-actions-right">
            <button type="button" class="btn-reset" onclick="resetForm()">Clear Form</button>
            <button type="submit" class="btn-submit">Save &amp; Add to Sheet</button>
          </div>
        </div>
      </form>

      <div class="success-banner" id="successBanner">
        ✝ &nbsp; Record saved and added to the Attendance Sheet.
      </div>
    </div>
  </div>

  <!-- SHEET PANEL -->
  <div id="panel-sheet" class="tab-panel">
    <div class="sheet-card">

      <!-- Summary Stats -->
      <div class="stats-row">
        <div class="stat-box"><span class="stat-val" id="stat-total">0</span><span class="stat-lbl">Total Records</span></div>
        <div class="stat-box"><span class="stat-val" id="stat-members">0</span><span class="stat-lbl">Members</span></div>
        <div class="stat-box"><span class="stat-val" id="stat-visitors">0</span><span class="stat-lbl">Visitors</span></div>
        <div class="stat-box"><span class="stat-val" id="stat-converts">0</span><span class="stat-lbl">New Converts</span></div>
        <div class="stat-box"><span class="stat-val" id="stat-offering">0</span><span class="stat-lbl">Offering + Tithe (GHS)</span></div>
      </div>

      <!-- Toolbar -->
      <div class="sheet-toolbar">
        <input type="text" id="sheet-search" placeholder="&#128269; Search by name, date, service…" oninput="renderSheet()">
        <select id="sheet-filter-status" onchange="renderSheet()">
          <option value="">All statuses</option>
          <option value="member">Member</option>
          <option value="visitor">Visitor</option>
          <option value="new_convert">New Convert</option>
        </select>
        <select id="sheet-filter-type" onchange="renderSheet()">
          <option value="">All services</option>
          <option>Sunday Morning Service</option>
          <option>Sunday Evening Service</option>
          <option>Wednesday Bible Study</option>
          <option>Youth Service</option>
          <option>Children's Service</option>
          <option>Special Programme</option>
          <option>National Week Programme</option>
          <option>National Pemem Week</option>
          <option>National Women's Week</option>
          <option>National Youth Week Programme</option>
          <option>Area Week</option>
          <option>Area Programme</option>
          <option>District Week</option>
          <option>Local Week</option>
        </select>
        <button class="btn-export" onclick="exportCSV()">&#8595; Export CSV</button>
        <button class="btn-clear-sheet" onclick="clearSheet()">&#128465; Clear All</button>
      </div>

      <!-- Table -->
      <div class="table-wrap">
        <table id="sheet-table">
          <thead>
            <tr>
              <th onclick="sortBy('no')">#<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('date')">Date<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('name')">Full Name<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('service')">Service<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('status')">Status<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('department')">Department<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('phone')">Phone<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('total')">Attendance<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('offering')">Offering (GHS)<i class="sort-icon">⇅</i></th>
              <th onclick="sortBy('minister')">Minister<i class="sort-icon">⇅</i></th>
              <th></th>
            </tr>
          </thead>
          <tbody id="sheet-body"></tbody>
        </table>
      </div>

      <div id="empty-state" class="empty-state">
        <span class="cross">✝</span>
        <p>No records yet. Submit the entry form to begin the register.</p>
      </div>

    </div>
  </div>

  <p class="scripture">
    "And let us not neglect our meeting together, as some people do,<br>
    but encourage one another." — Hebrews 10:25
  </p>

</div>

<script>
  // Auth guard — checks real server session via check_session.php
  (async function checkAuth() {
    try {
      const res  = await fetch('../check_session.php');
      const data = await res.json();
      if (!data.logged_in) {
        window.location.href = '../../Login.html';
      }
    } catch {
      console.warn('Auth check skipped — check_session.php not reachable.');
    }
  })();

  // CSRF token injected server-side into the page
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const STORAGE_KEY = 'gcc_attendance_records';

  // ---- State ----
  let records = [];
  let sortKey = 'no';
  let sortAsc = true;

  const sortKeyMap = { dept: 'department' };

  // ---- Tab switching ----
  function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
    if (name === 'sheet') renderSheet();
  }

  function mapApiRecord(r, index) {
    return {
      id: r.id,
      localOnly: false,
      no: index + 1,
      date: r.service_date,
      firstName: r.first_name,
      lastName: r.last_name,
      name: r.full_name || (r.first_name + ' ' + r.last_name),
      phone: r.phone || '',
      email: r.email || '',
      status: r.member_status || '',
      department: r.department || '',
      service: r.service_type || '',
      time: r.service_time || '',
      minister: r.minister || '',
      apostles: parseInt(r.cnt_apostles) || 0,
      apostles_wife: parseInt(r.cnt_apostles_wife) || 0,
      pastors: parseInt(r.cnt_pastors) || 0,
      pastors_wife: parseInt(r.cnt_pastors_wife) || 0,
      elders: parseInt(r.cnt_elders) || 0,
      dcn: parseInt(r.cnt_dcn) || 0,
      dcns: parseInt(r.cnt_dcns) || 0,
      men: parseInt(r.cnt_men) || 0,
      women: parseInt(r.cnt_women) || 0,
      youth: parseInt(r.cnt_youth) || 0,
      children: parseInt(r.cnt_children) || 0,
      visitors: parseInt(r.cnt_visitors) || 0,
      new_converts: parseInt(r.cnt_new_converts) || 0,
      guests: parseInt(r.cnt_guests) || 0,
      adult_total: parseInt(r.adult_total) || 0,
      total: parseInt(r.grand_total) || 0,
      offering: parseFloat(r.offering_amount) || 0,
      tithe: parseFloat(r.tithe_amount) || 0,
      activities: r.activities || '',
      prayer: r.prayer_request || '',
      notes: r.notes || '',
      com_officers: parseInt(r.com_officers) || 0,
      com_male: parseInt(r.com_male) || 0,
      com_female: parseInt(r.com_female) || 0,
      communion_total: parseInt(r.communion_total) || 0,
      bs_male: parseInt(r.bs_male) || 0,
      bs_female: parseInt(r.bs_female) || 0,
      bs_total: parseInt(r.bs_total) || 0
    };
  }

  function buildPayload() {
    const activities = [...document.querySelectorAll('input[name="activity"]:checked')].map(c => c.value);
    const statusEl = document.querySelector('input[name="member_status"]:checked');
    return {
      first_name: document.getElementById('first-name').value.trim(),
      last_name: document.getElementById('last-name').value.trim(),
      service_date: document.getElementById('service-date').value,
      service_type: document.getElementById('service-type').value,
      service_time: document.getElementById('service-time').value.trim(),
      minister: document.getElementById('minister').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      email: document.getElementById('email').value.trim(),
      member_status: statusEl ? statusEl.value : '',
      department: document.getElementById('department').value,
      cnt_apostles: parseInt(document.getElementById('cnt-apostles').value) || 0,
      cnt_apostles_wife: parseInt(document.getElementById('cnt-apostles-wife').value) || 0,
      cnt_pastors: parseInt(document.getElementById('cnt-pastors').value) || 0,
      cnt_pastors_wife: parseInt(document.getElementById('cnt-pastors-wife').value) || 0,
      cnt_elders: parseInt(document.getElementById('cnt-elders').value) || 0,
      cnt_dcn: parseInt(document.getElementById('cnt-dcn').value) || 0,
      cnt_dcns: parseInt(document.getElementById('cnt-dcns').value) || 0,
      cnt_men: parseInt(document.getElementById('cnt-men').value) || 0,
      cnt_women: parseInt(document.getElementById('cnt-women').value) || 0,
      cnt_youth: parseInt(document.getElementById('cnt-youth').value) || 0,
      cnt_children: parseInt(document.getElementById('cnt-children').value) || 0,
      cnt_visitors: parseInt(document.getElementById('cnt-visitors').value) || 0,
      offering_amount: parseFloat(document.getElementById('offering-amount').value) || 0,
      tithe_amount: parseFloat(document.getElementById('tithe-amount').value) || 0,
      activities: activities.join(', '),
      prayer_request: document.getElementById('prayer-request').value.trim(),
      notes: document.getElementById('notes').value.trim(),
      com_officers: parseInt(document.getElementById('com-officers').value) || 0,
      com_male: parseInt(document.getElementById('com-male').value) || 0,
      com_female: parseInt(document.getElementById('com-female').value) || 0,
      bs_male: parseInt(document.getElementById('bs-male').value) || 0,
      bs_female: parseInt(document.getElementById('bs-female').value) || 0
    };
  }

  function buildLocalRecord(payload) {
    return {
      id: Date.now(),
      localOnly: true,
      no: records.length + 1,
      date: payload.service_date,
      firstName: payload.first_name,
      lastName: payload.last_name,
      name: payload.first_name + ' ' + payload.last_name,
      phone: payload.phone,
      email: payload.email,
      status: payload.member_status,
      department: payload.department,
      service: payload.service_type,
      time: payload.service_time,
      minister: payload.minister,
      apostles: payload.cnt_apostles,
      apostles_wife: payload.cnt_apostles_wife,
      pastors: payload.cnt_pastors,
      pastors_wife: payload.cnt_pastors_wife,
      elders: payload.cnt_elders,
      dcn: payload.cnt_dcn,
      dcns: payload.cnt_dcns,
      men: payload.cnt_men,
      women: payload.cnt_women,
      youth: payload.cnt_youth,
      children: payload.cnt_children,
      visitors: payload.cnt_visitors,
      adult_total: parseInt(document.getElementById('adult-count').textContent) || 0,
      total: parseInt(document.getElementById('total-count').textContent) || 0,
      offering: payload.offering_amount,
      tithe: payload.tithe_amount,
      activities: payload.activities,
      prayer: payload.prayer_request,
      notes: payload.notes,
      com_officers: payload.com_officers,
      com_male: payload.com_male,
      com_female: payload.com_female,
      communion_total: parseInt(document.getElementById('communion-total').textContent) || 0,
      bs_male: payload.bs_male,
      bs_female: payload.bs_female,
      bs_total: parseInt(document.getElementById('bs-total').textContent) || 0
    };
  }

  function saveLocalRecords() {
    const local = records.filter(r => r.localOnly);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(local));
  }

  function loadLocalRecords() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (!saved) return [];
      return JSON.parse(saved).map((r, i) => ({ ...r, localOnly: true, no: i + 1 }));
    } catch (e) {
      return [];
    }
  }

  async function loadRecords() {
    try {
      const res = await fetch('get_attendance.php');
      const data = await res.json();
      if (data.success && Array.isArray(data.records)) {
        records = data.records.map(mapApiRecord);
        const local = loadLocalRecords();
        records = records.concat(local);
        records.forEach((r, i) => r.no = i + 1);
        updateBadge();
        renderSheet();
        return;
      }
    } catch (e) {
      console.warn('Could not load records from server:', e);
    }
    records = loadLocalRecords();
    updateBadge();
    renderSheet();
  }

  // ---- Congregation totals ----
  // adultIds: all groups except children
  const adultIds = [
    'cnt-apostles','cnt-apostles-wife',
    'cnt-pastors','cnt-pastors-wife',
    'cnt-elders','cnt-dcn','cnt-dcns',
    'cnt-men','cnt-women','cnt-youth','cnt-visitors'
  ];
  // allIds: everything including children
  const allIds = [...adultIds, 'cnt-children'];

  // ---- Communion total ----
  function updateCommunion() {
    const total = ['com-officers','com-male','com-female']
      .reduce((s, id) => s + (parseInt(document.getElementById(id).value) || 0), 0);
    document.getElementById('communion-total').textContent = total;
  }

  // ---- Bible Studies total ----
  function updateBibleStudies() {
    const total = ['bs-male','bs-female']
      .reduce((s, id) => s + (parseInt(document.getElementById(id).value) || 0), 0);
    document.getElementById('bs-total').textContent = total;
  }

  function updateTotals() {
    const adultTotal = adultIds.reduce((s, id) => s + (parseInt(document.getElementById(id).value) || 0), 0);
    const grandTotal = allIds.reduce((s, id) => s + (parseInt(document.getElementById(id).value) || 0), 0);
    document.getElementById('adult-count').textContent = adultTotal;
    document.getElementById('total-count').textContent = grandTotal;
  }

  // ---- Form submit ----
  document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const payload = buildPayload();
    if (!payload.first_name || !payload.last_name || !payload.service_date) {
      alert('Please fill in: First Name, Last Name, and Service Date.');
      return;
    }

    let savedToServer = false;
    try {
      const res = await fetch('save_attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        savedToServer = true;
        await loadRecords();
      } else {
        console.warn('Server save failed:', data.message);
      }
    } catch (err) {
      console.warn('Server save failed:', err);
    }

    if (!savedToServer) {
      const rec = buildLocalRecord(payload);
      records.push(rec);
      saveLocalRecords();
      updateBadge();
      updateStats();
    }

    const banner = document.getElementById('successBanner');
    banner.classList.add('visible');
    setTimeout(() => banner.classList.remove('visible'), 4000);

    this.reset();
    document.getElementById('adult-count').textContent = '0';
    document.getElementById('total-count').textContent = '0';
    document.getElementById('communion-total').textContent = '0';
    document.getElementById('bs-total').textContent = '0';
    document.getElementById('service-date').value = new Date().toISOString().split('T')[0];
  });

  // ---- Reset form ----
  function resetForm() {
    document.getElementById('attendanceForm').reset();
    document.getElementById('adult-count').textContent = '0';
    document.getElementById('total-count').textContent = '0';
    document.getElementById('communion-total').textContent = '0';
    document.getElementById('bs-total').textContent = '0';
    document.getElementById('successBanner').classList.remove('visible');
    document.getElementById('service-date').value = new Date().toISOString().split('T')[0];
  }

  // ---- Badge ----
  function updateBadge() {
    document.getElementById('record-count').textContent = records.length;
  }

  // ---- Stats ----
  function updateStats() {
    document.getElementById('stat-total').textContent = records.length;
    document.getElementById('stat-members').textContent = records.filter(r => r.status === 'member').length;
    document.getElementById('stat-visitors').textContent = records.filter(r => r.status === 'visitor').length;
    document.getElementById('stat-converts').textContent = records.filter(r => r.status === 'new_convert').length;
    const totalOff = records.reduce((s, r) => s + (r.offering || 0) + (r.tithe || 0), 0);
    document.getElementById('stat-offering').textContent = totalOff.toFixed(2);
  }

  // ---- Sort ----
  function sortBy(key) {
    const mapped = sortKeyMap[key] || key;
    if (sortKey === mapped) sortAsc = !sortAsc;
    else { sortKey = mapped; sortAsc = true; }
    document.querySelectorAll('thead th').forEach(th => th.classList.remove('sorted'));
    renderSheet();
  }

  // ---- Render sheet ----
  function renderSheet() {
    const search = document.getElementById('sheet-search').value.toLowerCase();
    const filterStatus = document.getElementById('sheet-filter-status').value;
    const filterType = document.getElementById('sheet-filter-type').value;

    let filtered = records.filter(r => {
      const haystack = (r.name + r.date + r.service + r.minister + r.department + r.phone).toLowerCase();
      const matchSearch = !search || haystack.includes(search);
      const matchStatus = !filterStatus || r.status === filterStatus;
      const matchType = !filterType || r.service === filterType;
      return matchSearch && matchStatus && matchType;
    });

    // Sort
    filtered.sort((a, b) => {
      let av = a[sortKey] ?? a.no, bv = b[sortKey] ?? b.no;
      if (typeof av === 'string') av = av.toLowerCase();
      if (typeof bv === 'string') bv = bv.toLowerCase();
      if (av < bv) return sortAsc ? -1 : 1;
      if (av > bv) return sortAsc ? 1 : -1;
      return 0;
    });

    const tbody = document.getElementById('sheet-body');
    const empty = document.getElementById('empty-state');

    if (filtered.length === 0) {
      tbody.innerHTML = '';
      empty.style.display = 'block';
    } else {
      empty.style.display = 'none';
      tbody.innerHTML = filtered.map((r, i) => {
        const badgeClass = r.status === 'member' ? 'badge-member' : r.status === 'visitor' ? 'badge-visitor' : 'badge-convert';
        const statusLabel = r.status === 'new_convert' ? 'New Convert' : r.status ? (r.status.charAt(0).toUpperCase() + r.status.slice(1)) : '—';
        const dateStr = r.date ? new Date(r.date + 'T00:00:00').toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) : '—';
        return `<tr>
          <td>${i + 1}</td>
          <td>${dateStr}</td>
          <td><strong style="font-weight:500">${r.name}</strong>${r.email ? '<br><span style="color:var(--muted);font-size:0.78rem">'+r.email+'</span>' : ''}</td>
          <td>${r.service || '—'}<br><span style="color:var(--muted);font-size:0.78rem">${r.time || ''}</span></td>
          <td>${r.status ? '<span class="badge '+badgeClass+'">'+statusLabel+'</span>' : '—'}</td>
          <td>${r.department || '—'}</td>
          <td>${r.phone || '—'}</td>
          <td style="text-align:center;font-weight:500">${r.total}</td>
          <td style="text-align:right">${((r.offering || 0) + (r.tithe || 0)).toFixed(2)}</td>
          <td>${r.minister || '—'}</td>
          <td><button class="btn-del-row" onclick="deleteRecord(${r.id})" title="Remove">✕</button></td>
        </tr>`;
      }).join('');
    }

    updateStats();
  }

  // ---- Delete record ----
  async function deleteRecord(id) {
    if (!confirm('Remove this record from the sheet?')) return;
    const rec = records.find(r => r.id === id);
    if (rec && !rec.localOnly) {
      try {
        const res = await fetch('delete_attendance.php?id=' + id, {
          method: 'DELETE',
          headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        const data = await res.json();
        if (!data.success) {
          alert(data.message || 'Could not delete record from server.');
          return;
        }
      } catch (err) {
        alert('Could not delete record from server.');
        return;
      }
    }
    records = records.filter(r => r.id !== id);
    records.forEach((r, i) => r.no = i + 1);
    saveLocalRecords();
    updateBadge();
    renderSheet();
  }

  // ---- Clear all ----
  function clearSheet() {
    if (!confirm('Clear all records from this view? Locally saved entries will be removed. Database records will return on refresh unless deleted individually.')) return;
    records = records.filter(r => !r.localOnly);
    localStorage.removeItem(STORAGE_KEY);
    updateBadge();
    renderSheet();
  }

  // ---- Export CSV ----
  function exportCSV() {
    if (records.length === 0) { alert('No records to export.'); return; }
    const headers = ['No','Date','Full Name','Phone','Email','Status','Department','Service','Time','Minister','Apostles',"Apostle's Wife",'Pastors',"Pastor's Wife",'Elders','DCN','DCNS','Men','Women','Youth','Children','Visitors','Adults Total (excl. Children)','Grand Total','Offering (GHS)','Tithe (GHS)','Activities','Prayer Request','Notes','Communion Officers','Communion Male','Communion Female','Communion Total','Bible Studies Male','Bible Studies Female','Bible Studies Total'];
    const rows = records.map(r => [
      r.no, r.date, r.name, r.phone, r.email,
      r.status === 'new_convert' ? 'New Convert' : r.status,
      r.department, r.service, r.time, r.minister,
      r.apostles, r.apostles_wife, r.pastors, r.pastors_wife,
      r.elders, r.dcn, r.dcns,
      r.men, r.women, r.youth, r.children, r.visitors,
      r.adult_total, r.total,
      (r.offering || 0).toFixed(2), (r.tithe || 0).toFixed(2),
      r.activities, r.prayer, r.notes,
      r.com_officers, r.com_male, r.com_female, r.communion_total,
      r.bs_male, r.bs_female, r.bs_total
    ].map(v => '"' + String(v ?? '').replace(/"/g, '""') + '"'));

    const csv = [headers.map(h => '"'+h+'"').join(','), ...rows.map(r => r.join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'GCC_Attendance_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    URL.revokeObjectURL(url);
  }

  // ---- Init ----
  document.getElementById('service-date').value = new Date().toISOString().split('T')[0];
  updateTotals();
  updateCommunion();
  updateBibleStudies();
  loadRecords();
</script>
</body>
</html>
