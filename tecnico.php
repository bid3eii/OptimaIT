<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['optimait_user'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['optimait_user'];
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OptimaIT - Centro de Control de Asistencia Técnica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #3874CC;
      --primary-hover: #295FB3;
      --primary-glow: rgba(56, 116, 204, 0.35);
      --orange: #E77222;
      --orange-hover: #D06319;
      --orange-glow: rgba(231, 114, 34, 0.4);
      --bg: #090D16;
      --surface-card: rgba(18, 25, 41, 0.95);
      --border: rgba(255, 255, 255, 0.08);
      --border-hover: rgba(255, 255, 255, 0.16);
      --text: #F8FAFC;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
      --green: #10B981;
      --yellow: #F59E0B;
      --red: #EF4444;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    
    body {
      background: radial-gradient(circle at 15% 10%, rgba(56, 116, 204, 0.14) 0%, transparent 45%),
                  radial-gradient(circle at 85% 85%, rgba(231, 114, 34, 0.1) 0%, transparent 45%),
                  var(--bg);
      color: var(--text);
      min-height: 100vh;
      padding: 24px 32px;
      -webkit-font-smoothing: antialiased;
    }

    .container { max-width: 1420px; margin: 0 auto; }

    /* Top Navigation Bar */
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--border);
    }
    .brand-section { display: flex; align-items: center; }
    .brand-section img { height: 48px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4)); }

    .server-badge {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.3);
      padding: 7px 16px;
      border-radius: 999px;
      font-size: 13px;
      color: #34D399;
      font-weight: 600;
      box-shadow: 0 0 15px rgba(16, 185, 129, 0.12);
    }
    .pulse-dot {
      width: 8px; height: 8px; border-radius: 50%; background: #10B981;
      box-shadow: 0 0 8px #10B981; animation: pulseAnim 2s infinite;
    }
    @keyframes pulseAnim {
      0% { transform: scale(0.95); opacity: 0.6; }
      50% { transform: scale(1.15); opacity: 1; }
      100% { transform: scale(0.95); opacity: 0.6; }
    }

    /* KPI Summary Stats */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    .stat-card {
      background: var(--surface-card);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .stat-card:hover { border-color: var(--border-hover); transform: translateY(-1px); }
    .stat-info p {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 4px;
    }
    .stat-info h3 { font-size: 24px; font-weight: 800; color: #FFFFFF; }
    .stat-icon {
      width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
    }

    /* Main Workspace Grid */
    .workspace-grid {
      display: grid;
      grid-template-columns: 390px 1fr;
      gap: 24px;
      align-items: start;
    }

    .panel-card {
      background: var(--surface-card);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6);
    }

    .panel-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 17px;
      font-weight: 700;
      color: #FFFFFF;
      margin-bottom: 18px;
    }
    .panel-title svg { color: var(--primary); }

    /* Mode Switcher Pills */
    .mode-switcher {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      background: rgba(9, 13, 22, 0.7);
      padding: 5px;
      border-radius: 12px;
      border: 1px solid var(--border);
      margin-bottom: 20px;
    }
    .mode-btn {
      padding: 10px 14px;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 700;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.2s;
      user-select: none;
    }
    .mode-btn.active.empresa {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 14px var(--orange-glow);
    }
    .mode-btn.active.personal {
      background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 14px var(--primary-glow);
    }

    /* Form Fields */
    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 7px; }
    
    .input-field, select.input-field {
      width: 100%;
      background: rgba(9, 13, 22, 0.85);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 12px 14px;
      color: #FFFFFF;
      font-size: 14px;
      outline: none;
      transition: all 0.2s;
    }
    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
    }

    /* Helper info notice */
    .info-notice {
      background: rgba(56, 116, 204, 0.1);
      border: 1px solid rgba(56, 116, 204, 0.25);
      border-radius: 10px;
      padding: 12px;
      font-size: 12px;
      line-height: 1.45;
      color: #93C5FD;
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }
    .info-notice.orange {
      background: rgba(231, 114, 34, 0.1);
      border-color: rgba(231, 114, 34, 0.3);
      color: #FDBA74;
    }

    /* Primary Action Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      border: none;
      padding: 13px 20px;
      border-radius: 11px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      transition: all 0.2s;
      box-shadow: 0 8px 20px -4px var(--orange-glow);
    }
    .btn:hover { transform: translateY(-1px); filter: brightness(1.05); }

    .btn-sm {
      padding: 7px 12px;
      font-size: 12px;
      border-radius: 8px;
      width: auto;
      font-weight: 600;
      cursor: pointer;
      border: none;
    }
    .btn-blue {
      background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 12px var(--primary-glow);
    }
    .btn-outline {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
    }
    .btn-outline:hover { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.2); }

    .btn-ghost {
      background: transparent;
      border: none;
      color: var(--text-dim);
      cursor: pointer;
      padding: 6px 8px;
      border-radius: 6px;
      transition: all 0.2s;
    }
    .btn-ghost:hover { color: var(--red); background: rgba(239, 68, 68, 0.12); }

    /* Generated Share Card */
    .link-share-card {
      margin-top: 18px;
      background: rgba(9, 13, 22, 0.7);
      border: 1px solid rgba(231, 114, 34, 0.35);
      border-radius: 12px;
      padding: 16px;
      display: none;
    }
    .share-input-row { display: flex; gap: 8px; margin: 10px 0; }
    .share-input-row input {
      flex: 1;
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 9px 12px;
      color: #93C5FD;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      outline: none;
    }

    /* Main Category Tabs */
    .category-nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      padding-bottom: 16px;
      margin-bottom: 18px;
    }
    .tabs-group {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .main-tab {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 10px 18px;
      border-radius: 12px;
      font-size: 13.5px;
      font-weight: 700;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      user-select: none;
      border: 1px solid rgba(255, 255, 255, 0.06);
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(10px);
    }
    .main-tab:hover {
      color: #FFFFFF;
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(255, 255, 255, 0.14);
      transform: translateY(-1px);
    }
    .main-tab.active {
      background: rgba(56, 116, 204, 0.18);
      border-color: rgba(56, 116, 204, 0.55);
      color: #93C5FD;
      box-shadow: 0 4px 18px -2px rgba(56, 116, 204, 0.3);
    }
    .main-tab.active.empresa-tab {
      background: linear-gradient(135deg, rgba(231, 114, 34, 0.22) 0%, rgba(208, 99, 25, 0.12) 100%);
      border-color: rgba(231, 114, 34, 0.55);
      color: #FFB074;
      box-shadow: 0 4px 18px -2px rgba(231, 114, 34, 0.25);
    }
    .main-tab .tab-badge {
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 999px;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #FFFFFF;
      font-weight: 700;
    }

    /* Subfilter Bar Toolbar */
    .subfilter-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      gap: 16px;
      background: rgba(13, 20, 36, 0.55);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.07);
      border-radius: 14px;
      padding: 10px 16px;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
      flex-wrap: wrap;
    }

    /* Left tools: Search & Group Select */
    .filter-tools-left {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      flex: 1;
      min-width: 0;
    }

    .search-box-wrapper {
      position: relative;
      display: inline-flex;
      align-items: center;
    }
    .search-box-wrapper svg {
      position: absolute;
      left: 12px;
      color: var(--text-dim);
      pointer-events: none;
      transition: color 0.2s;
    }
    .filter-search-input {
      background: rgba(8, 12, 22, 0.75);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 8px 14px 8px 34px;
      color: #FFFFFF;
      font-size: 12.5px;
      outline: none;
      width: 210px;
      transition: all 0.2s ease;
    }
    .filter-search-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
      width: 250px;
    }
    .search-box-wrapper:focus-within svg {
      color: var(--primary);
    }

    .group-dropdown-wrapper {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .group-dropdown-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #FDBA74;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      white-space: nowrap;
      user-select: none;
    }
    .group-filter-select {
      background: rgba(8, 12, 22, 0.75);
      border: 1px solid rgba(231, 114, 34, 0.35);
      border-radius: 10px;
      padding: 8px 34px 8px 12px;
      color: #FFB074;
      font-size: 12.5px;
      font-weight: 600;
      outline: none;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23FB923C'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 11px center;
      background-size: 13px;
      transition: all 0.2s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .group-filter-select:hover {
      border-color: var(--orange);
      background-color: rgba(231, 114, 34, 0.08);
      transform: translateY(-1px);
    }
    .group-filter-select:focus {
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-glow);
    }
    .group-filter-select option {
      background: #0B111E;
      color: #F8FAFC;
      padding: 10px;
    }

    .status-filter-pills {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(8, 12, 22, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 11px;
      padding: 3px;
      margin-left: auto;
      flex-shrink: 0;
    }
    .s-pill {
      padding: 6px 13px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      user-select: none;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .s-pill:hover {
      color: #FFFFFF;
      background: rgba(255, 255, 255, 0.04);
    }
    .s-pill.active {
      background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
      color: #FFFFFF;
      font-weight: 700;
      box-shadow: 0 2px 10px var(--primary-glow);
    }
    .status-dot-pulse {
      display: inline-block;
      width: 7px;
      height: 7px;
      background: #10B981;
      border-radius: 50%;
      box-shadow: 0 0 8px #10B981;
    }

    /* Custom Dropdown Styling in Form */
    select.input-field {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394A3B8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      background-size: 16px;
      padding-right: 38px;
      cursor: pointer;
    }
    select.input-field option {
      background: #0F172A;
      color: #F8FAFC;
      padding: 10px;
    }

    /* Sessions Table */
    .table-container {
      overflow-x: auto;
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(9, 13, 22, 0.45);
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }
    .table-container::-webkit-scrollbar {
      height: 6px;
    }
    .table-container::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.2);
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.12);
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.22);
    }

    table { width: 100%; border-collapse: collapse; text-align: left; }
    th {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A3B8;
      padding: 14px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(15, 23, 42, 0.65);
      white-space: nowrap;
    }
    table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
    th {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A3B8;
      padding: 14px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(15, 23, 42, 0.65);
      white-space: nowrap;
    }
    td {
      padding: 13px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      font-size: 13px;
      vertical-align: middle;
      transition: background 0.15s ease;
      white-space: nowrap;
    }
    tr:hover td { background: rgba(56, 116, 204, 0.045); }

    .code-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 12px;
      background: rgba(56, 116, 204, 0.12);
      border: 1px solid rgba(56, 116, 204, 0.3);
      color: #60A5FA;
      padding: 4px 9px;
      border-radius: 7px;
      letter-spacing: 0.03em;
      white-space: nowrap;
      user-select: none;
    }
    .code-badge-clickable {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .code-badge-clickable:hover {
      background: rgba(56, 116, 204, 0.25);
      border-color: rgba(56, 116, 204, 0.6);
      color: #93C5FD;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(56, 116, 204, 0.3);
    }

    .client-cell {
      display: flex;
      flex-direction: column;
      gap: 3px;
      white-space: nowrap;
    }
    .client-name-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #F8FAFC;
      letter-spacing: -0.01em;
      white-space: nowrap;
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 999px;
      white-space: nowrap;
    }
    .badge-online { 
      background: rgba(16, 185, 129, 0.12); 
      color: #34D399; 
      border: 1px solid rgba(16, 185, 129, 0.32);
      box-shadow: 0 0 12px rgba(16, 185, 129, 0.12);
    }
    .badge-waiting { 
      background: rgba(245, 158, 11, 0.12); 
      color: #FBBF24; 
      border: 1px solid rgba(245, 158, 11, 0.32); 
    }
    .badge-closed { 
      background: rgba(71, 85, 105, 0.2); 
      color: #94A3B8; 
      border: 1px solid rgba(100, 116, 139, 0.22); 
    }

    .tag-installed {
      font-size: 10.5px;
      font-weight: 600;
      background: rgba(231, 114, 34, 0.12);
      color: #FB923C;
      padding: 2px 7px;
      border-radius: 5px;
      border: 1px solid rgba(231, 114, 34, 0.25);
      display: inline-flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      width: fit-content;
    }
    .tag-personal {
      font-size: 10.5px;
      font-weight: 600;
      color: #94A3B8;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      padding: 2px 7px;
      border-radius: 5px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      width: fit-content;
    }

    .group-badge-pill {
      font-size: 12px;
      background: rgba(231, 114, 34, 0.1);
      color: #FB923C;
      border: 1px solid rgba(231, 114, 34, 0.25);
      padding: 3px 9px;
      border-radius: 6px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      white-space: nowrap;
    }

    .id-pill {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 13px;
      color: #FB923C;
      background: rgba(231, 114, 34, 0.08);
      padding: 3.5px 9px;
      border-radius: 7px;
      border: 1px solid rgba(231, 114, 34, 0.25);
      letter-spacing: 0.03em;
      display: inline-flex;
      align-items: center;
      white-space: nowrap;
    }

    /* Standardized Row Action Buttons */
    .actions-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 6px;
      white-space: nowrap;
    }
    .btn-act {
      height: 31px;
      padding: 0 11px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      white-space: nowrap;
      transition: all 0.2s ease;
      box-sizing: border-box;
      line-height: 1;
      text-decoration: none;
      user-select: none;
    }
    .btn-act svg {
      flex-shrink: 0;
    }
    .btn-act-connect {
      background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
      color: #FFFFFF;
      border: 1px solid rgba(56, 116, 204, 0.5);
      box-shadow: 0 2px 10px rgba(56, 116, 204, 0.3);
    }
    .btn-act-connect:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(56, 116, 204, 0.45);
      filter: brightness(1.1);
    }
    .btn-act-reconnect {
      background: rgba(56, 116, 204, 0.08);
      color: #93C5FD;
      border: 1px solid rgba(56, 116, 204, 0.35);
    }
    .btn-act-reconnect:hover {
      background: rgba(56, 116, 204, 0.18);
      border-color: rgba(56, 116, 204, 0.55);
      color: #FFFFFF;
      transform: translateY(-1px);
    }
    .btn-act-copy {
      background: rgba(255, 255, 255, 0.03);
      color: #CBD5E1;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .btn-act-copy:hover {
      background: rgba(231, 114, 34, 0.12);
      border-color: rgba(231, 114, 34, 0.45);
      color: #FB923C;
      transform: translateY(-1px);
      box-shadow: 0 2px 10px rgba(231, 114, 34, 0.2);
    }
    .btn-act-delete {
      width: 31px;
      height: 31px;
      padding: 0;
      background: transparent;
      color: var(--text-dim);
      border: 1px solid transparent;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .btn-act-delete:hover {
      background: rgba(239, 68, 68, 0.12);
      border-color: rgba(239, 68, 68, 0.3);
      color: #F87171;
      transform: scale(1.08);
    }

    .toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: #1E293B;
      color: #FFFFFF;
      border: 1px solid var(--primary);
      padding: 12px 20px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s;
      pointer-events: none;
      z-index: 1000;
    }
    .toast.show { opacity: 1; transform: translateY(0); }

    /* Modal: Nuevo Grupo Corporativo */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(3, 7, 18, 0.78);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      z-index: 9999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.22s ease;
    }
    .modal-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }
    .modal-card {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.96) 0%, rgba(10, 15, 29, 0.98) 100%);
      border: 1px solid rgba(231, 114, 34, 0.35);
      border-radius: 20px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(231, 114, 34, 0.25);
      overflow: hidden;
      transform: scale(0.92) translateY(12px);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-overlay.active .modal-card {
      transform: scale(1) translateY(0);
    }
    .modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(231, 114, 34, 0.05);
    }
    .modal-title-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .modal-icon-badge {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(231, 114, 34, 0.25) 0%, rgba(208, 99, 25, 0.15) 100%);
      border: 1px solid rgba(231, 114, 34, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .modal-title {
      font-size: 16px;
      font-weight: 700;
      color: #FFFFFF;
      margin: 0;
      letter-spacing: -0.01em;
    }
    .modal-subtitle {
      font-size: 12px;
      color: #94A3B8;
      margin: 2px 0 0 0;
    }
    .modal-close-btn {
      background: transparent;
      border: none;
      color: #94A3B8;
      font-size: 24px;
      line-height: 1;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .modal-close-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
    }
    .modal-body {
      padding: 24px;
    }
    .modal-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #CBD5E1;
      margin-bottom: 8px;
    }
    .modal-input {
      width: 100%;
      background: rgba(0, 0, 0, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      padding: 13px 16px;
      font-size: 14px;
      color: #FFFFFF;
      outline: none;
      transition: all 0.2s;
    }
    .modal-input:focus {
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-glow);
      background: rgba(0, 0, 0, 0.6);
    }
    .modal-hint {
      font-size: 12px;
      color: #64748B;
      margin: 10px 0 0 0;
      line-height: 1.4;
    }
    .modal-footer {
      padding: 16px 24px;
      background: rgba(0, 0, 0, 0.25);
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
    }
    .modal-btn-cancel {
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 600;
      border-radius: 10px;
      cursor: pointer;
    }
    .btn-save-group {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      border: none;
      padding: 9px 20px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px -2px var(--orange-glow);
      transition: all 0.2s;
    }
    .btn-save-group:hover {
      transform: translateY(-1px);
      filter: brightness(1.08);
    }

    /* Row Action Buttons Enhancement */
    .btn-copy-link {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #CBD5E1;
      padding: 7px 11px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }
    .btn-copy-link:hover {
      background: rgba(56, 116, 204, 0.15);
      border-color: rgba(56, 116, 204, 0.45);
      color: #93C5FD;
      transform: translateY(-1px);
      box-shadow: 0 2px 10px rgba(56, 116, 204, 0.25);
    }
    .code-badge-clickable {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .code-badge-clickable:hover {
      background: rgba(56, 116, 204, 0.25);
      border-color: rgba(56, 116, 204, 0.6);
      color: #93C5FD;
      transform: scale(1.04);
    }
    /* User Header Chip & Management Styles */
    .header-right-tools {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .user-profile-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(15, 23, 42, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.09);
      padding: 5px 10px 5px 6px;
      border-radius: 999px;
      backdrop-filter: blur(10px);
    }
    .user-avatar-circle {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      font-weight: 800;
      font-size: 13px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px var(--orange-glow);
    }
    .user-info-text {
      display: flex;
      flex-direction: column;
      gap: 1px;
    }
    .user-display-name {
      font-size: 12.5px;
      font-weight: 700;
      color: #FFFFFF;
      line-height: 1.2;
    }
    .user-role-pill {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.02em;
      line-height: 1;
    }
    .role-admin {
      color: #FBBF24;
    }
    .role-tech {
      color: #60A5FA;
    }
    .btn-user-manage {
      background: rgba(231, 114, 34, 0.12);
      border: 1px solid rgba(231, 114, 34, 0.3);
      color: #FB923C;
      padding: 6px 11px;
      font-size: 11.5px;
      font-weight: 700;
      border-radius: 999px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s ease;
    }
    .btn-user-manage:hover {
      background: rgba(231, 114, 34, 0.25);
      border-color: rgba(231, 114, 34, 0.55);
      transform: translateY(-1px);
    }
    .btn-logout {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #CBD5E1;
      padding: 6px 11px;
      font-size: 11.5px;
      font-weight: 600;
      border-radius: 999px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s ease;
    }
    .btn-logout:hover {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.35);
      color: #F87171;
      transform: translateY(-1px);
    }
    .user-create-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 16px;
    }
    /* Symmetrical Confirmation Dialog Styles */
    .confirm-modal-footer {
      padding: 16px 24px 20px 24px;
      background: rgba(0, 0, 0, 0.35);
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      width: 100%;
      box-sizing: border-box;
    }
    .confirm-btn {
      height: 44px;
      padding: 0 16px;
      border-radius: 12px;
      font-size: 13.5px;
      font-weight: 700;
      letter-spacing: 0.01em;
      white-space: nowrap;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-sizing: border-box;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      width: 100%;
      user-select: none;
    }
    .confirm-btn-cancel {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.14);
      color: #E2E8F0;
    }
    .confirm-btn-cancel:hover {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(255, 255, 255, 0.25);
      color: #FFFFFF;
      transform: translateY(-1px);
    }
    .confirm-btn-danger {
      background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
      border: 1px solid rgba(239, 68, 68, 0.5);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px rgba(239, 68, 68, 0.45);
    }
    .confirm-btn-danger:hover {
      filter: brightness(1.12);
      transform: translateY(-1px);
      box-shadow: 0 6px 22px -2px rgba(239, 68, 68, 0.65);
    }
    .confirm-btn-warning {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      border: 1px solid rgba(231, 114, 34, 0.5);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px var(--orange-glow);
    }
    .confirm-btn-warning:hover {
      filter: brightness(1.12);
      transform: translateY(-1px);
      box-shadow: 0 6px 22px -2px rgba(231, 114, 34, 0.65);
    }
  </style>
</head>
<body>

  <div class="container">
    
    <!-- Top Bar -->
    <header>
      <div class="brand-section">
        <img src="flutter/assets/logo_dark.png?v=2" alt="OptimaIT Logo">
      </div>

      <div class="header-right-tools">
        <div class="server-badge">
          <div class="pulse-dot"></div>
          <?php $relayHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '178.156.246.205'; ?>
          <span>Servidor Relay: <?= htmlspecialchars($relayHost) ?> (En línea)</span>
        </div>

        <!-- User Profile Chip -->
        <div class="user-profile-badge">
          <div class="user-avatar-circle">
            <?= strtoupper(substr($currentUser['name'] ?? $currentUser['username'], 0, 1)) ?>
          </div>
          <div class="user-info-text">
            <span class="user-display-name"><?= htmlspecialchars($currentUser['name'] ?? $currentUser['username']) ?></span>
            <span class="user-role-pill <?= $isAdmin ? 'role-admin' : 'role-tech' ?>">
              <?= $isAdmin ? '👑 Administrador' : '🛠️ Técnico' ?>
            </span>
          </div>

          <?php if ($isAdmin): ?>
          <button type="button" class="btn-user-manage" onclick="openUsersModal()" title="Gestionar usuarios del sistema">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>Usuarios</span>
          </button>
          <?php endif; ?>

          <button type="button" class="btn-logout" onclick="logoutUser()" title="Cerrar sesión">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span>Salir</span>
          </button>
        </div>
      </div>
    </header>

    <!-- KPI Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-info">
          <p>Clientes En Línea</p>
          <h3 id="statOnline">0</h3>
        </div>
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.828a5 5 0 010-7.072m7.072 0a5 5 0 010 7.072M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p>Equipos Empresa</p>
          <h3 id="statEmpresas">0</h3>
        </div>
        <div class="stat-icon" style="background: rgba(231, 114, 34, 0.15); color: #E77222;">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p>Personas Naturales</p>
          <h3 id="statPersonal">0</h3>
        </div>
        <div class="stat-icon" style="background: rgba(56, 116, 204, 0.15); color: #3874CC;">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <p>Total Registrados</p>
          <h3 id="statTotal">0</h3>
        </div>
        <div class="stat-icon" style="background: rgba(148, 163, 184, 0.15); color: #94A3B8;">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
      </div>
    </div>

    <!-- Workspace Grid -->
    <div class="workspace-grid">
      
      <!-- Generator Left Column -->
      <div class="panel-card">
        <div class="panel-title">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
          Nueva Sesión de Soporte
        </div>

        <form id="createForm" onsubmit="handleCreateSession(event)">
          
          <!-- Mode Switcher -->
          <label>¿A quién va dirigido el soporte?</label>
          <div class="mode-switcher">
            <div class="mode-btn empresa active" id="btnModeEmpresa" onclick="setCreationMode('empresa')">
              🏢 Empresa
            </div>
            <div class="mode-btn personal" id="btnModePersonal" onclick="setCreationMode('personal')">
              👤 Persona Natural
            </div>
          </div>
          <input type="hidden" id="clientTypeInput" value="empresa">

          <!-- Notice explanation -->
          <div class="info-notice orange" id="noticeText">
            <span>🏢 <strong>Empresa:</strong> Se genera instalador con elevación UAC y servicio en segundo plano para iniciar con Windows.</span>
          </div>

          <!-- Client / Machine Name -->
          <div class="form-group">
            <label id="lblClientName">Nombre de la Empresa o Equipo</label>
            <input type="text" id="clientNameInput" class="input-field" placeholder="Ej: Sucursal Central / Caja 01" required>
          </div>

          <!-- Empresa Group Selection (VISIBLE FOR EMPRESA) -->
          <div class="form-group" id="empresaGroupContainer">
            <label for="groupSelect">Asignar a Grupo Corporativo / Sede</label>
            <div style="display:flex; gap:8px;">
              <select id="groupSelect" class="input-field"></select>
              <button type="button" class="btn btn-outline btn-sm" onclick="promptNewGroup()" title="Crear nueva sede o grupo">+</button>
            </div>
          </div>

          <button type="submit" class="btn" id="btnSubmit">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <span id="btnSubmitText">Generar Enlace para Empresa</span>
          </button>
        </form>

        <!-- Generated Link Card -->
        <div class="link-share-card" id="linkBox">
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:11px; font-weight:700; color:var(--orange);" id="shareModeTitle">ENLACE GENERADO</span>
            <span id="badgeShareType" style="font-size:10px; padding:2px 7px; border-radius:4px; font-weight:700;"></span>
          </div>
          <div class="share-input-row">
            <input type="text" id="generatedUrl" readonly>
            <button type="button" class="btn btn-blue btn-sm" onclick="copyLink()">📋 Copiar</button>
          </div>
          <p style="font-size:11px; color:var(--text-dim); line-height:1.4;" id="shareExplanation"></p>
        </div>
      </div>

      <!-- Right Column: Organized Workspace -->
      <div class="panel-card">
        
        <!-- Category Navigation -->
        <div class="category-nav">
          <div class="tabs-group">
            <div class="main-tab empresa-tab active" data-cat="empresa" onclick="setCategoryTab('empresa')">
              🏢 Empresas <span class="tab-badge" id="countTabEmpresa">0</span>
            </div>
            <div class="main-tab" data-cat="personal" onclick="setCategoryTab('personal')">
              👤 Personas Naturales <span class="tab-badge" id="countTabPersonal">0</span>
            </div>
            <div class="main-tab" data-cat="all" onclick="setCategoryTab('all')">
              🌐 Todos los Equipos <span class="tab-badge" id="countTabAll">0</span>
            </div>
          </div>
        </div>

        <!-- Subfilters Bar Toolbar -->
        <div class="subfilter-bar">
          
          <div class="filter-tools-left">
            <!-- Search Box -->
            <div class="search-box-wrapper">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
              <input type="text" id="searchInput" class="filter-search-input" placeholder="Buscar equipo o ID..." oninput="handleSearchInput(event)">
            </div>

            <!-- Corporate Group Dropdown Filter -->
            <div class="group-dropdown-wrapper" id="groupFilterBox">
              <span class="group-dropdown-label">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Grupo:
              </span>
              <select id="groupFilterSelect" class="group-filter-select" onchange="selectGroupDropdown(this.value)">
                <option value="all">🏢 Todos los grupos</option>
              </select>
            </div>
          </div>

          <!-- Status pills -->
          <div class="status-filter-pills">
            <div class="s-pill active" data-status="all" onclick="setStatusFilter('all')">Todos</div>
            <div class="s-pill" data-status="online" onclick="setStatusFilter('online')"><span class="status-dot-pulse"></span> En Línea</div>
            <div class="s-pill" data-status="inactive" onclick="setStatusFilter('inactive')">Inactivos</div>
          </div>
        </div>

        <!-- Sessions Table -->
        <div class="table-container">
          <table>
            <thead>
              <tr id="tableHeaderRow">
                <th style="width:115px;">Código</th>
                <th>Cliente / Equipo</th>
                <th id="thColGrupo" style="width:160px;">Grupo Corporativo</th>
                <th style="width:125px;">Estado</th>
                <th style="width:145px;">ID Remoto</th>
                <th style="text-align:right; width:250px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="sessionsBody">
              <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--text-dim);">Cargando registros...</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>

  </div>

  <div class="toast" id="toastBox">
    <span id="toastMsg">Acción completada</span>
  </div>

  <!-- Modal: Crear Nuevo Grupo Corporativo -->
  <div id="newGroupModal" class="modal-overlay" onclick="handleModalBackdropClick(event)">
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-icon-badge">🏢</div>
          <div>
            <h3 class="modal-title">Nuevo Grupo Corporativo</h3>
            <p class="modal-subtitle">Organiza tus equipos por empresa, sucursal o sede</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeGroupModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body">
        <label for="newGroupNameInput" class="modal-label">Nombre de la Empresa o Sede</label>
        <input type="text" id="newGroupNameInput" class="modal-input" placeholder="Ej: Sucursal Norte / Farmacia Central / Almacén" autocomplete="off" onkeydown="handleModalKeydown(event)">
        <p class="modal-hint">💡 Este nombre se agregará a tu selector de empresas y a los filtros de equipos.</p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-btn-cancel" onclick="closeGroupModal()">Cancelar</button>
        <button type="button" class="btn btn-save-group" id="btnSaveGroup" onclick="submitNewGroup()">
          <span>Guardar Grupo</span>
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
        </button>
      </div>
    </div>
  </div>

  <?php if ($isAdmin): ?>
  <!-- Modal: Gestión de Usuarios (Solo Administrador) -->
  <div id="usersModal" class="modal-overlay" onclick="handleUsersModalBackdropClick(event)">
    <div class="modal-card" style="max-width: 660px;">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-icon-badge" style="background:linear-gradient(135deg, rgba(56, 116, 204, 0.25) 0%, rgba(37, 99, 235, 0.15) 100%); border-color:rgba(56, 116, 204, 0.4);">
            👥
          </div>
          <div>
            <h3 class="modal-title">Gestión de Usuarios</h3>
            <p class="modal-subtitle">Administra las cuentas técnicas con acceso al panel</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeUsersModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body" style="padding: 20px 24px;">
        
        <!-- Formulario Crear Usuario -->
        <div class="user-create-box">
          <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 12px; display:flex; align-items:center; gap:6px;">
            <span>➕ Crear Nuevo Usuario</span>
          </div>
          <form id="createUserForm" onsubmit="handleCreateUser(event)" style="display:grid; grid-template-columns: 1fr 1fr 1fr 120px; gap: 10px; align-items: end;">
            <div>
              <label class="modal-label" style="font-size:11px;">Nombre</label>
              <input type="text" id="newUserName" class="modal-input" style="padding:9px 12px; font-size:13px;" placeholder="Ej: Juan Perez" required>
            </div>
            <div>
              <label class="modal-label" style="font-size:11px;">Usuario</label>
              <input type="text" id="newUserUsername" class="modal-input" style="padding:9px 12px; font-size:13px;" placeholder="Ej: jperez" required autocomplete="off">
            </div>
            <div>
              <label class="modal-label" style="font-size:11px;">Contraseña</label>
              <input type="password" id="newUserPassword" class="modal-input" style="padding:9px 12px; font-size:13px;" placeholder="Mínimo 4 carácteres" required autocomplete="new-password">
            </div>
            <div>
              <label class="modal-label" style="font-size:11px;">Rol</label>
              <select id="newUserRole" class="modal-input" style="padding:9px 10px; font-size:12.5px; cursor:pointer;">
                <option value="tecnico">Técnico</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div style="grid-column: 1 / -1; display:flex; justify-content:flex-end; margin-top: 4px;">
              <button type="submit" class="btn btn-save-group" id="btnSubmitUser" style="padding:8px 18px; font-size:12.5px;">
                <span>+ Guardar Usuario</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Tabla de Usuarios Registrados -->
        <div style="margin-top: 20px;">
          <div style="font-size: 12px; font-weight: 700; color: #94A3B8; text-transform:uppercase; letter-spacing:0.06em; margin-bottom: 10px;">
            Usuarios Registrados
          </div>
          <div style="max-height: 220px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; background: rgba(8,12,22,0.45);">
            <table style="width: 100%;">
              <thead>
                <tr>
                  <th style="padding: 10px 14px; font-size: 11px;">Usuario</th>
                  <th style="padding: 10px 14px; font-size: 11px;">Nombre</th>
                  <th style="padding: 10px 14px; font-size: 11px;">Rol</th>
                  <th style="padding: 10px 14px; font-size: 11px; text-align:right;">Acción</th>
                </tr>
              </thead>
              <tbody id="usersTableBody">
                <tr><td colspan="4" style="text-align:center; padding:20px; color:#64748B;">Cargando usuarios...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <div class="modal-footer" style="padding: 12px 24px;">
        <button type="button" class="btn btn-outline modal-btn-cancel" onclick="closeUsersModal()">Cerrar</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modal: Confirmación Elegante -->
  <div id="confirmActionModal" class="modal-overlay" style="z-index: 10005;" onclick="handleConfirmModalBackdropClick(event)">
    <div class="modal-card" style="max-width: 440px; border-color: rgba(239, 68, 68, 0.35); box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(239, 68, 68, 0.2);">
      <div class="modal-header" style="background: rgba(239, 68, 68, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.07);">
        <div class="modal-title-wrap">
          <div class="modal-icon-badge" id="confirmModalIcon" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.1) 100%); border-color: rgba(239, 68, 68, 0.4);">
            ⚠️
          </div>
          <div>
            <h3 class="modal-title" id="confirmModalTitle">Confirmar Acción</h3>
            <p class="modal-subtitle">Por favor confirma para continuar</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeConfirmDialog()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body" style="padding: 22px 24px;">
        <p id="confirmModalMessage" style="font-size: 13.5px; color: #E2E8F0; line-height: 1.5; margin: 0;"></p>
      </div>

      <div class="confirm-modal-footer">
        <button type="button" class="confirm-btn confirm-btn-cancel" onclick="closeConfirmDialog()">Cancelar</button>
        <button type="button" id="btnConfirmAction" class="confirm-btn confirm-btn-danger" onclick="executeConfirmedAction()">Confirmar</button>
      </div>
    </div>
  </div>

  <script>
    let rawSessions = [];
    let customGroups = [];
    let currentCategory = 'empresa'; // View defaults to Empresa
    let currentGroupFilter = 'all';
    let currentStatusFilter = 'all';

    function showToast(msg) {
      const toast = document.getElementById('toastBox');
      document.getElementById('toastMsg').innerText = msg;
      toast.classList.add('show');
      setTimeout(() => { toast.classList.remove('show'); }, 2800);
    }

    function setCreationMode(type) {
      document.getElementById('clientTypeInput').value = type;
      
      const btnP = document.getElementById('btnModePersonal');
      const btnE = document.getElementById('btnModeEmpresa');
      const notice = document.getElementById('noticeText');
      const grpCont = document.getElementById('empresaGroupContainer');
      const lblName = document.getElementById('lblClientName');
      const btnText = document.getElementById('btnSubmitText');

      if (type === 'empresa') {
        btnP.classList.remove('active');
        btnE.classList.add('active');
        notice.className = 'info-notice orange';
        notice.innerHTML = `<span>🏢 <strong>Empresa:</strong> Se genera instalador con elevación UAC y servicio en segundo plano para iniciar con Windows.</span>`;
        grpCont.style.display = 'block';
        lblName.innerText = "Nombre de la Empresa o Equipo";
        btnText.innerText = "Generar Enlace para Empresa";
      } else {
        btnE.classList.remove('active');
        btnP.classList.add('active');
        notice.className = 'info-notice';
        notice.innerHTML = `<span>👤 <strong>Persona Natural:</strong> Asistente temporal directo sin requerir instalación. Se asigna automáticamente a Personas Naturales.</span>`;
        grpCont.style.display = 'none';
        lblName.innerText = "Nombre del Cliente";
        btnText.innerText = "Generar Enlace para Persona Natural";
      }
    }

    let currentSearchQuery = '';

    function handleSearchInput(e) {
      currentSearchQuery = e.target.value.trim();
      renderSessions();
    }

    function setCategoryTab(cat) {
      currentCategory = cat;
      document.querySelectorAll('.main-tab').forEach(t => {
        t.classList.toggle('active', t.getAttribute('data-cat') === cat);
      });

      // Show group dropdown filter ONLY when viewing Empresa or Todos
      const grpFilterBox = document.getElementById('groupFilterBox');
      const thGrupo = document.getElementById('thColGrupo');
      if (cat === 'personal') {
        if (grpFilterBox) grpFilterBox.style.display = 'none';
        if (thGrupo) thGrupo.style.display = 'none';
      } else {
        if (grpFilterBox) grpFilterBox.style.display = 'inline-flex';
        if (thGrupo) thGrupo.style.display = 'table-cell';
      }

      renderSessions();
    }

    function selectGroupDropdown(groupName) {
      currentGroupFilter = groupName;
      renderSessions();
    }

    function setStatusFilter(status) {
      currentStatusFilter = status;
      document.querySelectorAll('.s-pill').forEach(f => {
        f.classList.toggle('active', f.getAttribute('data-status') === status);
      });
      renderSessions();
    }

    async function loadSessions() {
      try {
        const res = await fetch('api.php?action=list_sessions');
        const data = await res.json();
        if (data.success) {
          rawSessions = data.sessions || [];
          customGroups = data.groups || ['Sucursales', 'Oficina Central', 'Soporte VIP'];
          updateGroupControls();
          updateKPIs();
          renderSessions();
        }
      } catch (err) {
        console.error("Error al cargar sesiones", err);
      }
    }

    function updateGroupControls() {
      const companyGroups = new Set();
      // Add existing from customGroups
      customGroups.forEach(g => { 
        if (g && g !== 'General' && g !== 'Personas Naturales') {
          companyGroups.add(g);
        } 
      });
      // Add existing from active sessions
      rawSessions.forEach(s => {
        if (s.client_type === 'empresa' && s.group_name && s.group_name !== 'Personas Naturales' && s.group_name !== 'General') {
          companyGroups.add(s.group_name);
        }
      });
      
      const groupArr = Array.from(companyGroups);
      if (groupArr.length === 0) {
        groupArr.push('Oficina Central', 'Sucursales');
      }

      // 1. Populate Generator group select
      const sel = document.getElementById('groupSelect');
      const curr = sel.value;
      sel.innerHTML = groupArr.map(g => `<option value="${escapeHtml(g)}">🏢 ${escapeHtml(g)}</option>`).join('');
      if (curr && groupArr.includes(curr)) {
        sel.value = curr;
      }

      // 2. Populate Group Filter Dropdown in Table Toolbar
      const grpFilterSelect = document.getElementById('groupFilterSelect');
      if (grpFilterSelect) {
        let optHtml = `<option value="all">🏢 Todos los grupos</option>`;
        groupArr.forEach(g => {
          const isSel = (currentGroupFilter === g) ? 'selected' : '';
          optHtml += `<option value="${escapeHtml(g)}" ${isSel}>🏢 ${escapeHtml(g)}</option>`;
        });
        grpFilterSelect.innerHTML = optHtml;
        if (currentGroupFilter && groupArr.includes(currentGroupFilter)) {
          grpFilterSelect.value = currentGroupFilter;
        } else {
          grpFilterSelect.value = 'all';
          currentGroupFilter = 'all';
        }
      }
    }

    function updateKPIs() {
      let online = 0;
      let empresas = 0;
      let personal = 0;

      rawSessions.forEach(s => {
        if (s.status === 'online') online++;
        if (s.client_type === 'empresa') empresas++;
        else personal++;
      });

      document.getElementById('statOnline').innerText = online;
      document.getElementById('statEmpresas').innerText = empresas;
      document.getElementById('statPersonal').innerText = personal;
      document.getElementById('statTotal').innerText = rawSessions.length;

      document.getElementById('countTabEmpresa').innerText = empresas;
      document.getElementById('countTabPersonal').innerText = personal;
      document.getElementById('countTabAll').innerText = rawSessions.length;
    }

    function renderSessions() {
      const tbody = document.getElementById('sessionsBody');
      const isPersonalView = (currentCategory === 'personal');

      // 1. Filter by category
      let list = rawSessions;
      if (currentCategory === 'empresa') {
        list = list.filter(s => s.client_type === 'empresa');
      } else if (currentCategory === 'personal') {
        list = list.filter(s => s.client_type !== 'empresa');
      }

      // 2. Filter by group (when viewing Empresa or All)
      if (currentCategory !== 'personal' && currentGroupFilter !== 'all') {
        list = list.filter(s => (s.group_name || '') === currentGroupFilter);
      }

      // 3. Filter by status
      if (currentStatusFilter === 'online') {
        list = list.filter(s => s.status === 'online');
      } else if (currentStatusFilter === 'inactive') {
        list = list.filter(s => s.status === 'closed' || s.status === 'idle' || s.status === 'offline');
      }

      // 4. Filter by search query
      if (currentSearchQuery) {
        const q = currentSearchQuery.toLowerCase();
        list = list.filter(s => 
          (s.client_name && s.client_name.toLowerCase().includes(q)) ||
          (s.session_id && s.session_id.toLowerCase().includes(q)) ||
          (s.rustdesk_id && s.rustdesk_id.toLowerCase().includes(q)) ||
          (s.group_name && s.group_name.toLowerCase().includes(q))
        );
      }

      const colSpan = isPersonalView ? 5 : 6;
      if (!list || list.length === 0) {
        let emptyMsg = "No hay registros en esta sección.";
        if (currentCategory === 'empresa') {
          emptyMsg = currentGroupFilter === 'all' 
            ? "No hay equipos empresariales registrados aún. Genere un enlace para instalar el primer equipo."
            : `No hay equipos asignados al grupo "${escapeHtml(currentGroupFilter)}".`;
        } else if (currentCategory === 'personal') {
          emptyMsg = "No hay asistencias temporales de Personas Naturales activas.";
        }
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding:45px 20px; color:var(--text-dim); font-size:13.5px;"><div style="font-size:28px; margin-bottom:10px;">📋</div>${emptyMsg}</td></tr>`;
        return;
      }

      tbody.innerHTML = list.map(s => {
        const isOnline = s.status === 'online';
        const isWaiting = s.status === 'waiting';
        const isEmpresa = s.client_type === 'empresa';

        let badgeStatus = '';
        if (isOnline) {
          badgeStatus = `<span class="badge-status badge-online">● EN LÍNEA</span>`;
        } else if (isWaiting) {
          badgeStatus = `<span class="badge-status badge-waiting">⏳ En espera</span>`;
        } else {
          badgeStatus = `<span class="badge-status badge-closed">Inactivo</span>`;
        }

        const tagType = isEmpresa 
          ? `<span class="tag-installed">🏢 Empresa (Auto-inicio)</span>` 
          : `<span class="tag-personal">👤 Temporal</span>`;

        const groupCell = !isPersonalView ? `
          <td>
            <span class="group-badge-pill">
              🏢 ${escapeHtml(s.group_name || 'General')}
            </span>
          </td>
        ` : '';

        const idDisplay = s.rustdesk_id 
          ? `<span class="id-pill">${s.rustdesk_id}</span>` 
          : `<span style="font-size:12px; color:var(--text-dim); white-space:nowrap;">⏳ Pendiente</span>`;

        let connectBtn = '';
        if (s.rustdesk_id) {
          if (isOnline) {
            connectBtn = `
              <button onclick="connectSession('${s.rustdesk_id}')" class="btn-act btn-act-connect" title="Conectar por RustDesk a ${s.rustdesk_id}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <span>Conectar</span>
              </button>
            `;
          } else {
            connectBtn = `
              <button onclick="connectSession('${s.rustdesk_id}')" class="btn-act btn-act-reconnect" title="Intentar conectar con ${s.rustdesk_id}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span>Conectar</span>
              </button>
            `;
          }
        }

        const copyBtn = `
          <button onclick="copySpecificUrl('${s.session_id}')" class="btn-act btn-act-copy" title="Copiar enlace de asistencia para este equipo">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
            <span>Copiar Link</span>
          </button>
        `;

        const deleteBtn = `
          <button onclick="deleteSessionPermanently('${s.session_id}')" class="btn-act-delete" title="Eliminar definitivamente">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
          </button>
        `;

        return `
          <tr>
            <td><span class="code-badge code-badge-clickable" onclick="copySpecificUrl('${s.session_id}')" title="Clic para copiar enlace">${s.session_id}</span></td>
            <td>
              <div class="client-cell">
                <span class="client-name-title">${escapeHtml(s.client_name)}</span>
                <div>${tagType}</div>
              </div>
            </td>
            ${groupCell}
            <td>${badgeStatus}</td>
            <td>${idDisplay}</td>
            <td style="text-align:right;">
              <div class="actions-wrap">
                ${connectBtn}
                ${copyBtn}
                ${deleteBtn}
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    async function handleCreateSession(e) {
      e.preventDefault();
      const name = document.getElementById('clientNameInput').value.trim();
      const type = document.getElementById('clientTypeInput').value;
      const group = (type === 'empresa') ? document.getElementById('groupSelect').value : 'Personas Naturales';
      const btn = document.getElementById('btnSubmit');

      btn.disabled = true;
      btn.innerText = "Generando...";

      try {
        const res = await fetch(`api.php?action=create_session&client_name=${encodeURIComponent(name)}&client_type=${type}&group_name=${encodeURIComponent(group)}`);
        const data = await res.json();
        if (data.success) {
          document.getElementById('generatedUrl').value = data.join_url;
          document.getElementById('linkBox').style.display = 'block';
          
          if (type === 'empresa') {
            document.getElementById('shareModeTitle').innerText = 'ENLACE DE ASISTENCIA EMPRESARIAL';
            document.getElementById('badgeShareType').innerText = 'EMPRESA';
            document.getElementById('badgeShareType').style.background = 'rgba(231,114,34,0.2)';
            document.getElementById('badgeShareType').style.color = '#FB923C';
            document.getElementById('shareExplanation').innerText = 'Descarga con elevación UAC. Se instalará de forma permanente e iniciará con Windows.';
          } else {
            document.getElementById('shareModeTitle').innerText = 'ENLACE PERSONA NATURAL';
            document.getElementById('badgeShareType').innerText = 'NATURAL';
            document.getElementById('badgeShareType').style.background = 'rgba(56,116,204,0.2)';
            document.getElementById('badgeShareType').style.color = '#93C5FD';
            document.getElementById('shareExplanation').innerText = 'Asistente temporal de 1 solo clic. No instala nada permanente en la computadora del cliente.';
          }

          document.getElementById('clientNameInput').value = '';
          showToast(`Sesión para ${data.session.client_name} generada`);
          loadSessions();
        }
      } catch (err) {
        alert("Error al generar sesión");
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> <span id="btnSubmitText">${type === 'empresa' ? 'Generar Enlace para Empresa' : 'Generar Enlace para Persona Natural'}</span>`;
      }
    }

    function promptNewGroup() {
      const modal = document.getElementById('newGroupModal');
      const input = document.getElementById('newGroupNameInput');
      input.value = '';
      modal.classList.add('active');
      setTimeout(() => input.focus(), 60);
    }

    function closeGroupModal() {
      const modal = document.getElementById('newGroupModal');
      modal.classList.remove('active');
    }

    function handleModalBackdropClick(e) {
      if (e.target.id === 'newGroupModal') {
        closeGroupModal();
      }
    }

    function handleModalKeydown(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        submitNewGroup();
      } else if (e.key === 'Escape') {
        closeGroupModal();
      }
    }

    async function submitNewGroup() {
      const input = document.getElementById('newGroupNameInput');
      const gName = input.value.trim();
      if (!gName) {
        input.focus();
        showToast("⚠️ Ingrese un nombre de grupo válido");
        return;
      }
      const btn = document.getElementById('btnSaveGroup');
      btn.disabled = true;
      btn.innerText = "Guardando...";
      try {
        const res = await fetch(`api.php?action=add_group&group_name=${encodeURIComponent(gName)}`);
        const data = await res.json();
        if (data.success) {
          showToast(`Grupo corporativo "${gName}" agregado con éxito`);
          closeGroupModal();
          await loadSessions();
          // Select newly created group in the generator select
          const sel = document.getElementById('groupSelect');
          if (sel) sel.value = gName;
        } else {
          showToast(data.error || "Error al agregar grupo");
        }
      } catch (err) {
        showToast("Error al agregar grupo");
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<span>Guardar Grupo</span> <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>`;
      }
    }

    function copyLink() {
      const url = document.getElementById('generatedUrl').value;
      doClipboardCopy(url, "¡Enlace copiado al portapapeles!");
    }

    function copySpecificUrl(sId) {
      const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
      const url = `${baseUrl}asistencia.php?sesion=${sId}`;
      doClipboardCopy(url, `¡Enlace de sesión ${sId} copiado al portapapeles!`);
    }

    function doClipboardCopy(text, successToast) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          showToast(successToast);
        }).catch(() => fallbackClipboardCopy(text, successToast));
      } else {
        fallbackClipboardCopy(text, successToast);
      }
    }

    function fallbackClipboardCopy(text, successToast) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand('copy');
        showToast(successToast);
      } catch (e) {
        showToast("No se pudo copiar automáticamente");
      }
      document.body.removeChild(ta);
    }

    function deleteSessionPermanently(sId) {
      showConfirmDialog({
        title: 'Eliminar Registro de Equipo',
        message: `¿Deseas eliminar la sesión <strong>${escapeHtml(sId)}</strong> del registro?<br><span style="font-size:12px; color:#94A3B8;">El equipo se removerá de tu lista y filtros de soporte.</span>`,
        iconHtml: '🗑️',
        confirmText: 'Sí, Eliminar',
        isDanger: true,
        onConfirm: async () => {
          await fetch(`api.php?action=close_session&session_id=${sId}&delete=1`);
          showToast(`Sesión ${sId} eliminada`);
          loadSessions();
        }
      });
    }

    function connectSession(id) {
      showToast(`Conectando con equipo ${id}...`);
      window.location.href = `rustdesk://${id}`;
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.innerText = text || '';
      return div.innerHTML;
    }

    function logoutUser() {
      showConfirmDialog({
        title: 'Cerrar Sesión',
        message: '¿Estás seguro de que deseas salir del panel de control de Optima IT?',
        iconHtml: '🚪',
        confirmText: 'Cerrar Sesión',
        isDanger: false,
        onConfirm: async () => {
          try {
            await fetch('api.php?action=logout');
          } catch (e) {}
          window.location.href = 'login.php';
        }
      });
    }

    function openUsersModal() {
      const modal = document.getElementById('usersModal');
      if (modal) {
        modal.classList.add('active');
        loadUsersList();
      }
    }

    function closeUsersModal() {
      const modal = document.getElementById('usersModal');
      if (modal) {
        modal.classList.remove('active');
      }
    }

    function handleUsersModalBackdropClick(e) {
      if (e.target.id === 'usersModal') {
        closeUsersModal();
      }
    }

    async function loadUsersList() {
      const tbody = document.getElementById('usersTableBody');
      if (!tbody) return;
      try {
        const res = await fetch('api.php?action=list_users');
        const data = await res.json();
        if (data.success && data.users) {
          if (data.users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#64748B;">No hay usuarios registrados</td></tr>';
            return;
          }
          tbody.innerHTML = data.users.map(u => {
            const isAdminUser = u.role === 'admin';
            const roleBadge = isAdminUser 
              ? '<span style="font-size:11px; font-weight:700; color:#FBBF24; background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3); padding:2px 7px; border-radius:5px;">Admin</span>' 
              : '<span style="font-size:11px; font-weight:600; color:#93C5FD; background:rgba(56,116,204,0.15); border:1px solid rgba(56,116,204,0.3); padding:2px 7px; border-radius:5px;">Técnico</span>';
            
            const isSelf = u.username.toLowerCase() === '<?= strtolower($currentUser['username'] ?? '') ?>';
            const deleteBtn = isSelf 
              ? '<span style="font-size:11px; color:#64748B; font-weight:600;">(Tú)</span>' 
              : `<button type="button" class="btn-act-delete" onclick="deleteUserAccount('${escapeHtml(u.username)}')" title="Eliminar usuario">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                 </button>`;

            return `
              <tr>
                <td style="padding:10px 14px; font-weight:700; color:#FFFFFF;">${escapeHtml(u.username)}</td>
                <td style="padding:10px 14px; color:#CBD5E1;">${escapeHtml(u.name)}</td>
                <td style="padding:10px 14px;">${roleBadge}</td>
                <td style="padding:10px 14px; text-align:right;">${deleteBtn}</td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:15px; color:#F87171;">${data.error || 'Error al cargar'}</td></tr>`;
        }
      } catch (err) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#F87171;">Error de conexión</td></tr>';
      }
    }

    async function handleCreateUser(e) {
      e.preventDefault();
      const u = document.getElementById('newUserUsername').value.trim();
      const n = document.getElementById('newUserName').value.trim();
      const p = document.getElementById('newUserPassword').value;
      const r = document.getElementById('newUserRole').value;
      const btn = document.getElementById('btnSubmitUser');

      btn.disabled = true;
      btn.innerText = "Creando...";

      try {
        const formData = new FormData();
        formData.append('action', 'create_user');
        formData.append('new_username', u);
        formData.append('new_name', n);
        formData.append('new_password', p);
        formData.append('new_role', r);

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
          showToast(`¡Usuario ${u} creado con éxito!`);
          document.getElementById('newUserUsername').value = '';
          document.getElementById('newUserName').value = '';
          document.getElementById('newUserPassword').value = '';
          loadUsersList();
        } else {
          showToast(`⚠️ ${data.error || 'Error al crear usuario'}`);
        }
      } catch (err) {
        showToast("Error de conexión");
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<span>+ Guardar Usuario</span>`;
      }
    }

    function deleteUserAccount(username) {
      showConfirmDialog({
        title: 'Eliminar Usuario',
        message: `¿Estás seguro de eliminar al usuario <strong>'${escapeHtml(username)}'</strong>?<br><span style="font-size:12px; color:#94A3B8;">Esta cuenta perderá de inmediato el acceso al panel técnico.</span>`,
        iconHtml: '🗑️',
        confirmText: 'Sí, Eliminar',
        isDanger: true,
        onConfirm: async () => {
          try {
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('target_user', username);

            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
              showToast(`Usuario ${username} eliminado`);
              loadUsersList();
            } else {
              showToast(`⚠️ ${data.error || 'Error al eliminar usuario'}`);
            }
          } catch (err) {
            showToast("Error al eliminar");
          }
        }
      });
    }

    // Confirmation Dialog Controller
    let confirmCallback = null;

    function showConfirmDialog({ title, message, iconHtml, confirmText, isDanger, onConfirm }) {
      const modal = document.getElementById('confirmActionModal');
      const card = modal.querySelector('.modal-card');
      const iconBadge = document.getElementById('confirmModalIcon');
      document.getElementById('confirmModalTitle').innerText = title || 'Confirmar Acción';
      document.getElementById('confirmModalMessage').innerHTML = message || '';
      if (iconHtml) {
        iconBadge.innerHTML = iconHtml;
      }
      const btn = document.getElementById('btnConfirmAction');
      btn.innerText = confirmText || 'Confirmar';
      if (isDanger) {
        btn.className = 'confirm-btn confirm-btn-danger';
        if (card) {
          card.style.borderColor = 'rgba(239, 68, 68, 0.35)';
          card.style.boxShadow = '0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(239, 68, 68, 0.2)';
        }
        if (iconBadge) {
          iconBadge.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.1) 100%)';
          iconBadge.style.borderColor = 'rgba(239, 68, 68, 0.4)';
        }
      } else {
        btn.className = 'confirm-btn confirm-btn-warning';
        if (card) {
          card.style.borderColor = 'rgba(231, 114, 34, 0.35)';
          card.style.boxShadow = '0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(231, 114, 34, 0.2)';
        }
        if (iconBadge) {
          iconBadge.style.background = 'linear-gradient(135deg, rgba(231, 114, 34, 0.2) 0%, rgba(208, 99, 25, 0.1) 100%)';
          iconBadge.style.borderColor = 'rgba(231, 114, 34, 0.4)';
        }
      }
      confirmCallback = onConfirm;
      modal.classList.add('active');
    }

    function closeConfirmDialog() {
      const modal = document.getElementById('confirmActionModal');
      modal.classList.remove('active');
      confirmCallback = null;
    }

    function handleConfirmModalBackdropClick(e) {
      if (e.target.id === 'confirmActionModal') {
        closeConfirmDialog();
      }
    }

    function executeConfirmedAction() {
      if (confirmCallback) {
        const cb = confirmCallback;
        closeConfirmDialog();
        cb();
      } else {
        closeConfirmDialog();
      }
    }

    // Initialize with Empresa view as requested
    setCategoryTab('empresa');
    setInterval(loadSessions, 3000);
    loadSessions();
  </script>
</body>
</html>
