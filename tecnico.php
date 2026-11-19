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
  <title>OptimaIT - Control Center Pro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2563EB;
      --primary-accent: #38BDF8;
      --primary-glow: rgba(37, 99, 235, 0.4);
      --orange: #FF6B00;
      --orange-accent: #FFA133;
      --orange-glow: rgba(255, 107, 0, 0.35);
      --bg-base: #060913;
      --bg-sidebar: #090E1A;
      --card-bg: rgba(13, 20, 36, 0.75);
      --card-hover: rgba(18, 28, 51, 0.85);
      --border-subtle: rgba(255, 255, 255, 0.07);
      --border-focus: rgba(56, 189, 248, 0.4);
      --text-main: #FFFFFF;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
      --emerald: #10B981;
      --emerald-glow: rgba(16, 185, 129, 0.3);
      --amber: #F59E0B;
      --rose: #F43F5E;
      --sidebar-width: 270px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.15) rgba(6, 9, 19, 0.4);
    }

    body {
      background: radial-gradient(circle at 10% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 45%),
                  radial-gradient(circle at 90% 100%, rgba(255, 107, 0, 0.09) 0%, transparent 40%),
                  var(--bg-base);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    .app-shell {
      display: flex;
      width: 100%;
      min-height: 100vh;
    }

    /* LEFT SIDEBAR */
    .app-sidebar {
      width: var(--sidebar-width);
      background: var(--bg-sidebar);
      border-right: 1px solid var(--border-subtle);
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      flex-shrink: 0;
      z-index: 100;
      backdrop-filter: blur(20px);
    }

    .sidebar-brand-box {
      padding: 24px 20px 20px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--border-subtle);
    }
    .brand-logo-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .brand-logo-wrap img {
      height: 36px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 4px 12px rgba(0,0,0,0.6));
    }
    .badge-enterprise {
      font-size: 10px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 999px;
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(56, 189, 248, 0.15) 100%);
      border: 1px solid rgba(56, 189, 248, 0.35);
      color: #38BDF8;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .sidebar-scroll {
      flex: 1;
      padding: 20px 14px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    /* Call To Action Button */
    .btn-create-session {
      background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
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
      transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
      width: 100%;
      letter-spacing: 0.01em;
    }
    .btn-create-session:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px -2px rgba(255, 107, 0, 0.55);
      filter: brightness(1.06);
    }

    .nav-section-title {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-dim);
      padding: 0 8px 8px 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-menu {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .nav-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 12px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      transition: all 0.18s ease;
      user-select: none;
      border: 1px solid transparent;
    }
    .nav-link:hover {
      background: rgba(255, 255, 255, 0.04);
      color: #FFFFFF;
      transform: translateX(3px);
    }
    .nav-link.active {
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.18) 0%, rgba(37, 99, 235, 0.08) 100%);
      border-color: rgba(37, 99, 235, 0.4);
      color: #FFFFFF;
      font-weight: 700;
      box-shadow: 0 2px 10px rgba(37, 99, 235, 0.2);
    }
    .nav-link.active.empresa {
      background: linear-gradient(135deg, rgba(255, 107, 0, 0.16) 0%, rgba(255, 107, 0, 0.06) 100%);
      border-color: rgba(255, 107, 0, 0.35);
      box-shadow: 0 2px 10px rgba(255, 107, 0, 0.15);
    }
    .nav-label {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .nav-pill-counter {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.07);
      color: var(--text-muted);
    }
    .nav-link.active .nav-pill-counter {
      background: #FFFFFF;
      color: #0F172A;
    }

    /* Relay Server Live Indicator Card */
    .relay-card {
      background: rgba(16, 185, 129, 0.06);
      border: 1px solid rgba(16, 185, 129, 0.22);
      border-radius: 12px;
      padding: 12px 14px;
      margin-top: auto;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .relay-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 11.5px;
      font-weight: 700;
      color: #34D399;
    }
    .radar-pulse-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--emerald);
      box-shadow: 0 0 8px var(--emerald);
      animation: pulseRadar 2s infinite;
    }
    @keyframes pulseRadar {
      0% { transform: scale(0.9); opacity: 0.6; }
      50% { transform: scale(1.3); opacity: 1; }
      100% { transform: scale(0.9); opacity: 0.6; }
    }
    .relay-host-text {
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      color: #A7F3D0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Sidebar User Footer */
    .sidebar-user-footer {
      padding: 16px 18px;
      border-top: 1px solid var(--border-subtle);
      background: rgba(6, 9, 19, 0.6);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .user-info-cluster {
      display: flex;
      align-items: center;
      gap: 11px;
      overflow: hidden;
    }
    .user-avatar-badge {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
      color: #FFFFFF;
      font-weight: 800;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 2px 10px var(--orange-glow);
    }
    .user-label-group {
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .user-label-name {
      font-size: 13px;
      font-weight: 700;
      color: #FFFFFF;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .user-label-role {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .role-admin { color: #FBBF24; }
    .role-tech { color: #38BDF8; }

    .user-footer-btns {
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
      transition: all 0.18s ease;
    }
    .btn-icon-soft:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #FFFFFF;
      transform: translateY(-1px);
    }
    .btn-icon-soft.danger:hover {
      background: rgba(244, 63, 94, 0.2);
      border-color: rgba(244, 63, 94, 0.4);
      color: #FB7185;
    }

    /* MAIN APP WORKSPACE */
    .app-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      padding: 24px 36px 40px 36px;
      max-width: 1650px;
    }

    /* Top Navigation Header */
    .main-top-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 26px;
      flex-wrap: wrap;
    }
    .page-title-cluster h1 {
      font-size: 24px;
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.02em;
    }
    .page-title-cluster p {
      font-size: 13px;
      color: var(--text-muted);
      margin-top: 3px;
    }

    .top-toolbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .search-input-wrapper {
      position: relative;
      width: 320px;
    }
    .search-input-wrapper svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-dim);
      pointer-events: none;
    }
    .search-box {
      width: 100%;
      background: rgba(13, 20, 36, 0.85);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 11px 14px 11px 42px;
      color: #FFFFFF;
      font-size: 13.5px;
      outline: none;
      transition: all 0.2s;
    }
    .search-box:focus {
      border-color: #38BDF8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
      background: rgba(13, 20, 36, 0.98);
    }

    /* KPI METRIC CARDS */
    .metrics-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }
    .metric-card {
      background: var(--card-bg);
      border: 1px solid var(--border-subtle);
      border-radius: 18px;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      backdrop-filter: blur(16px);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.5);
      position: relative;
      overflow: hidden;
    }
    .metric-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: transparent;
      transition: background 0.2s;
    }
    .metric-card:hover {
      border-color: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
      box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.65);
    }
    .metric-card.card-online:hover::before { background: var(--emerald); }
    .metric-card.card-empresa:hover::before { background: var(--orange); }
    .metric-card.card-personal:hover::before { background: var(--primary); }
    .metric-card.card-total:hover::before { background: #38BDF8; }

    .metric-meta p {
      font-size: 11.5px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 6px;
    }
    .metric-meta h3 {
      font-size: 30px;
      font-weight: 800;
      color: #FFFFFF;
      line-height: 1;
      letter-spacing: -0.02em;
    }
    .metric-icon-box {
      width: 50px;
      height: 50px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* WORKSPACE DATA PANEL */
    .content-panel {
      background: var(--card-bg);
      border: 1px solid var(--border-subtle);
      border-radius: 20px;
      padding: 24px 28px;
      backdrop-filter: blur(20px);
      box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.6);
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Sub-header Filter Toolbar */
    .filter-header-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .filter-title-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .active-view-heading {
      font-size: 16px;
      font-weight: 800;
      color: #FFFFFF;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .active-badge-tag {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.07);
      color: #94A3B8;
    }

    .filter-controls-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    /* Modern Styled Sede Select */
    .group-dropdown-wrap {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(9, 14, 26, 0.7);
      border: 1px solid var(--border-subtle);
      border-radius: 11px;
      padding: 3px 6px 3px 12px;
    }
    .group-dropdown-select {
      background: transparent;
      border: none;
      color: #FDBA74;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      outline: none;
      padding: 6px 8px;
    }
    .group-dropdown-select option {
      background: #0B111E;
      color: #F8FAFC;
    }
    .btn-add-group-badge {
      background: rgba(255, 107, 0, 0.15);
      border: 1px solid rgba(255, 107, 0, 0.35);
      color: #FB923C;
      border-radius: 7px;
      padding: 4px 9px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-add-group-badge:hover {
      background: var(--orange);
      color: #FFFFFF;
    }

    /* Status Filters */
    .status-filter-group {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(9, 14, 26, 0.7);
      border: 1px solid var(--border-subtle);
      border-radius: 11px;
      padding: 3px;
    }
    .status-tab-btn {
      padding: 6px 14px;
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
      border: 1px solid transparent;
    }
    .status-tab-btn:hover { color: #FFFFFF; }
    .status-tab-btn.active {
      background: rgba(37, 99, 235, 0.2);
      color: #38BDF8;
      font-weight: 700;
      border-color: rgba(56, 189, 248, 0.35);
    }
    .status-online-dot {
      width: 7px;
      height: 7px;
      background: var(--emerald);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--emerald);
    }

    /* View Switcher */
    .view-toggle-bar {
      display: inline-flex;
      background: rgba(9, 14, 26, 0.7);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 3px;
    }
    .view-toggle-btn {
      padding: 6px 11px;
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
    .view-toggle-btn:hover { color: #FFFFFF; }
    .view-toggle-btn.active {
      background: rgba(255, 255, 255, 0.12);
      color: #FFFFFF;
      font-weight: 700;
    }

    /* TABLE VIEW STYLES */
    .table-container {
      container-type: inline-size;
      container-name: tableContainer;
      overflow-x: auto;
      border-radius: 14px;
      border: 1px solid var(--border-subtle);
      background: rgba(8, 12, 22, 0.55);
      box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }
    .table-container::-webkit-scrollbar { height: 6px; }
    .table-container::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
    .table-container::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.14); border-radius: 4px; }

    table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
    th {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A3B8;
      padding: 15px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(13, 20, 36, 0.85);
      white-space: nowrap;
      position: sticky;
      top: 0;
      backdrop-filter: blur(12px);
      z-index: 2;
    }
    td {
      padding: 14px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      font-size: 13.5px;
      vertical-align: middle;
      transition: background 0.15s ease;
      white-space: nowrap;
    }
    tr:hover td { background: rgba(37, 99, 235, 0.05); }

    .code-badge-pill {
      display: inline-flex;
      align-items: center;
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 12px;
      background: rgba(37, 99, 235, 0.12);
      border: 1px solid rgba(37, 99, 235, 0.35);
      color: #60A5FA;
      padding: 4px 9px;
      border-radius: 8px;
      letter-spacing: 0.03em;
      cursor: pointer;
      transition: all 0.2s;
    }
    .code-badge-pill:hover {
      background: rgba(37, 99, 235, 0.25);
      border-color: rgba(37, 99, 235, 0.6);
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
      gap: 7px;
    }
    .tag-installed {
      font-size: 11px;
      font-weight: 700;
      background: rgba(255, 107, 0, 0.12);
      color: #FB923C;
      padding: 2px 8px;
      border-radius: 6px;
      border: 1px solid rgba(255, 107, 0, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 5px;
      width: fit-content;
    }
    .tag-personal {
      font-size: 11px;
      font-weight: 700;
      color: #94A3B8;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 2px 8px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      width: fit-content;
    }

    .group-pill {
      font-size: 12px;
      background: rgba(255, 107, 0, 0.09);
      color: #FDBA74;
      border: 1px solid rgba(255, 107, 0, 0.25);
      padding: 3.5px 10px;
      border-radius: 7px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* Live Radar Badges */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 11.5px;
      font-weight: 800;
      padding: 4px 11px;
      border-radius: 7px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .status-badge.online {
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #34D399;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.2);
    }
    .status-badge.waiting {
      background: rgba(245, 158, 11, 0.12);
      border: 1px solid rgba(245, 158, 11, 0.4);
      color: #FBBF24;
    }
    .status-badge.inactive {
      background: rgba(148, 163, 184, 0.08);
      border: 1px solid rgba(148, 163, 184, 0.2);
      color: #94A3B8;
    }

    .remote-id-tag {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 13px;
      color: #FB923C;
      background: rgba(255, 107, 0, 0.08);
      padding: 4px 10px;
      border-radius: 8px;
      border: 1px solid rgba(255, 107, 0, 0.25);
      letter-spacing: 0.03em;
      display: inline-flex;
      align-items: center;
    }

    /* Action Buttons */
    .row-actions-cluster {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 7px;
    }
    .btn-action {
      height: 33px;
      padding: 0 13px;
      font-size: 12px;
      font-weight: 700;
      border-radius: 9px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      line-height: 1;
      text-decoration: none;
      user-select: none;
    }
    .btn-action.connect {
      background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
      color: #FFFFFF;
      border: 1px solid rgba(56, 189, 248, 0.45);
      box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35);
    }
    .btn-action.connect:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.55);
      filter: brightness(1.12);
    }
    .btn-action.reconnect {
      background: rgba(37, 99, 235, 0.09);
      color: #93C5FD;
      border: 1px solid rgba(37, 99, 235, 0.35);
    }
    .btn-action.reconnect:hover {
      background: rgba(37, 99, 235, 0.2);
      border-color: rgba(37, 99, 235, 0.6);
      color: #FFFFFF;
      transform: translateY(-1px);
    }
    .btn-action.copy {
      background: rgba(255, 255, 255, 0.04);
      color: #CBD5E1;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .btn-action.copy:hover {
      background: rgba(255, 107, 0, 0.14);
      border-color: rgba(255, 107, 0, 0.45);
      color: #FB923C;
      transform: translateY(-1px);
    }
    .btn-action.delete {
      width: 33px;
      height: 33px;
      padding: 0;
      background: transparent;
      color: var(--text-dim);
      border: 1px solid transparent;
      border-radius: 9px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
    }
    .btn-action.delete:hover {
      background: rgba(244, 63, 94, 0.14);
      border-color: rgba(244, 63, 94, 0.35);
      color: #FB7185;
      transform: scale(1.08);
    }

    /* GRID CARDS VIEW */
    .cards-grid-wrap {
      display: none;
      grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
      gap: 18px;
    }
    .device-grid-card {
      background: rgba(10, 16, 29, 0.75);
      border: 1px solid var(--border-subtle);
      border-radius: 18px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.4);
    }
    .device-grid-card:hover {
      border-color: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
      box-shadow: 0 14px 30px -8px rgba(0, 0, 0, 0.6);
    }
    .card-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
    }
    .card-meta-name {
      font-size: 15px;
      font-weight: 800;
      color: #FFFFFF;
    }
    .card-meta-sub {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    .card-inner-box {
      display: flex;
      flex-direction: column;
      gap: 9px;
      background: rgba(6, 9, 19, 0.6);
      border-radius: 12px;
      padding: 14px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .card-row-spec {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 12.5px;
    }
    .card-spec-key { color: var(--text-dim); }
    .card-actions-grid {
      display: grid;
      grid-template-columns: 1fr auto auto;
      gap: 8px;
      margin-top: auto;
    }

    /* Container Queries */
    @container tableContainer (max-width: 780px) {
      th, td { padding: 10px 12px; font-size: 12px; }
      .row-actions-cluster { gap: 4px; }
      .btn-action { height: 29px; padding: 0 8px; font-size: 11px; }
      .remote-id-tag { font-size: 11.5px; padding: 2.5px 6px; }
    }
    @container tableContainer (min-width: 1150px) {
      th { padding: 16px 22px; font-size: 12px; }
      td { padding: 15px 22px; font-size: 14px; }
      .btn-action { height: 35px; padding: 0 15px; font-size: 12.5px; }
    }

    /* HTML5 <dialog> MODALS */
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
      background: rgba(2, 6, 16, 0.82);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .modal-frame {
      background: linear-gradient(145deg, rgba(14, 22, 40, 0.98) 0%, rgba(9, 14, 26, 0.99) 100%);
      border: 1px solid rgba(255, 107, 0, 0.35);
      border-radius: 22px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 40px -5px rgba(255, 107, 0, 0.2);
      overflow: hidden;
      animation: dialogZoom 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes dialogZoom {
      from { opacity: 0; transform: scale(0.92) translateY(12px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header-row {
      padding: 20px 26px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 107, 0, 0.05);
    }
    .modal-headline-wrap { display: flex; align-items: center; gap: 12px; }
    .modal-icon-bubble {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(255, 107, 0, 0.25) 0%, rgba(234, 88, 12, 0.15) 100%);
      border: 1px solid rgba(255, 107, 0, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .modal-headline-title { font-size: 16.5px; font-weight: 800; color: #FFFFFF; }
    .modal-headline-sub { font-size: 12px; color: #94A3B8; margin-top: 2px; }
    .modal-close-x {
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
    .modal-close-x:hover { background: rgba(255, 255, 255, 0.08); color: #FFFFFF; }
    .modal-body-content { padding: 26px; }
    .input-label { display: block; font-size: 12.5px; font-weight: 700; color: #CBD5E1; margin-bottom: 8px; }
    .modal-text-input {
      width: 100%;
      background: rgba(6, 9, 19, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      padding: 13px 16px;
      font-size: 14px;
      color: #FFFFFF;
      outline: none;
      transition: all 0.2s;
    }
    .modal-text-input:focus {
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-glow);
      background: rgba(6, 9, 19, 0.85);
    }
    .modal-footer-row {
      padding: 18px 26px;
      background: rgba(0, 0, 0, 0.3);
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
    }
    .btn-modal-cancel {
      padding: 10px 20px;
      font-size: 13px;
      font-weight: 700;
      border-radius: 10px;
      cursor: pointer;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #CBD5E1;
      transition: all 0.2s;
    }
    .btn-modal-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #FFFFFF; }
    .btn-modal-save {
      background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
      color: #FFFFFF;
      border: none;
      padding: 10px 22px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 800;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px -2px var(--orange-glow);
      transition: all 0.2s;
    }
    .btn-modal-save:hover { transform: translateY(-1px); filter: brightness(1.08); }

    /* Generator Modal Components */
    .mode-toggle-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      background: rgba(6, 9, 19, 0.7);
      padding: 5px;
      border-radius: 12px;
      border: 1px solid var(--border-subtle);
      margin-bottom: 20px;
    }
    .mode-toggle-option {
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
    .mode-toggle-option.active.empresa {
      background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 14px var(--orange-glow);
    }
    .mode-toggle-option.active.personal {
      background: linear-gradient(135deg, var(--primary) 0%, #1D4ED8 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 14px var(--primary-glow);
    }
    .notice-box {
      background: rgba(37, 99, 235, 0.1);
      border: 1px solid rgba(37, 99, 235, 0.25);
      border-radius: 11px;
      padding: 12px 14px;
      font-size: 12.5px;
      line-height: 1.45;
      color: #93C5FD;
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }
    .notice-box.orange {
      background: rgba(255, 107, 0, 0.1);
      border-color: rgba(255, 107, 0, 0.3);
      color: #FDBA74;
    }
    .result-link-card {
      background: rgba(255, 107, 0, 0.08);
      border: 1px dashed rgba(255, 107, 0, 0.4);
      border-radius: 14px;
      padding: 16px;
      margin-top: 20px;
      display: none;
    }
    .result-input-line {
      display: flex;
      gap: 8px;
      margin: 10px 0;
    }
    .result-input-line input {
      flex: 1;
      background: rgba(0,0,0,0.5);
      border: 1px solid var(--border-subtle);
      border-radius: 9px;
      padding: 10px 14px;
      color: #FFFFFF;
      font-size: 12.5px;
      font-family: 'JetBrains Mono', monospace;
      outline: none;
    }

    /* Popover Toast */
    .toast {
      position: fixed;
      inset: auto 26px 26px auto;
      margin: 0;
      background: rgba(14, 22, 40, 0.96);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      color: #FFFFFF;
      border: 1px solid #38BDF8;
      padding: 14px 24px;
      border-radius: 14px;
      font-size: 13.5px;
      font-weight: 700;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.7), 0 0 20px rgba(56, 189, 248, 0.25);
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

    /* Confirm Dialog */
    .confirm-footer-grid {
      padding: 18px 26px 22px 26px;
      background: rgba(0, 0, 0, 0.35);
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      width: 100%;
    }
    .btn-confirm-action {
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
    .btn-confirm-cancel {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.14);
      color: #E2E8F0;
    }
    .btn-confirm-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #FFFFFF; }
    .btn-confirm-danger {
      background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px rgba(239, 68, 68, 0.45);
    }
    .btn-confirm-danger:hover { filter: brightness(1.12); transform: translateY(-1px); }
    .btn-confirm-warning {
      background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 18px -2px var(--orange-glow);
    }
    .btn-confirm-warning:hover { filter: brightness(1.12); transform: translateY(-1px); }

    /* ENHANCED USERS MANAGEMENT MODAL */
    .modal-frame.users-modal-custom {
      max-width: 780px;
      border: 1px solid rgba(56, 189, 248, 0.28);
      box-shadow: 0 25px 65px -12px rgba(0, 0, 0, 0.9), 0 0 45px -5px rgba(37, 99, 235, 0.22);
    }
    .users-modal-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      background: rgba(37, 99, 235, 0.16);
      border: 1px solid rgba(56, 189, 248, 0.3);
      color: #38BDF8;
    }
    .users-modal-badge .dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #38BDF8;
      box-shadow: 0 0 8px #38BDF8;
    }
    .user-create-card {
      background: linear-gradient(145deg, rgba(15, 23, 42, 0.7) 0%, rgba(9, 14, 26, 0.8) 100%);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      padding: 20px 22px;
      margin-bottom: 24px;
      box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.4);
    }
    .user-create-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    .user-create-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13.5px;
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.01em;
    }
    .user-create-title svg {
      color: #FF6B00;
    }
    .user-create-tag {
      font-size: 11px;
      color: #94A3B8;
      font-weight: 500;
    }
    .user-create-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px 18px;
      align-items: start;
    }
    @media (max-width: 640px) {
      .user-create-grid {
        grid-template-columns: 1fr;
      }
    }
    .user-field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .user-field-label {
      font-size: 11.5px;
      font-weight: 700;
      color: #CBD5E1;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .user-field-label svg {
      color: #94A3B8;
    }
    .user-input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .user-input-wrap .field-icon {
      position: absolute;
      left: 14px;
      color: #64748B;
      pointer-events: none;
      transition: color 0.2s;
    }
    .user-input-wrap input,
    .user-input-wrap select {
      width: 100%;
      background: rgba(6, 9, 19, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 11px;
      padding: 10px 14px 10px 40px;
      font-size: 13px;
      color: #FFFFFF;
      outline: none;
      transition: all 0.2s;
      font-family: inherit;
    }
    .user-input-wrap input:focus,
    .user-input-wrap select:focus {
      border-color: #FF6B00;
      background: rgba(6, 9, 19, 0.95);
      box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.25);
    }
    .user-input-wrap input:focus + .field-icon,
    .user-input-wrap select:focus + .field-icon {
      color: #FF6B00;
    }
    .btn-pwd-eye {
      position: absolute;
      right: 12px;
      background: transparent;
      border: none;
      color: #64748B;
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.2s;
    }
    .btn-pwd-eye:hover {
      color: #CBD5E1;
    }
    .user-form-actions {
      grid-column: 1 / -1;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      margin-top: 6px;
      padding-top: 12px;
      border-top: 1px dashed rgba(255, 255, 255, 0.08);
    }
    .btn-submit-operator {
      background: linear-gradient(135deg, #FF6B00 0%, #EA580C 100%);
      color: #FFFFFF;
      border: none;
      padding: 10px 22px;
      border-radius: 11px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 16px -2px rgba(255, 107, 0, 0.45);
      transition: all 0.2s;
    }
    .btn-submit-operator:hover {
      transform: translateY(-1px);
      filter: brightness(1.1);
      box-shadow: 0 6px 20px -2px rgba(255, 107, 0, 0.55);
    }
    .btn-submit-operator:active {
      transform: translateY(0);
    }
    .btn-submit-operator:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    /* Active Users Table Section */
    .spin-icon { animation: spinRotate 0.85s linear infinite; }
    @keyframes spinRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Active Users Table Section */
    .users-table-card {
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      background: rgba(6, 9, 19, 0.6);
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    }
    .users-table-header-bar {
      padding: 14px 20px;
      background: rgba(255, 255, 255, 0.02);
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .users-table-title {
      font-size: 11.5px;
      font-weight: 800;
      color: #94A3B8;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .users-table-container {
      max-height: 250px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
    }
    .users-modern-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }
    .users-modern-table th {
      padding: 11px 18px;
      font-size: 11px;
      font-weight: 700;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      background: rgba(15, 23, 42, 0.6);
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      position: sticky;
      top: 0;
      z-index: 2;
    }
    .users-modern-table td {
      padding: 13px 18px;
      font-size: 13px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      color: #CBD5E1;
      vertical-align: middle;
    }
    .users-modern-table tr:last-child td {
      border-bottom: none;
    }
    .users-modern-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }
    .user-cell-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .user-avatar-disc {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 13px;
      flex-shrink: 0;
      letter-spacing: -0.02em;
    }
    .user-avatar-disc.admin {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(217, 119, 6, 0.15) 100%);
      border: 1px solid rgba(245, 158, 11, 0.4);
      color: #FBBF24;
    }
    .user-avatar-disc.tech {
      background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(56, 189, 248, 0.15) 100%);
      border: 1px solid rgba(56, 189, 248, 0.4);
      color: #38BDF8;
    }
    .user-profile-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .user-profile-name {
      font-weight: 700;
      color: #FFFFFF;
      font-size: 13px;
    }
    .user-profile-handle {
      font-size: 11.5px;
      color: #94A3B8;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .user-role-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 11.5px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .user-role-badge.admin {
      background: rgba(245, 158, 11, 0.14);
      border: 1px solid rgba(245, 158, 11, 0.35);
      color: #FBBF24;
    }
    .user-role-badge.tech {
      background: rgba(56, 189, 248, 0.14);
      border: 1px solid rgba(56, 189, 248, 0.35);
      color: #38BDF8;
    }
    .user-badge-self {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 700;
      background: rgba(148, 163, 184, 0.12);
      border: 1px solid rgba(148, 163, 184, 0.25);
      color: #94A3B8;
    }
    .btn-delete-operator {
      width: 32px;
      height: 32px;
      border-radius: 9px;
      background: rgba(239, 68, 68, 0.08);
      border: 1px solid rgba(239, 68, 68, 0.22);
      color: #F87171;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-delete-operator:hover {
      background: rgba(239, 68, 68, 0.22);
      border-color: rgba(239, 68, 68, 0.45);
      color: #FFFFFF;
      transform: translateY(-1px);
      box-shadow: 0 0 14px rgba(239, 68, 68, 0.3);
    }

    /* OPERATOR LAUNCH & DOWNLOAD COMPONENTS */
    .btn-download-operator-top {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 10px 16px;
      border-radius: 11px;
      font-size: 13px;
      font-weight: 700;
      color: #38BDF8;
      background: rgba(37, 99, 235, 0.14);
      border: 1px solid rgba(56, 189, 248, 0.35);
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-download-operator-top:hover {
      background: rgba(37, 99, 235, 0.25);
      border-color: #38BDF8;
      color: #FFFFFF;
      box-shadow: 0 0 16px rgba(56, 189, 248, 0.3);
      transform: translateY(-1px);
    }



    /* RESPONSIVE ADAPTATIONS */
    @media (max-width: 1080px) {
      .app-sidebar { width: 76px; }
      .brand-logo-wrap span, .badge-enterprise, .nav-label span:not(:first-child), .nav-pill-counter, .nav-section-title, .relay-card, .user-label-group {
        display: none !important;
      }
      .sidebar-brand-box, .sidebar-scroll, .sidebar-user-footer {
        padding: 16px 10px;
        align-items: center;
      }
      .btn-create-session span { display: none; }
      .btn-create-session { padding: 12px; width: 44px; height: 44px; border-radius: 12px; }
      .nav-link { justify-content: center; padding: 12px; }
      .metrics-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 680px) {
      .app-shell { flex-direction: column; }
      .app-sidebar { width: 100%; height: auto; position: static; flex-direction: row; border-right: none; border-bottom: 1px solid var(--border-subtle); }
      .sidebar-scroll { display: none; }
      .metrics-row { grid-template-columns: 1fr; }
      .app-main { padding: 16px; }
      .main-top-header { flex-direction: column; align-items: stretch; }
      .search-input-wrapper { width: 100%; }
    }
  </style>
</head>
<body>

  <div class="app-shell">
    
    <!-- LEFT SIDEBAR -->
    <aside class="app-sidebar">
      <div class="sidebar-brand-box">
        <div class="brand-logo-wrap">
          <img src="flutter/assets/logo_dark.png?v=2" alt="OptimaIT">
        </div>
        <span class="badge-enterprise">PRO</span>
      </div>

      <div class="sidebar-scroll">
        <button type="button" class="btn-create-session" onclick="openSessionModal()">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
          <span>Nueva Sesión</span>
        </button>

        <div>
          <div class="nav-section-title">
            <span>Vistas de Equipos</span>
          </div>
          <ul class="nav-menu">
            <li class="nav-link active empresa" id="navItemEmpresas" onclick="setCategoryTab('empresa')">
              <div class="nav-label">
                <span>🏢</span>
                <span>Empresas</span>
              </div>
              <span class="nav-pill-counter" id="countTabEmpresa">0</span>
            </li>
            <li class="nav-link" id="navItemPersonal" onclick="setCategoryTab('personal')">
              <div class="nav-label">
                <span>👤</span>
                <span>Personas Naturales</span>
              </div>
              <span class="nav-pill-counter" id="countTabPersonal">0</span>
            </li>
            <li class="nav-link" id="navItemAll" onclick="setCategoryTab('all')">
              <div class="nav-label">
                <span>🌐</span>
                <span>Todos los Equipos</span>
              </div>
              <span class="nav-pill-counter" id="countTabAll">0</span>
            </li>
          </ul>
        </div>

        <div>
          <div class="nav-section-title">
            <span>Sedes / Grupos</span>
            <button type="button" class="btn-add-group-badge" onclick="promptNewGroup()" title="Crear nueva sede">+</button>
          </div>
          <ul class="nav-menu" id="sidebarGroupsList">
            <!-- Dynamic groups -->
          </ul>
        </div>

        <!-- Relay Server Status -->
        <div class="relay-card">
          <div class="relay-title">
            <div class="radar-pulse-dot"></div>
            <span>Relay Activo</span>
          </div>
          <div class="relay-host-text" title="<?= htmlspecialchars($relayHost) ?>"><?= htmlspecialchars($relayHost) ?></div>
        </div>
      </div>

      <!-- User Profile Box -->
      <div class="sidebar-user-footer">
        <div class="user-info-cluster">
          <div class="user-avatar-badge">
            <?= strtoupper(substr($currentUser['name'] ?? $currentUser['username'], 0, 1)) ?>
          </div>
          <div class="user-label-group">
            <span class="user-label-name"><?= htmlspecialchars($currentUser['name'] ?? $currentUser['username']) ?></span>
            <span class="user-label-role <?= $isAdmin ? 'role-admin' : 'role-tech' ?>">
              <?= $isAdmin ? '👑 Administrador' : '🛠️ Técnico' ?>
            </span>
          </div>
        </div>

        <div class="user-footer-btns">
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

    <!-- MAIN APP CANVAS -->
    <main class="app-main">
      
      <!-- Top Title & Search Bar -->
      <header class="main-top-header">
        <div class="page-title-cluster">
          <h1>Centro de Asistencia Técnica</h1>
          <p>Supervisión en tiempo real y conexión desatendida corporativa</p>
        </div>

        <div class="top-toolbar-actions">
          <div class="search-input-wrapper">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" class="search-box" placeholder="Buscar por equipo, ID, sede..." oninput="handleSearchInput(event)">
          </div>

          <a href="api.php?action=download_tech_client" class="btn-download-operator-top" title="Descargar Consola de Operador Optima IT (.exe)">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Consola de Operador</span>
          </a>

          <button type="button" class="btn-create-session" style="padding:11px 18px; font-size:13px; width:auto;" onclick="openSessionModal()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            <span>Generar Enlace</span>
          </button>
        </div>
      </header>

      <!-- KPI METRICS SUMMARY -->
      <div class="metrics-row">
        <div class="metric-card card-online">
          <div class="metric-meta">
            <p>Clientes En Línea</p>
            <h3 id="statOnline">0</h3>
          </div>
          <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.828a5 5 0 010-7.072m7.072 0a5 5 0 010 7.072M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
          </div>
        </div>

        <div class="metric-card card-empresa">
          <div class="metric-meta">
            <p>Equipos Empresa</p>
            <h3 id="statEmpresas">0</h3>
          </div>
          <div class="metric-icon-box" style="background: rgba(255, 107, 0, 0.15); color: #FF6B00;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
          </div>
        </div>

        <div class="metric-card card-personal">
          <div class="metric-meta">
            <p>Personas Naturales</p>
            <h3 id="statPersonal">0</h3>
          </div>
          <div class="metric-icon-box" style="background: rgba(37, 99, 235, 0.15); color: #38BDF8;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
        </div>

        <div class="metric-card card-total">
          <div class="metric-meta">
            <p>Total Registrados</p>
            <h3 id="statTotal">0</h3>
          </div>
          <div class="metric-icon-box" style="background: rgba(148, 163, 184, 0.15); color: #94A3B8;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
        </div>
      </div>

      <!-- MAIN DATA CONTENT PANEL -->
      <section class="content-panel">
        
        <!-- Filter Bar -->
        <div class="filter-header-bar">
          <div class="filter-title-group">
            <div class="active-view-heading" id="activeCategoryTitle">
              🏢 Equipos Corporativos
            </div>
            <span class="active-badge-tag" id="activeCategoryBadge">0 equipos</span>
          </div>

          <div class="filter-controls-right">
            <!-- Sede Dropdown Filter -->
            <div class="group-dropdown-wrap" id="groupFilterBox">
              <select id="groupFilterSelect" class="group-dropdown-select" onchange="selectGroupDropdown(this.value)">
                <option value="all">🏢 Todas las Sedes</option>
              </select>
              <button type="button" class="btn-add-group-badge" onclick="promptNewGroup()" title="Crear nueva sede">+</button>
            </div>

            <!-- Status Tabs -->
            <div class="status-filter-group">
              <div class="status-tab-btn active" data-status="all" onclick="setStatusFilter('all')">Todos</div>
              <div class="status-tab-btn" data-status="online" onclick="setStatusFilter('online')"><span class="status-online-dot"></span> En Línea</div>
              <div class="status-tab-btn" data-status="inactive" onclick="setStatusFilter('inactive')">Inactivos</div>
            </div>

            <!-- View Switcher -->
            <div class="view-toggle-bar">
              <div class="view-toggle-btn active" id="btnViewTable" onclick="switchView('table')" title="Vista Tabla">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <span>Tabla</span>
              </div>
              <div class="view-toggle-btn" id="btnViewGrid" onclick="switchView('grid')" title="Vista Tarjetas">
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
                <th style="text-align:right; width:260px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="sessionsBody">
              <tr>
                <td colspan="6" style="text-align:center; padding:45px 20px; color:var(--text-dim);">Cargando registros...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Cards View -->
        <div class="cards-grid-wrap" id="gridViewContainer">
          <!-- Dynamically populated -->
        </div>

      </section>

    </main>

  </div>

  <!-- TOAST NOTIFICATION (POPOVER) -->
  <div class="toast" id="toastBox" popover="manual">
    <span id="toastMsg">Acción completada</span>
  </div>

  <!-- MODAL: GENERAR NUEVA SESIÓN -->
  <dialog id="newSessionModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
    <div class="modal-frame" style="max-width: 520px;">
      <div class="modal-header-row">
        <div class="modal-headline-wrap">
          <div class="modal-icon-bubble">⚡</div>
          <div>
            <h3 class="modal-headline-title">Generar Enlace de Asistencia</h3>
            <p class="modal-headline-sub">Crea un enlace para instalación corporativa o soporte directo</p>
          </div>
        </div>
        <button type="button" class="modal-close-x" onclick="closeSessionModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body-content">
        <form id="createForm" onsubmit="handleCreateSession(event)">
          <label class="input-label">Modalidad de Asistencia:</label>
          <div class="mode-toggle-grid">
            <div class="mode-toggle-option empresa active" id="btnModeEmpresa" onclick="setCreationMode('empresa')">
              🏢 Empresa (Permanente)
            </div>
            <div class="mode-toggle-option personal" id="btnModePersonal" onclick="setCreationMode('personal')">
              👤 Persona Natural (Temporal)
            </div>
          </div>
          <input type="hidden" id="clientTypeInput" value="empresa">

          <div class="notice-box orange" id="noticeText">
            <span>🏢 <strong>Modo Empresa:</strong> Genera instalador desatendido que iniciará automáticamente con Windows.</span>
          </div>

          <div style="margin-bottom: 16px;">
            <label class="input-label" id="lblClientName">Nombre de la Empresa o Equipo:</label>
            <input type="text" id="clientNameInput" class="modal-text-input" placeholder="Ej: Sucursal Central / Caja 01" required>
          </div>

          <div style="margin-bottom: 22px;" id="empresaGroupContainer">
            <label class="input-label" for="groupSelect">Asignar a Grupo Corporativo / Sede:</label>
            <div style="display:flex; gap:8px;">
              <select id="groupSelect" class="modal-text-input" style="cursor:pointer;"></select>
              <button type="button" class="btn-modal-save" style="padding:0 14px; font-size:16px;" onclick="promptNewGroup()" title="Crear nueva sede">+</button>
            </div>
          </div>

          <button type="submit" class="btn-create-session" id="btnSubmit" style="height:48px; font-size:14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <span id="btnSubmitText">Generar Enlace para Empresa</span>
          </button>
        </form>

        <div class="result-link-card" id="linkBox">
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:11px; font-weight:800; color:var(--orange);" id="shareModeTitle">ENLACE GENERADO</span>
            <span id="badgeShareType" style="font-size:10px; padding:2px 8px; border-radius:5px; font-weight:800;"></span>
          </div>
          <div class="result-input-line">
            <input type="text" id="generatedUrl" readonly>
            <button type="button" class="btn-modal-save" style="padding:0 14px; font-size:12.5px;" onclick="copyLink()">📋 Copiar</button>
          </div>
          <p style="font-size:11.5px; color:var(--text-dim); line-height:1.4;" id="shareExplanation"></p>
        </div>
      </div>
    </div>
  </dialog>

  <!-- MODAL: NUEVA SEDE / GRUPO -->
  <dialog id="newGroupModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
    <div class="modal-frame">
      <div class="modal-header-row">
        <div class="modal-headline-wrap">
          <div class="modal-icon-bubble">🏢</div>
          <div>
            <h3 class="modal-headline-title">Nueva Sede o Grupo</h3>
            <p class="modal-headline-sub">Organiza tus equipos por empresa, sucursal o área</p>
          </div>
        </div>
        <button type="button" class="modal-close-x" onclick="closeGroupModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body-content">
        <label for="newGroupNameInput" class="input-label">Nombre de la Sede o Empresa</label>
        <input type="text" id="newGroupNameInput" class="modal-text-input" placeholder="Ej: Sucursal Norte / Farmacia Central / Almacén" autocomplete="off" onkeydown="handleModalKeydown(event)">
        <p style="font-size:12px; color:var(--text-dim); margin-top:10px;">💡 Se agregará a la lista de sedes y filtros del panel.</p>
      </div>

      <div class="modal-footer-row">
        <button type="button" class="btn-modal-cancel" onclick="closeGroupModal()">Cancelar</button>
        <button type="button" class="btn-modal-save" id="btnSaveGroup" onclick="submitNewGroup()">
          <span>Guardar Sede</span>
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
        </button>
      </div>
    </div>
  </dialog>

  <?php if ($isAdmin): ?>
  <!-- MODAL: GESTIÓN DE TÉCNICOS & ACCESOS -->
  <dialog id="usersModal" class="app-dialog" onclick="handleDialogBackdropClick(event)">
    <div class="modal-frame users-modal-custom">
      <div class="modal-header-row" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(56, 189, 248, 0.03) 100%); border-bottom: 1px solid rgba(56, 189, 248, 0.2);">
        <div class="modal-headline-wrap">
          <div class="modal-icon-bubble" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.3) 0%, rgba(56, 189, 248, 0.15) 100%); border-color: rgba(56, 189, 248, 0.45); color: #38BDF8;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          </div>
          <div>
            <div style="display:flex; align-items:center; gap:10px;">
              <h3 class="modal-headline-title">Gestión de Cuentas y Operadores</h3>
              <div class="users-modal-badge" id="usersCountBadge">
                <span class="dot"></span>
                <span id="usersCountText">Cargando...</span>
              </div>
            </div>
            <p class="modal-headline-sub">Control de credenciales y roles con acceso a la consola de soporte</p>
          </div>
        </div>
        <button type="button" class="modal-close-x" onclick="closeUsersModal()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body-content" style="padding: 22px 26px;">
        <!-- Card: Crear Nuevo Operador -->
        <div class="user-create-card">
          <div class="user-create-header">
            <div class="user-create-title">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
              <span>Registrar Nuevo Operador</span>
            </div>
            <span class="user-create-tag">Credenciales encriptadas con Bcrypt</span>
          </div>

          <form id="createUserForm" onsubmit="handleCreateUser(event)">
            <div class="user-create-grid">
              <!-- Nombre Completo -->
              <div class="user-field-group">
                <label class="user-field-label" for="newUserName">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  Nombre Completo
                </label>
                <div class="user-input-wrap">
                  <input type="text" id="newUserName" placeholder="Ej: Juan Perez" required autocomplete="name">
                  <svg class="field-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
              </div>

              <!-- Nombre de Usuario -->
              <div class="user-field-group">
                <label class="user-field-label" for="newUserUsername">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                  Usuario de Acceso
                </label>
                <div class="user-input-wrap">
                  <input type="text" id="newUserUsername" placeholder="Ej: jperez" required autocomplete="username">
                  <svg class="field-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                </div>
              </div>

              <!-- Contraseña -->
              <div class="user-field-group">
                <label class="user-field-label" for="newUserPassword">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                  Contraseña de Seguridad
                </label>
                <div class="user-input-wrap">
                  <input type="password" id="newUserPassword" placeholder="Mínimo 4 caracteres" required autocomplete="new-password">
                  <svg class="field-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                  <button type="button" class="btn-pwd-eye" onclick="togglePasswordVisibility('newUserPassword', this)" title="Ver / Ocultar">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  </button>
                </div>
              </div>

              <!-- Rol / Nivel -->
              <div class="user-field-group">
                <label class="user-field-label" for="newUserRole">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                  Privilegio / Rol
                </label>
                <div class="user-input-wrap">
                  <select id="newUserRole">
                    <option value="tecnico">Técnico de Soporte (Estándar)</option>
                    <option value="admin">Administrador General (Acceso Total)</option>
                  </select>
                  <svg class="field-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
              </div>

              <!-- Action Bar -->
              <div class="user-form-actions">
                <button type="submit" class="btn-submit-operator" id="btnSubmitUser">
                  <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                  <span>Crear y Habilitar Operador</span>
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Section: Cuentas Activas Table -->
        <div class="users-table-card">
          <div class="users-table-header-bar">
            <div class="users-table-title">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Cuentas Activas en el Sistema
            </div>
            <span style="font-size: 11px; color: #64748B;">Sincronizado en tiempo real</span>
          </div>

          <div class="users-table-container">
            <table class="users-modern-table">
              <thead>
                <tr>
                  <th style="width: 44%;">Operador / Nombre</th>
                  <th style="width: 26%;">Rol Asignado</th>
                  <th style="width: 18%;">Fecha Alta</th>
                  <th style="width: 12%; text-align: right;">Acciones</th>
                </tr>
              </thead>
              <tbody id="usersTableBody">
                <tr><td colspan="4" style="text-align:center; padding:24px; color:#64748B;">Cargando lista de operadores...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="modal-footer-row" style="justify-content: space-between;">
        <div style="font-size: 12px; color: #64748B; display: flex; align-items: center; gap: 6px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
          Los técnicos solo gestionan sesiones; los administradores tienen control de usuarios.
        </div>
        <button type="button" class="btn-modal-cancel" onclick="closeUsersModal()">Cerrar</button>
      </div>
    </div>
  </dialog>
  <?php endif; ?>



  <!-- MODAL: CONFIRMAR ACCIÓN -->
  <dialog id="confirmActionModal" class="app-dialog" style="z-index: 10005;" onclick="handleDialogBackdropClick(event)">
    <div class="modal-frame" style="max-width: 440px; border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 40px -5px rgba(239, 68, 68, 0.25);">
      <div class="modal-header-row" style="background: rgba(239, 68, 68, 0.06);">
        <div class="modal-headline-wrap">
          <div class="modal-icon-bubble" id="confirmModalIcon" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(220, 38, 38, 0.15) 100%); border-color: rgba(239, 68, 68, 0.45);">
            ⚠️
          </div>
          <div>
            <h3 class="modal-headline-title" id="confirmModalTitle">Confirmar Acción</h3>
            <p class="modal-headline-sub">Por favor confirma para continuar</p>
          </div>
        </div>
        <button type="button" class="modal-close-x" onclick="closeConfirmDialog()" title="Cerrar">&times;</button>
      </div>

      <div class="modal-body-content" style="padding: 24px;">
        <p id="confirmModalMessage" style="font-size: 14px; color: #E2E8F0; line-height: 1.5; margin: 0;"></p>
      </div>

      <div class="confirm-footer-grid">
        <button type="button" class="btn-confirm-action btn-confirm-cancel" onclick="closeConfirmDialog()">Cancelar</button>
        <button type="button" id="btnConfirmAction" class="btn-confirm-action btn-confirm-danger" onclick="executeConfirmedAction()">Confirmar</button>
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
        notice.className = 'notice-box orange';
        notice.innerHTML = `<span>🏢 <strong>Modo Empresa:</strong> Genera instalador desatendido que iniciará automáticamente con Windows.</span>`;
        grpCont.style.display = 'block';
        lblName.innerText = "Nombre de la Empresa o Equipo:";
        btnText.innerText = "Generar Enlace para Empresa";
      } else {
        btnE.classList.remove('active');
        btnP.classList.add('active');
        notice.className = 'notice-box';
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
      
      // Update sidebar nav items
      document.getElementById('navItemEmpresas').classList.toggle('active', cat === 'empresa');
      document.getElementById('navItemPersonal').classList.toggle('active', cat === 'personal');
      document.getElementById('navItemAll').classList.toggle('active', cat === 'all');

      // Update workspace panel title
      const titleElem = document.getElementById('activeCategoryTitle');
      const grpFilterBox = document.getElementById('groupFilterBox');
      const thGrupo = document.getElementById('thColGrupo');

      if (cat === 'empresa') {
        titleElem.innerHTML = `🏢 Equipos Corporativos`;
        if (grpFilterBox) grpFilterBox.style.display = 'inline-flex';
        if (thGrupo) thGrupo.style.display = 'table-cell';
      } else if (cat === 'personal') {
        titleElem.innerHTML = `👤 Personas Naturales`;
        if (grpFilterBox) grpFilterBox.style.display = 'none';
        if (thGrupo) thGrupo.style.display = 'none';
      } else {
        titleElem.innerHTML = `🌐 Todos los Equipos`;
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
      document.querySelectorAll('.status-tab-btn').forEach(f => {
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
          
          // Toast alert for newly connected equipment
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

      // Generator group select
      const sel = document.getElementById('groupSelect');
      if (sel) {
        const curr = sel.value;
        sel.innerHTML = groupArr.map(g => `<option value="${escapeHtml(g)}">🏢 ${escapeHtml(g)}</option>`).join('');
        if (curr && groupArr.includes(curr)) sel.value = curr;
      }

      // Table filter select
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

      // Sidebar groups list
      const sList = document.getElementById('sidebarGroupsList');
      if (sList) {
        sList.innerHTML = groupArr.slice(0, 6).map(g => {
          const count = rawSessions.filter(s => s.group_name === g).length;
          const isAct = (currentCategory === 'empresa' && currentGroupFilter === g);
          return `
            <li class="nav-link ${isAct ? 'active empresa' : ''}" onclick="setCategoryTab('empresa'); selectGroupDropdown('${escapeHtml(g)}');">
              <div class="nav-label">
                <span style="font-size:12px;">📁</span>
                <span style="font-size:12.5px;">${escapeHtml(g)}</span>
              </div>
              <span class="nav-pill-counter">${count}</span>
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

      // 1. Filter category
      let list = rawSessions;
      if (currentCategory === 'empresa') {
        list = list.filter(s => s.client_type === 'empresa');
      } else if (currentCategory === 'personal') {
        list = list.filter(s => s.client_type !== 'empresa');
      }

      // 2. Filter group
      if (currentCategory !== 'personal' && currentGroupFilter !== 'all') {
        list = list.filter(s => (s.group_name || '') === currentGroupFilter);
      }

      // 3. Filter status
      if (currentStatusFilter === 'online') {
        list = list.filter(s => s.status === 'online');
      } else if (currentStatusFilter === 'inactive') {
        list = list.filter(s => s.status === 'closed' || s.status === 'idle' || s.status === 'offline');
      }

      // 4. Search query
      if (currentSearchQuery) {
        const q = currentSearchQuery.toLowerCase();
        list = list.filter(s => 
          (s.client_name && s.client_name.toLowerCase().includes(q)) ||
          (s.session_id && s.session_id.toLowerCase().includes(q)) ||
          (s.rustdesk_id && s.rustdesk_id.toLowerCase().includes(q)) ||
          (s.group_name && s.group_name.toLowerCase().includes(q))
        );
      }

      // Update badge count
      const badgeElem = document.getElementById('activeCategoryBadge');
      if (badgeElem) badgeElem.innerText = `${list.length} equipo${list.length === 1 ? '' : 's'}`;

      const colSpan = isPersonalView ? 5 : 6;
      if (!list || list.length === 0) {
        let emptyMsg = "No hay registros en esta sección.";
        if (currentCategory === 'empresa') {
          emptyMsg = currentGroupFilter === 'all' 
            ? "No hay equipos empresariales registrados aún. Haz clic en '+ Nueva Sesión' para instalar el primer equipo."
            : `No hay equipos asignados a la sede "${escapeHtml(currentGroupFilter)}".`;
        } else if (currentCategory === 'personal') {
          emptyMsg = "No hay asistencias temporales activas.";
        }
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding:50px 20px; color:var(--text-dim); font-size:13.5px;"><div style="font-size:32px; margin-bottom:12px;">📋</div>${emptyMsg}</td></tr>`;
        gridContainer.innerHTML = `<div style="grid-column: 1 / -1; text-align:center; padding:50px 20px; color:var(--text-dim); font-size:13.5px;"><div style="font-size:36px; margin-bottom:12px;">🗂️</div>${emptyMsg}</div>`;
        return;
      }

      // Render Table Rows
      tbody.innerHTML = list.map(s => {
        const isOnline = s.status === 'online';
        const isWaiting = s.status === 'waiting';
        const isEmpresa = s.client_type === 'empresa';

        let badgeStatus = '';
        if (isOnline) {
          badgeStatus = `<span class="status-badge online">● EN LÍNEA</span>`;
        } else if (isWaiting) {
          badgeStatus = `<span class="status-badge waiting">⏳ En espera</span>`;
        } else {
          badgeStatus = `<span class="status-badge inactive">Inactivo</span>`;
        }

        const tagType = isEmpresa 
          ? `<span class="tag-installed">🏢 Empresa (Auto-inicio)</span>` 
          : `<span class="tag-personal">👤 Temporal</span>`;

        const groupCell = !isPersonalView ? `
          <td>
            <span class="group-pill">
              🏢 ${escapeHtml(s.group_name || 'General')}
            </span>
          </td>
        ` : '';

        const idDisplay = s.rustdesk_id 
          ? `<span class="remote-id-tag">${s.rustdesk_id}</span>` 
          : `<span style="font-size:12px; color:var(--text-dim); white-space:nowrap;">⏳ Pendiente</span>`;

        let connectBtn = '';
        if (s.rustdesk_id) {
          if (isOnline) {
            connectBtn = `
              <button onclick="connectSession('${s.rustdesk_id}')" class="btn-action connect" title="Conectar por RustDesk a ${s.rustdesk_id}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <span>Conectar</span>
              </button>
            `;
          } else {
            connectBtn = `
              <button onclick="connectSession('${s.rustdesk_id}')" class="btn-action reconnect" title="Intentar conectar con ${s.rustdesk_id}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span>Conectar</span>
              </button>
            `;
          }
        }

        const copyBtn = `
          <button onclick="copySpecificUrl('${s.session_id}')" class="btn-action copy" title="Copiar enlace de descarga para este equipo">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
            <span>Copiar Link</span>
          </button>
        `;

        const deleteBtn = `
          <button onclick="deleteSessionPermanently('${s.session_id}')" class="btn-action delete" title="Eliminar definitivamente">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
          </button>
        `;

        return `
          <tr>
            <td><span class="code-badge-pill" onclick="copySpecificUrl('${s.session_id}')" title="Clic para copiar enlace">${s.session_id}</span></td>
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
              <div class="row-actions-cluster">
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
          ? `<span class="status-badge online">● EN LÍNEA</span>` 
          : `<span class="status-badge inactive">Inactivo</span>`;

        let connectBtnGrid = s.rustdesk_id ? `
          <button onclick="connectSession('${s.rustdesk_id}')" class="btn-action ${isOnline ? 'connect' : 'reconnect'}" style="width:100%; justify-content:center; height:36px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            <span>Conectar</span>
          </button>
        ` : `<div style="font-size:12px; color:var(--text-dim); text-align:center; padding:8px;">⏳ Pendiente de instalación</div>`;

        return `
          <div class="device-grid-card">
            <div class="card-head">
              <div class="card-meta-sub">
                <span class="card-meta-name">🖥️ ${escapeHtml(s.client_name)}</span>
                <span class="code-badge-pill" style="width:fit-content;" onclick="copySpecificUrl('${s.session_id}')">${s.session_id}</span>
              </div>
              ${statusPill}
            </div>

            <div class="card-inner-box">
              <div class="card-row-spec">
                <span class="card-spec-key">Tipo:</span>
                <span>${isEmpresa ? '🏢 Empresa' : '👤 Temporal'}</span>
              </div>
              ${isEmpresa ? `
              <div class="card-row-spec">
                <span class="card-spec-key">Sede:</span>
                <span style="color:#FDBA74; font-weight:700;">🏢 ${escapeHtml(s.group_name || 'General')}</span>
              </div>` : ''}
              <div class="card-row-spec">
                <span class="card-spec-key">ID Remoto:</span>
                <span class="remote-id-tag">${s.rustdesk_id || '---'}</span>
              </div>
            </div>

            <div class="card-actions-grid">
              ${connectBtnGrid}
              <button onclick="copySpecificUrl('${s.session_id}')" class="btn-action copy" title="Copiar Link" style="height:36px; padding:0 12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
              </button>
              <button onclick="deleteSessionPermanently('${s.session_id}')" class="btn-action delete" title="Eliminar" style="height:36px; width:36px;">
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
            document.getElementById('badgeShareType').style.background = 'rgba(255,107,0,0.2)';
            document.getElementById('badgeShareType').style.color = '#FB923C';
            document.getElementById('shareExplanation').innerText = 'Descarga con elevación UAC y servicio en segundo plano que iniciará con Windows.';
          } else {
            document.getElementById('shareModeTitle').innerText = 'ENLACE PERSONA NATURAL';
            document.getElementById('badgeShareType').innerText = 'NATURAL';
            document.getElementById('badgeShareType').style.background = 'rgba(37,99,235,0.2)';
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
        showToast("⚠️ Ingrese un nombre de sede válido");
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
        btn.innerHTML = `<span>Guardar Sede</span> <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>`;
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
      if (!id) return;
      const targetSession = rawSessions.find(s => s.rustdesk_id === id) || {
        client_name: 'Equipo Remoto'
      };

      const relayHost = '155.133.197.176';
      const relayKey = 'wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU=';
      const pwd = targetSession.password || '';

      // Mark session online in API
      fetch(`api.php?action=mark_online&id=${encodeURIComponent(id)}`).then(() => loadSessions()).catch(() => {});

      showToast(`⚡ Conectando con ${targetSession.client_name || id}...`);

      let launchUrl = `rustdesk://${id}`;
      if (pwd) {
        launchUrl += `?password=${encodeURIComponent(pwd)}`;
      }
      window.location.href = launchUrl;
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

    function getInitials(name) {
      if (!name) return 'OP';
      const parts = name.trim().split(/\s+/);
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    }

    function togglePasswordVisibility(inputId, btn) {
      const input = document.getElementById(inputId);
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        btn.style.color = '#38BDF8';
      } else {
        input.type = 'password';
        btn.style.color = '#64748B';
      }
    }

    async function loadUsersList() {
      const tbody = document.getElementById('usersTableBody');
      const countText = document.getElementById('usersCountText');
      if (!tbody) return;
      try {
        const res = await fetch('api.php?action=list_users');
        const data = await res.json();
        if (data.success && data.users) {
          if (countText) {
            countText.innerText = `${data.users.length} ${data.users.length === 1 ? 'Operador' : 'Operadores'}`;
          }
          if (data.users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:24px; color:#64748B;">No hay operadores registrados</td></tr>';
            return;
          }
          tbody.innerHTML = data.users.map(u => {
            const isAdminUser = u.role === 'admin';
            const roleClass = isAdminUser ? 'admin' : 'tech';
            const initials = getInitials(u.name || u.username);
            const roleIcon = isAdminUser 
              ? `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>`
              : `<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>`;

            const roleBadge = `
              <span class="user-role-badge ${roleClass}">
                ${roleIcon}
                ${isAdminUser ? 'Administrador' : 'Técnico'}
              </span>
            `;
            
            const isSelf = u.username.toLowerCase() === '<?= strtolower($currentUser['username'] ?? '') ?>';
            const deleteBtn = isSelf 
              ? `<span class="user-badge-self">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                  Tú (Activo)
                 </span>` 
              : `<button type="button" class="btn-delete-operator" onclick="deleteUserAccount('${escapeHtml(u.username)}')" title="Eliminar operador">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                 </button>`;

            const dateStr = u.created_at ? escapeHtml(u.created_at.split(' ')[0] || u.created_at) : 'Activo';

            return `
              <tr>
                <td>
                  <div class="user-cell-profile">
                    <div class="user-avatar-disc ${roleClass}">${initials}</div>
                    <div class="user-profile-info">
                      <span class="user-profile-name">${escapeHtml(u.name || u.username)}</span>
                      <span class="user-profile-handle">@${escapeHtml(u.username)}</span>
                    </div>
                  </div>
                </td>
                <td>${roleBadge}</td>
                <td style="font-size:12px; color:#94A3B8;">${dateStr}</td>
                <td style="text-align:right;">${deleteBtn}</td>
              </tr>
            `;
          }).join('');
        } else {
          if (countText) countText.innerText = "Error";
          tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:18px; color:#F87171;">${data.error || 'Error al cargar'}</td></tr>`;
        }
      } catch (err) {
        if (countText) countText.innerText = "Error";
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:18px; color:#F87171;">Error de conexión</td></tr>';
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
      btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin-icon"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10"></path></svg> <span>Guardando...</span>`;

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
          showToast(`Operador "${u}" creado con éxito`);
          document.getElementById('createUserForm').reset();
          loadUsersList();
        } else {
          showToast(data.error || "Error al crear usuario");
        }
      } catch (err) {
        showToast("Error al procesar la solicitud");
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg> <span>Crear y Habilitar Operador</span>`;
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
            formData.append('target_user', username);
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
      const card = modal.querySelector('.modal-frame');
      const iconBadge = document.getElementById('confirmModalIcon');
      document.getElementById('confirmModalTitle').innerText = title || 'Confirmar Acción';
      document.getElementById('confirmModalMessage').innerHTML = message || '';
      if (iconHtml) {
        iconBadge.innerHTML = iconHtml;
      }
      const btn = document.getElementById('btnConfirmAction');
      btn.innerText = confirmText || 'Confirmar';
      if (isDanger) {
        btn.className = 'btn-confirm-action btn-confirm-danger';
        if (card) {
          card.style.borderColor = 'rgba(239, 68, 68, 0.45)';
          card.style.boxShadow = '0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(239, 68, 68, 0.25)';
        }
        if (iconBadge) {
          iconBadge.style.background = 'linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(220, 38, 38, 0.15) 100%)';
          iconBadge.style.borderColor = 'rgba(239, 68, 68, 0.45)';
        }
      } else {
        btn.className = 'btn-confirm-action btn-confirm-warning';
        if (card) {
          card.style.borderColor = 'rgba(255, 107, 0, 0.4)';
          card.style.boxShadow = '0 25px 60px -12px rgba(0, 0, 0, 0.85), 0 0 35px -5px rgba(255, 107, 0, 0.25)';
        }
        if (iconBadge) {
          iconBadge.style.background = 'linear-gradient(135deg, rgba(255, 107, 0, 0.25) 0%, rgba(234, 88, 12, 0.15) 100%)';
          iconBadge.style.borderColor = 'rgba(255, 107, 0, 0.45)';
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
