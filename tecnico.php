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
$relayHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '155.133.197.176';
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
      --bg-dark: #070B14;
      --bg-sidebar: #0B111E;
      --surface-card: rgba(16, 24, 40, 0.85);
      --surface-elevated: rgba(22, 33, 56, 0.7);
      --border: rgba(255, 255, 255, 0.08);
      --border-hover: rgba(255, 255, 255, 0.16);
      --text: #F8FAFC;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
      --green: #10B981;
      --green-glow: rgba(16, 185, 129, 0.35);
      --yellow: #F59E0B;
      --red: #EF4444;
      --sidebar-width: 280px;
    }

    * { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.18) rgba(7, 11, 20, 0.5);
    }

    body {
      background: radial-gradient(circle at 20% 0%, rgba(56, 116, 204, 0.15) 0%, transparent 50%),
                  radial-gradient(circle at 85% 100%, rgba(231, 114, 34, 0.12) 0%, transparent 50%),
                  var(--bg-dark);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    /* Layout Shell */
    .app-shell {
      display: flex;
      width: 100%;
      min-height: 100vh;
    }

    /* Left Sidebar */
    .app-sidebar {
      width: var(--sidebar-width);
      background: var(--bg-sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      flex-shrink: 0;
      z-index: 100;
      backdrop-filter: blur(20px);
    }

    .sidebar-header {
      padding: 24px 20px 20px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--border);
    }
    .brand-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .brand-wrap img {
      height: 38px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));
    }
    .brand-badge {
      font-size: 10px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 6px;
      background: rgba(56, 116, 204, 0.18);
      border: 1px solid rgba(56, 116, 204, 0.35);
      color: #93C5FD;
      letter-spacing: 0.04em;
    }

    /* Sidebar Content */
    .sidebar-body {
      flex: 1;
      padding: 20px 14px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .btn-create-session {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      border: none;
      padding: 13px 18px;
      border-radius: 12px;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      box-shadow: 0 4px 18px -2px var(--orange-glow);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      width: 100%;
    }
    .btn-create-session:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px -2px rgba(231, 114, 34, 0.6);
      filter: brightness(1.08);
    }

    .nav-group-title {
      font-size: 10.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-dim);
      padding: 0 10px 8px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.2s ease;
      user-select: none;
      border: 1px solid transparent;
    }
    .nav-item:hover {
      background: rgba(255, 255, 255, 0.04);
      color: #FFFFFF;
      transform: translateX(2px);
    }
    .nav-item.active {
      background: rgba(56, 116, 204, 0.15);
      border-color: rgba(56, 116, 204, 0.35);
      color: #FFFFFF;
      font-weight: 700;
      box-shadow: 0 2px 10px rgba(56, 116, 204, 0.15);
    }
    .nav-item.active.empresa {
      background: rgba(231, 114, 34, 0.15);
      border-color: rgba(231, 114, 34, 0.35);
      color: #FFFFFF;
      box-shadow: 0 2px 10px rgba(231, 114, 34, 0.15);
    }
    .nav-label-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .nav-count {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.06);
      color: var(--text-muted);
    }
    .nav-item.active .nav-count {
      background: #FFFFFF;
      color: #0F172A;
    }

    /* Relay Server Status Card */
    .server-status-card {
      background: rgba(16, 185, 129, 0.06);
      border: 1px solid rgba(16, 185, 129, 0.22);
      border-radius: 12px;
      padding: 14px;
      margin-top: auto;
    }
    .server-status-header {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 700;
      color: #34D399;
      margin-bottom: 6px;
    }
    .radar-pulse {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--green);
      box-shadow: 0 0 10px var(--green);
      animation: radarAnimation 2s infinite;
    }
    @keyframes radarAnimation {
      0% { transform: scale(0.9); opacity: 0.7; }
      50% { transform: scale(1.3); opacity: 1; }
      100% { transform: scale(0.9); opacity: 0.7; }
    }
    .server-status-host {
      font-family: 'JetBrains Mono', monospace;
      font-size: 11.5px;
      color: #A7F3D0;
      word-break: break-all;
    }

    /* Sidebar Footer / User Profile */
    .sidebar-footer {
      padding: 16px;
      border-top: 1px solid var(--border);
      background: rgba(9, 13, 22, 0.4);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    .user-chip-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      overflow: hidden;
    }
    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      font-weight: 800;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 2px 8px var(--orange-glow);
    }
    .user-meta {
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .user-meta-name {
      font-size: 13px;
      font-weight: 700;
      color: #FFFFFF;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .user-meta-role {
      font-size: 10.5px;
      font-weight: 700;
    }
    .role-admin { color: #FBBF24; }
    .role-tech { color: #60A5FA; }

    .sidebar-user-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-icon-soft {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .btn-icon-soft:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #FFFFFF;
      transform: translateY(-1px);
    }
    .btn-icon-soft.danger:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: rgba(239, 68, 68, 0.4);
      color: #F87171;
    }

    /* Main App Content Area */
    .app-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      padding: 24px 32px;
      max-width: 1600px;
    }

    /* Top Workspace Header Bar */
    .main-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .page-heading h1 {
      font-size: 22px;
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.02em;
    }
    .page-heading p {
      font-size: 13px;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .search-input-box {
      position: relative;
      width: 300px;
    }
    .search-input-box svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-dim);
      pointer-events: none;
    }
    .search-field {
      width: 100%;
      background: rgba(16, 24, 40, 0.7);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 10px 14px 10px 40px;
      color: #FFFFFF;
      font-size: 13.5px;
      outline: none;
      transition: all 0.2s;
    }
    .search-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px var(--primary-glow);
      background: rgba(16, 24, 40, 0.95);
    }

    /* KPI Summary Stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }
    .stat-card {
      background: var(--surface-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 18px 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.4);
    }
    .stat-card:hover {
      border-color: var(--border-hover);
      transform: translateY(-2px);
      box-shadow: 0 14px 30px -8px rgba(0, 0, 0, 0.6);
    }
    .stat-meta p {
      font-size: 11.5px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 5px;
    }
    .stat-meta h3 {
      font-size: 28px;
      font-weight: 800;
      color: #FFFFFF;
      line-height: 1;
    }
    .stat-icon-wrap {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* Workspace Panel */
    .workspace-panel {
      background: var(--surface-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 24px;
      box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Toolbar Filter Row */
    .toolbar-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    .category-pills {
      display: inline-flex;
      background: rgba(7, 11, 20, 0.6);
      padding: 4px;
      border-radius: 12px;
      border: 1px solid var(--border);
      gap: 4px;
    }
    .main-tab {
      padding: 8px 16px;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 700;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.2s;
      user-select: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .main-tab:hover { color: #FFFFFF; }
    .main-tab.active {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 12px var(--orange-glow);
    }
    .main-tab.active[data-cat="personal"] {
      background: linear-gradient(135deg, var(--primary) 0%, #2563EB 100%);
      box-shadow: 0 4px 12px var(--primary-glow);
    }
    .main-tab.active[data-cat="all"] {
      background: linear-gradient(135deg, #475569 0%, #334155 100%);
      box-shadow: 0 4px 12px rgba(71, 85, 105, 0.4);
    }

    .filter-tools {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .group-filter-box {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(7, 11, 20, 0.6);
      border: 1px solid var(--border);
      border-radius: 11px;
      padding: 3px 8px 3px 12px;
    }
    .group-filter-select {
      background: transparent;
      border: none;
      color: #FB923C;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      outline: none;
      padding: 5px 8px;
    }
    .group-filter-select option {
      background: #0B111E;
      color: #F8FAFC;
    }
    .btn-add-group-mini {
      background: rgba(231, 114, 34, 0.15);
      border: 1px solid rgba(231, 114, 34, 0.35);
      color: #FB923C;
      border-radius: 6px;
      padding: 3px 8px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-add-group-mini:hover {
      background: var(--orange);
      color: #FFFFFF;
    }

    .status-pills {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(7, 11, 20, 0.6);
      border: 1px solid var(--border);
      border-radius: 11px;
      padding: 3px;
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
    .s-pill:hover { color: #FFFFFF; }
    .s-pill.active {
      background: rgba(56, 116, 204, 0.2);
      color: #93C5FD;
      font-weight: 700;
      border: 1px solid rgba(56, 116, 204, 0.4);
    }
    .status-dot-pulse {
      display: inline-block;
      width: 7px;
      height: 7px;
      background: #10B981;
      border-radius: 50%;
      box-shadow: 0 0 8px #10B981;
    }

    .view-switcher {
      display: inline-flex;
      background: rgba(7, 11, 20, 0.6);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 3px;
    }
    .view-btn {
      padding: 6px 10px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s;
      user-select: none;
    }
    .view-btn:hover { color: #FFFFFF; }
    .view-btn.active {
      background: rgba(255, 255, 255, 0.1);
      color: #FFFFFF;
      font-weight: 700;
    }

    /* Container Queries Setup */
    .table-container {
      container-type: inline-size;
      container-name: tableContainer;
      overflow-x: auto;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: rgba(9, 13, 22, 0.4);
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }
    .table-container::-webkit-scrollbar { height: 6px; }
    .table-container::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); border-radius: 4px; }
    .table-container::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 4px; }
    .table-container::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.22); }

    table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
    th {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A3B8;
      padding: 14px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(15, 23, 42, 0.75);
      white-space: nowrap;
      position: sticky;
      top: 0;
      backdrop-filter: blur(10px);
      z-index: 2;
    }
    td {
      padding: 13px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      font-size: 13.5px;
      vertical-align: middle;
      transition: background 0.15s ease;
      white-space: nowrap;
    }
    tr:hover td { background: rgba(56, 116, 204, 0.045); }

    .code-badge {
      display: inline-flex;
      align-items: center;
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 12px;
      background: rgba(56, 116, 204, 0.12);
      border: 1px solid rgba(56, 116, 204, 0.3);
      color: #60A5FA;
      padding: 4px 9px;
      border-radius: 7px;
      letter-spacing: 0.03em;
      cursor: pointer;
      transition: all 0.2s;
    }
    .code-badge:hover {
      background: rgba(56, 116, 204, 0.25);
      border-color: rgba(56, 116, 204, 0.6);
      color: #93C5FD;
      transform: scale(1.04);
    }

    .client-cell {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .client-name-title {
      font-weight: 700;
      color: #FFFFFF;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
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
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .badge-online {
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.35);
      color: #34D399;
      box-shadow: 0 0 12px rgba(16, 185, 129, 0.18);
    }
    .badge-waiting {
      background: rgba(245, 158, 11, 0.12);
      border: 1px solid rgba(245, 158, 11, 0.35);
      color: #FBBF24;
    }
    .badge-closed {
      background: rgba(148, 163, 184, 0.08);
      border: 1px solid rgba(148, 163, 184, 0.18);
      color: #94A3B8;
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
    }

    /* Actions buttons */
    .actions-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 6px;
    }
    .btn-act {
      height: 32px;
      padding: 0 12px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s ease;
      line-height: 1;
      text-decoration: none;
      user-select: none;
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
    }
    .btn-act-delete {
      width: 32px;
      height: 32px;
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

    /* Grid Cards View */
    .grid-container {
      display: none;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 16px;
    }
    .device-card {
      background: rgba(11, 17, 30, 0.7);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .device-card:hover {
      border-color: var(--border-hover);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    }
    .card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
    }
    .card-title-group {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .card-device-name {
      font-size: 15px;
      font-weight: 700;
      color: #FFFFFF;
    }
    .card-details {
      display: flex;
      flex-direction: column;
      gap: 8px;
      background: rgba(7, 11, 20, 0.5);
      border-radius: 10px;
      padding: 12px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .card-detail-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 12px;
    }
    .card-detail-label { color: var(--text-dim); }
    .card-footer-actions {
      display: grid;
      grid-template-columns: 1fr auto auto;
      gap: 8px;
      margin-top: auto;
    }

    /* Container Queries */
    @container tableContainer (max-width: 780px) {
      th, td { padding: 10px 12px; font-size: 12px; }
      .actions-wrap { gap: 4px; }
      .btn-act { height: 28px; padding: 0 8px; font-size: 11px; }
      .id-pill { font-size: 11.5px; padding: 2.5px 6px; }
    }
    @container tableContainer (min-width: 1150px) {
      th { padding: 16px 20px; font-size: 12px; }
      td { padding: 15px 20px; font-size: 13.5px; }
      .btn-act { height: 34px; padding: 0 14px; font-size: 12.5px; }
    }

    /* HTML5 <dialog> Styling */
    dialog.app-dialog {
      position: fixed;
      inset: 0;
      margin: auto;
      background: transparent;
      border: none;
      padding: 20px;
      outline: none;
      max-width: 100vw;
      max-height: 100vh;
      overflow: visible;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    dialog.app-dialog:not([open]) { display: none; }
    dialog.app-dialog::backdrop {
      background: rgba(3, 7, 18, 0.78);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    .modal-card {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.96) 0%, rgba(10, 15, 29, 0.98) 100%);
      border: 1px solid rgba(231, 114, 34, 0.35);
      border-radius: 20px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(231, 114, 34, 0.25);
      overflow: hidden;
      animation: dialogPop 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes dialogPop {
      from { opacity: 0; transform: scale(0.92) translateY(12px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(231, 114, 34, 0.05);
    }
    .modal-title-wrap { display: flex; align-items: center; gap: 12px; }
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
    .modal-title { font-size: 16px; font-weight: 700; color: #FFFFFF; }
    .modal-subtitle { font-size: 12px; color: #94A3B8; margin-top: 2px; }
    .modal-close-btn {
      background: transparent;
      border: none;
      color: #94A3B8;
      font-size: 24px;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    .modal-close-btn:hover { background: rgba(255, 255, 255, 0.08); color: #FFFFFF; }
    .modal-body { padding: 24px; }
    .modal-label { display: block; font-size: 13px; font-weight: 600; color: #CBD5E1; margin-bottom: 8px; }
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
    .modal-hint { font-size: 12px; color: #64748B; margin-top: 10px; line-height: 1.4; }
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
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #CBD5E1;
      transition: all 0.2s;
    }
    .modal-btn-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #FFFFFF; }
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
    .btn-save-group:hover { transform: translateY(-1px); filter: brightness(1.08); }

    /* Generator Inside Modal / Offcanvas */
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
    .link-share-card {
      background: rgba(231, 114, 34, 0.08);
      border: 1px dashed rgba(231, 114, 34, 0.4);
      border-radius: 14px;
      padding: 16px;
      margin-top: 18px;
      display: none;
    }
    .share-input-row {
      display: flex;
      gap: 8px;
      margin: 10px 0;
    }
    .share-input-row input {
      flex: 1;
      background: rgba(0,0,0,0.5);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 9px 12px;
      color: #FFFFFF;
      font-size: 12.5px;
      font-family: 'JetBrains Mono', monospace;
      outline: none;
    }

    /* Native Toast Popover */
    .toast {
      position: fixed;
      inset: auto 24px 24px auto;
      margin: 0;
      background: rgba(15, 23, 42, 0.95);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      color: #FFFFFF;
      border: 1px solid var(--primary);
      padding: 13px 22px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 15px rgba(56, 116, 204, 0.25);
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: none;
      z-index: 10000;
    }
    .toast:popover-open,
    .toast.show {
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    /* Confirmation Modal Footer */
    .confirm-modal-footer {
      padding: 16px 24px 20px 24px;
      background: rgba(0, 0, 0, 0.35);
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      width: 100%;
    }
    .confirm-btn {
      height: 44px;
      padding: 0 16px;
      border-radius: 12px;
      font-size: 13.5px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: none;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .confirm-btn-cancel {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.14);
      color: #E2E8F0;
    }
    .confirm-btn-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #FFFFFF; }
    .confirm-btn-danger {
      background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px rgba(239, 68, 68, 0.45);
    }
    .confirm-btn-danger:hover { filter: brightness(1.12); transform: translateY(-1px); }
    .confirm-btn-warning {
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px var(--orange-glow);
    }
    .confirm-btn-warning:hover { filter: brightness(1.12); transform: translateY(-1px); }

    /* Responsive */
    @media (max-width: 1024px) {
      .app-sidebar {
        width: 72px;
      }
      .brand-wrap span, .nav-item span, .nav-count, .nav-group-title, .server-status-card, .user-meta {
        display: none !important;
      }
      .sidebar-header, .sidebar-body, .sidebar-footer {
        padding: 14px 10px;
        align-items: center;
      }
      .btn-create-session span { display: none; }
      .btn-create-session { padding: 12px; width: 44px; height: 44px; border-radius: 10px; }
      .nav-item { justify-content: center; padding: 12px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 680px) {
      .app-shell { flex-direction: column; }
      .app-sidebar { width: 100%; height: auto; position: static; flex-direction: row; border-right: none; border-bottom: 1px solid var(--border); }
      .sidebar-body { display: none; }
      .stats-grid { grid-template-columns: 1fr; }
      .app-main { padding: 16px; }
      .main-topbar { flex-direction: column; align-items: stretch; }
      .search-input-box { width: 100%; }
    }
  </style>
</head>
<body>

  <div class="app-shell">
    
    <!-- LEFT SIDEBAR -->
    <aside class="app-sidebar">
      <div class="sidebar-header">
        <div class="brand-wrap">
          <img src="flutter/assets/logo_dark.png?v=2" alt="OptimaIT">
        </div>
        <span class="brand-badge">SaaS v2.4</span>
      </div>

      <div class="sidebar-body">
        <button type="button" class="btn-create-session" onclick="openSessionModal()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
          <span>Nueva Sesión</span>
        </button>

        <div>
          <div class="nav-group-title">
            <span>Vistas de Equipos</span>
          </div>
          <ul class="nav-list">
            <li class="nav-item active empresa" id="navItemEmpresas" onclick="setCategoryTab('empresa')">
              <div class="nav-label-wrap">
                <span>🏢</span>
                <span>Empresas</span>
              </div>
              <span class="nav-count" id="countTabEmpresa">0</span>
            </li>
            <li class="nav-item" id="navItemPersonal" onclick="setCategoryTab('personal')">
              <div class="nav-label-wrap">
                <span>👤</span>
                <span>Personas Naturales</span>
              </div>
              <span class="nav-count" id="countTabPersonal">0</span>
            </li>
            <li class="nav-item" id="navItemAll" onclick="setCategoryTab('all')">
              <div class="nav-label-wrap">
                <span>🌐</span>
                <span>Todos los Equipos</span>
              </div>
              <span class="nav-count" id="countTabAll">0</span>
            </li>
          </ul>
        </div>

        <div>
          <div class="nav-group-title">
            <span>Sedes / Grupos</span>
            <button type="button" class="btn-add-group-mini" onclick="promptNewGroup()" title="Crear nueva sede">+</button>
          </div>
          <ul class="nav-list" id="sidebarGroupsList">
            <!-- Populated dynamically via updateGroupControls() -->
          </ul>
        </div>

        <!-- Relay Server Health Card -->
        <div class="server-status-card">
          <div class="server-status-header">
            <div class="radar-pulse"></div>
            <span>Relay Server Activo</span>
          </div>
          <div class="server-status-host"><?= htmlspecialchars($relayHost) ?></div>
        </div>
      </div>

      <!-- User Profile Card -->
      <div class="sidebar-footer">
        <div class="user-chip-wrap">
          <div class="user-avatar">
            <?= strtoupper(substr($currentUser['name'] ?? $currentUser['username'], 0, 1)) ?>
          </div>
          <div class="user-meta">
            <span class="user-meta-name"><?= htmlspecialchars($currentUser['name'] ?? $currentUser['username']) ?></span>
            <span class="user-meta-role <?= $isAdmin ? 'role-admin' : 'role-tech' ?>">
              <?= $isAdmin ? '👑 Administrador' : '🛠️ Técnico' ?>
            </span>
          </div>
        </div>

        <div class="sidebar-user-actions">
          <?php if ($isAdmin): ?>
          <button type="button" class="btn-icon-soft" onclick="openUsersModal()" title="Gestión de Técnicos">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </button>
          <?php endif; ?>
          <button type="button" class="btn-icon-soft danger" onclick="logoutUser()" title="Cerrar Sesión">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- MAIN WORKSPACE -->
    <main class="app-main">
      
      <!-- Topbar -->
      <header class="main-topbar">
        <div class="page-heading">
          <h1>Centro de Asistencia Remota</h1>
          <p>Supervisión en tiempo real y conexión desatendida corporativa</p>
        </div>

        <div class="topbar-actions">
          <div class="search-input-box">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" class="search-field" placeholder="Buscar equipo, código, ID..." oninput="handleSearchInput(event)">
          </div>

          <button type="button" class="btn-create-session" style="padding:10px 16px; font-size:13px; width:auto;" onclick="openSessionModal()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            <span>Generar Enlace</span>
          </button>
        </div>
      </header>

      <!-- KPI Summary Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-meta">
            <p>Clientes En Línea</p>
            <h3 id="statOnline">0</h3>
          </div>
          <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.828a5 5 0 010-7.072m7.072 0a5 5 0 010 7.072M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-meta">
            <p>Equipos Empresa</p>
            <h3 id="statEmpresas">0</h3>
          </div>
          <div class="stat-icon-wrap" style="background: rgba(231, 114, 34, 0.15); color: #E77222;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-meta">
            <p>Personas Naturales</p>
            <h3 id="statPersonal">0</h3>
          </div>
          <div class="stat-icon-wrap" style="background: rgba(56, 116, 204, 0.15); color: #3874CC;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-meta">
            <p>Total Registrados</p>
            <h3 id="statTotal">0</h3>
          </div>
          <div class="stat-icon-wrap" style="background: rgba(148, 163, 184, 0.15); color: #94A3B8;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
        </div>
      </div>

      <!-- Main Workspace Panel -->
      <section class="workspace-panel">
        
        <!-- Filter Toolbar -->
        <div class="toolbar-row">
          <div class="category-pills">
            <div class="main-tab active" data-cat="empresa" onclick="setCategoryTab('empresa')">
              🏢 Empresas
            </div>
            <div class="main-tab" data-cat="personal" onclick="setCategoryTab('personal')">
              👤 Personas Naturales
            </div>
            <div class="main-tab" data-cat="all" onclick="setCategoryTab('all')">
              🌐 Todos los Equipos
            </div>
          </div>

          <div class="filter-tools">
            <div class="group-filter-box" id="groupFilterBox">
              <select id="groupFilterSelect" class="group-filter-select" onchange="selectGroupDropdown(this.value)">
                <option value="all">🏢 Todas las Sedes</option>
              </select>
              <button type="button" class="btn-add-group-mini" onclick="promptNewGroup()" title="Agregar nuevo grupo">+</button>
            </div>

            <div class="status-pills">
              <div class="s-pill active" data-status="all" onclick="setStatusFilter('all')">Todos</div>
              <div class="s-pill" data-status="online" onclick="setStatusFilter('online')"><span class="status-dot-pulse"></span> En Línea</div>
              <div class="s-pill" data-status="inactive" onclick="setStatusFilter('inactive')">Inactivos</div>
            </div>

            <div class="view-switcher">
              <div class="view-btn active" id="btnViewTable" onclick="switchView('table')" title="Vista Tabla">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <span>Tabla</span>
              </div>
              <div class="view-btn" id="btnViewGrid" onclick="switchView('grid')" title="Vista Tarjetas">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Tarjetas</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div class="table-container" id="tableViewContainer">
          <table>
            <thead>
              <tr id="tableHeaderRow">
                <th style="width:115px;">Código</th>
                <th>Cliente / Equipo</th>
                <th id="thColGrupo" style="width:160px;">Sede / Grupo</th>
                <th style="width:125px;">Estado</th>
                <th style="width:145px;">ID Remoto</th>
                <th style="text-align:right; width:250px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="sessionsBody">
              <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--text-dim);">Cargando registros de equipos...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Grid View (Cards) -->
        <div class="grid-container" id="gridViewContainer">
          <!-- Populated dynamically via renderSessions() -->
        </div>

      </section>

    </main>

  </div>

  <!-- TOAST NOTIFICATION (POPOVER) -->
  <div class="toast" id="toastBox" popover="manual">
    <span id="toastMsg">Acción completada</span>
  </div>

  <!-- MODAL: GENERAR NUEVA SESIÓN DE SOPORTE -->
  <dialog id="newSessionModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
    <div class="modal-card" style="max-width: 520px;">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-icon-badge">⚡</div>
          <div>
            <h3 class="modal-title">Generar Enlace de Asistencia</h3>
            <p class="modal-subtitle">Crea una sesión de soporte directo o instalación permanente</p>
          </div>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeSessionModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body">
        <form id="createForm" onsubmit="handleCreateSession(event)">
          <label class="modal-label">Modalidad de Atención:</label>
          <div class="mode-switcher">
            <div class="mode-btn empresa active" id="btnModeEmpresa" onclick="setCreationMode('empresa')">
              🏢 Empresa (Permanente)
            </div>
            <div class="mode-btn personal" id="btnModePersonal" onclick="setCreationMode('personal')">
              👤 Persona Natural (Temporal)
            </div>
          </div>
          <input type="hidden" id="clientTypeInput" value="empresa">

          <div class="info-notice orange" id="noticeText">
            <span>🏢 <strong>Modo Empresa:</strong> Genera instalador desatendido que iniciará automáticamente con Windows.</span>
          </div>

          <div style="margin-bottom: 16px;">
            <label class="modal-label" id="lblClientName">Nombre de la Empresa o Equipo:</label>
            <input type="text" id="clientNameInput" class="modal-input" placeholder="Ej: Sucursal Central / Caja 01" required>
          </div>

          <div style="margin-bottom: 20px;" id="empresaGroupContainer">
            <label class="modal-label" for="groupSelect">Asignar a Grupo Corporativo / Sede:</label>
            <div style="display:flex; gap:8px;">
              <select id="groupSelect" class="modal-input" style="cursor:pointer;"></select>
              <button type="button" class="btn-save-group" style="padding:0 14px; font-size:16px;" onclick="promptNewGroup()" title="Crear nueva sede">+</button>
            </div>
          </div>

          <button type="submit" class="btn-create-session" id="btnSubmit" style="height:46px; font-size:14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <span id="btnSubmitText">Generar Enlace para Empresa</span>
          </button>
        </form>

        <div class="link-share-card" id="linkBox">
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:11px; font-weight:700; color:var(--orange);" id="shareModeTitle">ENLACE GENERADO</span>
            <span id="badgeShareType" style="font-size:10px; padding:2px 7px; border-radius:4px; font-weight:700;"></span>
          </div>
          <div class="share-input-row">
            <input type="text" id="generatedUrl" readonly>
            <button type="button" class="btn-save-group" style="padding:0 14px; font-size:12px;" onclick="copyLink()">📋 Copiar</button>
          </div>
          <p style="font-size:11px; color:var(--text-dim); line-height:1.4;" id="shareExplanation"></p>
        </div>
      </div>
    </div>
  </dialog>

  <!-- MODAL: NUEVO GRUPO CORPORATIVO -->
  <dialog id="newGroupModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-title-wrap">
          <div class="modal-icon-badge">🏢</div>
          <div>
            <h3 class="modal-title">Nueva Sede o Grupo Corporativo</h3>
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
        <button type="button" class="modal-btn-cancel" onclick="closeGroupModal()">Cancelar</button>
        <button type="button" class="btn-save-group" id="btnSaveGroup" onclick="submitNewGroup()">
          <span>Guardar Grupo</span>
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
        </button>
      </div>
    </div>
  </dialog>

  <?php if ($isAdmin): ?>
  <!-- MODAL: GESTIÓN DE USUARIOS (ADMIN) -->
  <dialog id="usersModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
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
        <!-- Form Crear Usuario -->
        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:16px;">
          <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 12px;">➕ Crear Nuevo Usuario Técnico</div>
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
              <button type="submit" class="btn-save-group" id="btnSubmitUser" style="padding:8px 18px; font-size:12.5px;">
                <span>+ Guardar Usuario</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Tabla Usuarios -->
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

      <div class="modal-footer">
        <button type="button" class="modal-btn-cancel" onclick="closeUsersModal()">Cerrar</button>
      </div>
    </div>
  </dialog>
  <?php endif; ?>

  <!-- MODAL: CONFIRMACIÓN ELEGANTE -->
  <dialog id="confirmActionModal" class="app-dialog" style="z-index: 10005;" onclick="handleDialogBackdropClick(event)">
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
  </dialog>

  <script>
    let rawSessions = [];
    let customGroups = [];
    let currentCategory = 'empresa'; // 'empresa' | 'personal' | 'all'
    let currentGroupFilter = 'all';
    let currentStatusFilter = 'all';
    let currentViewMode = 'table'; // 'table' | 'grid'
    let currentSearchQuery = '';
    let previousOnlineIds = new Set();
    let isInitialLoad = true;
    let confirmCallback = null;

    function showToast(msg) {
      const toast = document.getElementById('toastBox');
      const toastMsg = document.getElementById('toastMsg');
      if (toastMsg) toastMsg.innerText = msg;
      if (typeof toast.showPopover === 'function') {
        try { toast.showPopover(); } catch(e) {}
      }
      toast.classList.add('show');
      clearTimeout(toast._timeout);
      toast._timeout = setTimeout(() => {
        toast.classList.remove('show');
        if (typeof toast.hidePopover === 'function') {
          try { toast.hidePopover(); } catch(e) {}
        }
      }, 2800);
    }

    function switchView(mode) {
      currentViewMode = mode;
      document.getElementById('btnViewTable').classList.toggle('active', mode === 'table');
      document.getElementById('btnViewGrid').classList.toggle('active', mode === 'grid');
      document.getElementById('tableViewContainer').style.display = mode === 'table' ? 'block' : 'none';
      document.getElementById('gridViewContainer').style.display = mode === 'grid' ? 'grid' : 'none';
      renderSessions();
    }

    function openSessionModal() {
      const modal = document.getElementById('newSessionModal');
      if (modal.showModal) modal.showModal();
      else modal.classList.add('active');
      setTimeout(() => document.getElementById('clientNameInput').focus(), 80);
    }

    function closeSessionModal() {
      const modal = document.getElementById('newSessionModal');
      if (modal.close) modal.close();
      else modal.classList.remove('active');
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
        notice.innerHTML = `<span>🏢 <strong>Modo Empresa:</strong> Genera instalador desatendido que iniciará automáticamente con Windows.</span>`;
        grpCont.style.display = 'block';
        lblName.innerText = "Nombre de la Empresa o Equipo:";
        btnText.innerText = "Generar Enlace para Empresa";
      } else {
        btnE.classList.remove('active');
        btnP.classList.add('active');
        notice.className = 'info-notice';
        notice.innerHTML = `<span>👤 <strong>Persona Natural:</strong> Asistente temporal directo de un solo clic sin requerir instalación.</span>`;
        grpCont.style.display = 'none';
        lblName.innerText = "Nombre del Cliente:";
        btnText.innerText = "Generar Enlace para Persona Natural";
      }
    }

    function handleSearchInput(e) {
      currentSearchQuery = e.target.value.trim();
      renderSessions();
    }

    function setCategoryTab(cat) {
      currentCategory = cat;
      // Sync main tab pills
      document.querySelectorAll('.main-tab').forEach(t => {
        t.classList.toggle('active', t.getAttribute('data-cat') === cat);
      });
      // Sync sidebar nav items
      document.getElementById('navItemEmpresas').classList.toggle('active', cat === 'empresa');
      document.getElementById('navItemPersonal').classList.toggle('active', cat === 'personal');
      document.getElementById('navItemAll').classList.toggle('active', cat === 'all');

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
          
          // Detect newly connected clients for proactive Toast alerts
          const currentOnline = new Set();
          rawSessions.forEach(s => {
            if (s.status === 'online') {
              currentOnline.add(s.session_id);
              if (!isInitialLoad && !previousOnlineIds.has(s.session_id)) {
                showToast(`🟢 ¡Equipo "${s.client_name}" conectado y en línea!`);
              }
            }
          });
          previousOnlineIds = currentOnline;
          isInitialLoad = false;

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
      customGroups.forEach(g => { 
        if (g && g !== 'General' && g !== 'Personas Naturales') companyGroups.add(g);
      });
      rawSessions.forEach(s => {
        if (s.client_type === 'empresa' && s.group_name && s.group_name !== 'Personas Naturales' && s.group_name !== 'General') {
          companyGroups.add(s.group_name);
        }
      });
      const groupArr = Array.from(companyGroups);
      if (groupArr.length === 0) {
        groupArr.push('Oficina Central', 'Sucursales');
      }

      // 1. Generator Group Select
      const sel = document.getElementById('groupSelect');
      if (sel) {
        const curr = sel.value;
        sel.innerHTML = groupArr.map(g => `<option value="${escapeHtml(g)}">🏢 ${escapeHtml(g)}</option>`).join('');
        if (curr && groupArr.includes(curr)) sel.value = curr;
      }

      // 2. Table Filter Select
      const grpFilterSelect = document.getElementById('groupFilterSelect');
      if (grpFilterSelect) {
        let optHtml = `<option value="all">🏢 Todas las Sedes</option>`;
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

      // 3. Sidebar Groups Shortcut List
      const sList = document.getElementById('sidebarGroupsList');
      if (sList) {
        sList.innerHTML = groupArr.slice(0, 5).map(g => {
          const count = rawSessions.filter(s => s.group_name === g).length;
          return `
            <li class="nav-item ${currentGroupFilter === g ? 'active empresa' : ''}" onclick="setCategoryTab('empresa'); selectGroupDropdown('${escapeHtml(g)}');">
              <div class="nav-label-wrap">
                <span style="font-size:12px;">📁</span>
                <span style="font-size:12px;">${escapeHtml(g)}</span>
              </div>
              <span class="nav-count">${count}</span>
            </li>
          `;
        }).join('');
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
      const gridContainer = document.getElementById('gridViewContainer');
      const isPersonalView = (currentCategory === 'personal');

      // 1. Filter by category
      let list = rawSessions;
      if (currentCategory === 'empresa') {
        list = list.filter(s => s.client_type === 'empresa');
      } else if (currentCategory === 'personal') {
        list = list.filter(s => s.client_type !== 'empresa');
      }

      // 2. Filter by group
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
            : `No hay equipos asignados a la sede "${escapeHtml(currentGroupFilter)}".`;
        } else if (currentCategory === 'personal') {
          emptyMsg = "No hay asistencias temporales de Personas Naturales activas.";
        }
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding:45px 20px; color:var(--text-dim); font-size:13.5px;"><div style="font-size:28px; margin-bottom:10px;">📋</div>${emptyMsg}</td></tr>`;
        gridContainer.innerHTML = `<div style="grid-column: 1 / -1; text-align:center; padding:45px 20px; color:var(--text-dim); font-size:13.5px;"><div style="font-size:32px; margin-bottom:10px;">🗂️</div>${emptyMsg}</div>`;
        return;
      }

      // Render Table Rows
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
          <button onclick="copySpecificUrl('${s.session_id}')" class="btn-act btn-act-copy" title="Copiar enlace de descarga para este equipo">
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
            <td><span class="code-badge" onclick="copySpecificUrl('${s.session_id}')" title="Clic para copiar enlace">${s.session_id}</span></td>
            <td>
              <div class="client-cell">
                <span class="client-name-title">🖥️ ${escapeHtml(s.client_name)}</span>
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

      // Render Cards Grid
      gridContainer.innerHTML = list.map(s => {
        const isOnline = s.status === 'online';
        const isEmpresa = s.client_type === 'empresa';
        let statusPill = isOnline 
          ? `<span class="badge-status badge-online">● EN LÍNEA</span>` 
          : `<span class="badge-status badge-closed">Inactivo</span>`;

        let connectBtnGrid = s.rustdesk_id ? `
          <button onclick="connectSession('${s.rustdesk_id}')" class="btn-act ${isOnline ? 'btn-act-connect' : 'btn-act-reconnect'}" style="width:100%; justify-content:center; height:36px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            <span>Conectar</span>
          </button>
        ` : `<div style="font-size:12px; color:var(--text-dim); text-align:center; padding:8px;">⏳ Pendiente de instalación</div>`;

        return `
          <div class="device-card">
            <div class="card-top">
              <div class="card-title-group">
                <span class="card-device-name">🖥️ ${escapeHtml(s.client_name)}</span>
                <span class="code-badge" style="width:fit-content;" onclick="copySpecificUrl('${s.session_id}')">${s.session_id}</span>
              </div>
              ${statusPill}
            </div>

            <div class="card-details">
              <div class="card-detail-row">
                <span class="card-detail-label">Modalidad:</span>
                <span>${isEmpresa ? '🏢 Empresa' : '👤 Temporal'}</span>
              </div>
              ${isEmpresa ? `
              <div class="card-detail-row">
                <span class="card-detail-label">Sede / Grupo:</span>
                <span style="color:#FB923C; font-weight:700;">🏢 ${escapeHtml(s.group_name || 'General')}</span>
              </div>` : ''}
              <div class="card-detail-row">
                <span class="card-detail-label">ID Remoto:</span>
                <span class="id-pill">${s.rustdesk_id || '---'}</span>
              </div>
            </div>

            <div class="card-footer-actions">
              ${connectBtnGrid}
              <button onclick="copySpecificUrl('${s.session_id}')" class="btn-act btn-act-copy" title="Copiar Link" style="height:36px; padding:0 12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
              </button>
              <button onclick="deleteSessionPermanently('${s.session_id}')" class="btn-act-delete" title="Eliminar" style="height:36px; width:36px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>
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
            document.getElementById('shareModeTitle').innerText = 'ENLACE ASISTENCIA EMPRESA';
            document.getElementById('badgeShareType').innerText = 'EMPRESA';
            document.getElementById('badgeShareType').style.background = 'rgba(231,114,34,0.2)';
            document.getElementById('badgeShareType').style.color = '#FB923C';
            document.getElementById('shareExplanation').innerText = 'Descarga con elevación UAC y servicio en segundo plano que iniciará con Windows.';
          } else {
            document.getElementById('shareModeTitle').innerText = 'ENLACE PERSONA NATURAL';
            document.getElementById('badgeShareType').innerText = 'NATURAL';
            document.getElementById('badgeShareType').style.background = 'rgba(56,116,204,0.2)';
            document.getElementById('badgeShareType').style.color = '#93C5FD';
            document.getElementById('shareExplanation').innerText = 'Asistente temporal directo de 1 solo clic sin instalación permanente.';
          }

          document.getElementById('clientNameInput').value = '';
          showToast(`¡Enlace para "${data.session.client_name}" creado con éxito!`);
          loadSessions();
        }
      } catch (err) {
        showToast("Error al generar sesión");
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> <span id="btnSubmitText">${type === 'empresa' ? 'Generar Enlace para Empresa' : 'Generar Enlace para Persona Natural'}</span>`;
      }
    }

    function handleDialogBackdropClick(e) {
      if (e.target && e.target.tagName === 'DIALOG') {
        if (e.target.close) e.target.close();
        else e.target.classList.remove('active');
      }
    }

    function promptNewGroup() {
      const modal = document.getElementById('newGroupModal');
      const input = document.getElementById('newGroupNameInput');
      input.value = '';
      if (modal.showModal) modal.showModal();
      else modal.classList.add('active');
      setTimeout(() => input.focus(), 60);
    }

    function closeGroupModal() {
      const modal = document.getElementById('newGroupModal');
      if (modal.close) modal.close();
      else modal.classList.remove('active');
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
          showToast(`Sede corporativa "${gName}" agregada con éxito`);
          closeGroupModal();
          await loadSessions();
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
      fetch(`api.php?action=mark_online&id=${encodeURIComponent(id)}`).then(() => loadSessions()).catch(() => {});
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
        if (modal.showModal) modal.showModal();
        else modal.classList.add('active');
        loadUsersList();
      }
    }

    function closeUsersModal() {
      const modal = document.getElementById('usersModal');
      if (modal) {
        if (modal.close) modal.close();
        else modal.classList.remove('active');
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
          showToast(`Usuario "${u}" creado con éxito`);
          document.getElementById('createUserForm').reset();
          loadUsersList();
        } else {
          showToast(data.error || "Error al crear usuario");
        }
      } catch (err) {
        showToast("Error al procesar la solicitud");
      } finally {
        btn.disabled = false;
        btn.innerText = "+ Guardar Usuario";
      }
    }

    function deleteUserAccount(username) {
      showConfirmDialog({
        title: 'Eliminar Usuario',
        message: `¿Estás seguro de que deseas eliminar la cuenta de <strong>${escapeHtml(username)}</strong>?<br><span style="font-size:12px; color:#94A3B8;">Este usuario ya no podrá iniciar sesión en el panel técnico.</span>`,
        iconHtml: '👤',
        confirmText: 'Sí, Eliminar',
        isDanger: true,
        onConfirm: async () => {
          try {
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('username', username);
            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
              showToast(`Usuario "${username}" eliminado`);
              loadUsersList();
            } else {
              showToast(data.error || "Error al eliminar");
            }
          } catch (e) {
            showToast("Error de conexión");
          }
        }
      });
    }

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
      if (modal.showModal) modal.showModal();
      else modal.classList.add('active');
    }

    function closeConfirmDialog() {
      const modal = document.getElementById('confirmActionModal');
      if (modal.close) modal.close();
      else modal.classList.remove('active');
      confirmCallback = null;
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

    // Initialize with Empresa view
    setCategoryTab('empresa');
    setInterval(loadSessions, 3000);
    loadSessions();
  </script>
</body>
</html>
