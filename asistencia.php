<?php
$sessionId = trim($_GET['sesion'] ?? '');
if (empty($sessionId)) {
    die("Error: No se proporcionó un código de sesión válido.");
}

$dataFile = __DIR__ . '/data/sessions.json';
$sessions = [];
if (file_exists($dataFile)) {
    $sessions = json_decode(file_get_contents($dataFile), true) ?: [];
}

$session = $sessions[$sessionId] ?? null;
$clientName = $session['client_name'] ?? 'Cliente';
$clientType = $session['client_type'] ?? 'personal'; // 'personal' o 'empresa'
$groupName = $session['group_name'] ?? ($clientType === 'empresa' ? 'Empresas' : 'Personas Naturales');

// Auto-download action
if (isset($_GET['descargar'])) {
    $file = __DIR__ . '/OptimaIT_Asistente.exe';
    if (!file_exists($file)) {
        die("Error: Archivo asistente no disponible en el servidor.");
    }
    
    // Prefix determines self-elevation in C# launcher
    $prefix = ($clientType === 'empresa') ? "OptimaIT_Empresa" : "OptimaIT_Asistente";
    $serverHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : 'optimait.155.133.197.176.sslip.io';
    if ($serverHost === 'localhost' || $serverHost === '127.0.0.1' || $serverHost === '155.133.197.176') {
        $serverHost = 'optimait.155.133.197.176.sslip.io';
    }
    $downloadName = "{$prefix}_{$sessionId}.exe";
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Expires: 0');
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OptimaIT - Asistencia Remota para <?php echo htmlspecialchars($clientName); ?></title>
  <link rel="icon" type="image/png" href="res/32x32.png">
  <link rel="shortcut icon" href="favicon.ico">
  <meta name="color-scheme" content="dark light">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #090D16;
      --surface: rgba(15, 23, 42, 0.85);
      --border: rgba(255, 255, 255, 0.08);
      --border-focus: rgba(255, 255, 255, 0.4);
      --border-glow: rgba(255, 255, 255, 0.1);
      --text: #F8FAFC;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
      --btn-bg: #FFFFFF;
      --btn-text: #090D16;
      --btn-hover: #F1F5F9;
      --btn-shadow: rgba(255, 255, 255, 0.18);
      --emerald: #10B981;
      --amber: #F59E0B;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    body {
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* Ambient Lighting Mesh - Neutral Modern */
    body::before {
      content: '';
      position: fixed;
      top: -20%;
      left: 15%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
      filter: blur(80px);
      z-index: 0;
      pointer-events: none;
    }

    body::after {
      content: '';
      position: fixed;
      bottom: -20%;
      right: 15%;
      width: 650px;
      height: 650px;
      background: radial-gradient(circle, rgba(148, 163, 184, 0.05) 0%, transparent 70%);
      filter: blur(90px);
      z-index: 0;
      pointer-events: none;
    }

    /* Watermark Background Layer */
    .watermark-bg {
      position: fixed;
      inset: 0;
      overflow: hidden;
      pointer-events: none;
      z-index: 0;
      user-select: none;
    }
    .wm-icon {
      position: absolute;
      opacity: 0.04;
      color: #94A3B8;
      transition: opacity 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .wm-icon svg {
      width: 100%;
      height: 100%;
    }
    .wm-orange, .wm-blue {
      opacity: 0.045;
      color: #CBD5E1;
    }

    @keyframes floatA {
      0% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-12px) rotate(2deg); }
      100% { transform: translateY(0px) rotate(0deg); }
    }
    @keyframes floatB {
      0% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(14px) rotate(-3deg); }
      100% { transform: translateY(0px) rotate(0deg); }
    }
    @keyframes floatC {
      0% { transform: translateY(0px) scale(1); }
      50% { transform: translateY(-10px) scale(1.06); }
      100% { transform: translateY(0px) scale(1); }
    }

    .anim-float-a { animation: floatA 8s ease-in-out infinite; }
    .anim-float-b { animation: floatB 10s ease-in-out infinite; }
    .anim-float-c { animation: floatC 9s ease-in-out infinite; }

    /* Card Container with Border Beam */
    .asistencia-container {
      width: 100%;
      max-width: 480px;
      position: relative;
      z-index: 2;
      animation: enterCard 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes enterCard {
      from {
        opacity: 0;
        transform: translateY(20px) scale(0.97);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Moving Contour Light (Border Beam) */
    .card-beam-glow {
      position: absolute;
      inset: -4px;
      border-radius: 28px;
      overflow: hidden;
      filter: blur(18px);
      opacity: 0.35;
      pointer-events: none;
      z-index: 0;
    }
    .card-beam-glow::before {
      content: '';
      position: absolute;
      top: -150%;
      left: -150%;
      width: 400%;
      height: 400%;
      background: conic-gradient(
        from 0deg,
        transparent 0deg,
        transparent 70deg,
        rgba(255, 255, 255, 0.5) 120deg,
        rgba(203, 213, 225, 0.7) 160deg,
        rgba(255, 255, 255, 0.4) 200deg,
        transparent 250deg,
        transparent 360deg
      );
      animation: rotateBorderBeam 6s linear infinite;
    }

    .card-beam-border {
      position: relative;
      border-radius: 26px;
      padding: 1.5px;
      overflow: hidden;
      box-shadow: 0 35px 80px -15px rgba(0, 0, 0, 0.9),
                  0 0 25px -5px rgba(255, 255, 255, 0.08);
      z-index: 1;
      width: 100%;
      box-sizing: border-box;
      background: rgba(255, 255, 255, 0.08);
    }
    .card-beam-border::before {
      content: '';
      position: absolute;
      top: -150%;
      left: -150%;
      width: 400%;
      height: 400%;
      background: conic-gradient(
        from 0deg,
        transparent 0deg,
        transparent 70deg,
        rgba(255, 255, 255, 0.6) 120deg,
        rgba(226, 232, 240, 0.9) 155deg,
        rgba(255, 255, 255, 0.5) 190deg,
        transparent 240deg,
        transparent 360deg
      );
      animation: rotateBorderBeam 6s linear infinite;
      z-index: 0;
    }

    @keyframes rotateBorderBeam {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .asistencia-card {
      background: linear-gradient(160deg, rgba(16, 23, 38, 0.94) 0%, rgba(9, 14, 26, 0.98) 100%);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border-radius: 24px;
      padding: 38px 34px;
      position: relative;
      z-index: 1;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    .logo-wrap {
      text-align: center;
      margin-bottom: 22px;
    }

    .logo-wrap img {
      height: 48px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 4px 16px rgba(255, 255, 255, 0.1));
    }

    .session-badge-wrap {
      display: flex;
      flex-direction: column;
      gap: 8px;
      justify-content: center;
      align-items: center;
      width: 100%;
      margin-bottom: 22px;
    }

    .session-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #F8FAFC;
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      font-family: 'JetBrains Mono', monospace;
      letter-spacing: 0.03em;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
    }

    .session-badge .code-tag {
      color: #94A3B8;
      font-weight: 500;
      font-size: 11.5px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .badge-client-type {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      font-weight: 600;
      letter-spacing: 0.02em;
      padding: 4px 12px;
      border-radius: 8px;
    }
    .badge-empresa {
      background: rgba(245, 158, 11, 0.12);
      color: #FBBF24;
      border: 1px solid rgba(245, 158, 11, 0.28);
    }
    .badge-personal {
      background: rgba(148, 163, 184, 0.1);
      color: #CBD5E1;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    h1 {
      font-size: 24px;
      font-weight: 800;
      letter-spacing: -0.025em;
      color: #FFFFFF;
      margin-bottom: 8px;
      width: 100%;
    }

    .client-greeting-name {
      background: linear-gradient(135deg, #FFFFFF 30%, #CBD5E1 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    p.subtitle {
      font-size: 13.5px;
      color: var(--text-muted);
      line-height: 1.55;
      margin-bottom: 24px;
      width: 100%;
      padding: 0 4px;
    }

    .btn-launch {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: var(--btn-bg);
      color: var(--btn-text);
      text-decoration: none;
      font-size: 14.5px;
      font-weight: 700;
      padding: 15px 26px;
      border-radius: 12px;
      width: 100%;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 6px 20px -3px var(--btn-shadow);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      cursor: pointer;
      user-select: none;
    }

    .btn-launch:hover {
      background: var(--btn-hover);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px -2px rgba(255, 255, 255, 0.25);
    }

    .btn-launch:active {
      transform: translateY(0);
    }

    .btn-launch svg {
      transition: transform 0.2s ease;
    }

    .btn-launch:hover svg {
      transform: translateY(2px);
    }

    /* Steps Sub-Card */
    .steps-card {
      margin-top: 22px;
      width: 100%;
      background: rgba(8, 12, 22, 0.6);
      border-radius: 14px;
      padding: 18px;
      text-align: left;
      border: 1px solid var(--border);
    }

    .step-item {
      display: flex;
      align-items: flex-start;
      gap: 13px;
      margin-bottom: 12px;
    }

    .step-item:last-child {
      margin-bottom: 0;
    }

    .step-circle {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.16);
      color: #FFFFFF;
      font-weight: 700;
      font-size: 11.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .step-text {
      font-size: 12.8px;
      color: #CBD5E1;
      line-height: 1.45;
    }

    .step-text strong {
      color: #FFFFFF;
    }

    .security-guarantee {
      margin-top: 22px;
      font-size: 11.8px;
      color: var(--text-dim);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    .security-guarantee svg {
      color: var(--emerald);
    }
  </style>
</head>
<body>

  <!-- Watermark Background Layer with Support & System Icons -->
  <div class="watermark-bg" aria-hidden="true">
    <!-- Top-Left: Remote Screen Monitor -->
    <div class="wm-icon wm-blue anim-float-a" style="top: 8%; left: 7%; width: 90px; height: 90px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
    </div>

    <!-- Top-Left Inner: Security Shield -->
    <div class="wm-icon wm-orange anim-float-b" style="top: 26%; left: 18%; width: 70px; height: 70px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
    </div>

    <!-- Top-Center-Left: Server Rack -->
    <div class="wm-icon anim-float-c" style="top: 6%; left: 30%; width: 72px; height: 72px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
    </div>

    <!-- Top-Right: Network Nodes / Mesh -->
    <div class="wm-icon wm-orange anim-float-b" style="top: 9%; right: 9%; width: 85px; height: 85px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
    </div>

    <!-- Top-Right Inner: Terminal Window -->
    <div class="wm-icon wm-blue anim-float-a" style="top: 27%; right: 19%; width: 75px; height: 75px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line></svg>
    </div>

    <!-- Center-Left: Remote Cursor -->
    <div class="wm-icon wm-orange anim-float-c" style="top: 50%; left: 8%; width: 66px; height: 66px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path><path d="m13 13 6 6"></path></svg>
    </div>

    <!-- Center-Right: WiFi Waves & Remote Signal -->
    <div class="wm-icon wm-blue anim-float-a" style="top: 50%; right: 8%; width: 78px; height: 78px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
    </div>

    <!-- Bottom-Left Inner: Chip / CPU -->
    <div class="wm-icon anim-float-b" style="bottom: 23%; left: 16%; width: 72px; height: 72px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
    </div>

    <!-- Bottom-Left Outer: Remote Desktop Screen -->
    <div class="wm-icon wm-orange anim-float-a" style="bottom: 7%; left: 6%; width: 92px; height: 92px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line><path d="M7 8h10M7 12h5"></path></svg>
    </div>

    <!-- Bottom-Center-Left: IT Wrench -->
    <div class="wm-icon wm-blue anim-float-c" style="bottom: 8%; left: 28%; width: 65px; height: 65px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
    </div>

    <!-- Bottom-Right Inner: Secure Lock -->
    <div class="wm-icon wm-orange anim-float-a" style="bottom: 23%; right: 17%; width: 70px; height: 70px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
    </div>

    <!-- Bottom-Right Outer: Server Database -->
    <div class="wm-icon wm-blue anim-float-b" style="bottom: 7%; right: 7%; width: 88px; height: 88px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
    </div>

    <!-- Bottom-Center-Right: Fast Lightning Bolt -->
    <div class="wm-icon anim-float-c" style="bottom: 8%; right: 29%; width: 62px; height: 62px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
    </div>
  </div>

  <div class="asistencia-container">
    <!-- Glowing Ambient Shadow Layer -->
    <div class="card-beam-glow"></div>

    <!-- Animated Moving Beam Border -->
    <div class="card-beam-border">
      <div class="asistencia-card">
        
        <!-- Logo -->
        <div class="logo-wrap">
          <img src="flutter/assets/logo_dark.png?v=2" alt="OptimaIT">
        </div>

        <!-- Session & Profile Badges -->
        <div class="session-badge-wrap">
          <div class="session-badge">
            <span class="code-tag">SESIÓN</span>
            <span><?php echo htmlspecialchars($sessionId); ?></span>
          </div>
          <div>
            <?php if ($clientType === 'empresa'): ?>
              <span class="badge-client-type badge-empresa">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M10 11h2M10 15h2M14 11h2M14 15h2M18 11h2M18 15h2M9 21V3h6v18"></path></svg>
                Perfil Empresarial (Servicio Permanente)
              </span>
            <?php else: ?>
              <span class="badge-client-type badge-personal">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Asistencia Rápida (Soporte Directo)
              </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Greeting -->
        <h1>Hola, <span class="client-greeting-name"><?php echo htmlspecialchars($clientName); ?></span></h1>
        <p class="subtitle">
          <?php if ($clientType === 'empresa'): ?>
            Su especialista de <strong>OptimaIT</strong> preparó el acceso desatendido para su equipo. Descargue y autorice la ejecución para configurar el servicio permanente.
          <?php else: ?>
            Su especialista de <strong>OptimaIT</strong> está esperando para conectarse. Descargue y abra el asistente para iniciar.
          <?php endif; ?>
        </p>

        <!-- Download & Launch Button -->
        <a href="asistencia.php?sesion=<?php echo urlencode($sessionId); ?>&descargar=1" class="btn-launch" id="btnDownload">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
          <?php echo ($clientType === 'empresa') ? 'Descargar e Instalar Acceso Remoto' : 'Iniciar Asistencia Remota'; ?>
        </a>

        <!-- Instruction Steps -->
        <div class="steps-card">
          <div class="step-item">
            <div class="step-circle">1</div>
            <div class="step-text">Haga clic en el botón superior para descargar el archivo de conexión.</div>
          </div>
          <div class="step-item">
            <div class="step-circle">2</div>
            <div class="step-text">
              <?php if ($clientType === 'empresa'): ?>
                Abra el archivo descargado y en la ventana de Windows seleccione <strong>Sí (Permitir)</strong> para registrar el servicio.
              <?php else: ?>
                Abra el archivo descargado (es instantáneo y seguro, no requiere instalación compleja).
              <?php endif; ?>
            </div>
          </div>
          <div class="step-item">
            <div class="step-circle">3</div>
            <div class="step-text">
              <?php if ($clientType === 'empresa'): ?>
                ¡Listo! El equipo quedará registrado de forma desatendida y se iniciará con Windows.
              <?php else: ?>
                Cuando el técnico se conecte, simplemente pulse <strong>Aceptar</strong> en su pantalla.
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Security Guarantee -->
        <div class="security-guarantee">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          <span>Conexión cifrada punto a punto en servidor dedicado OptimaIT</span>
        </div>

      </div>
    </div>
  </div>

</body>
</html>
