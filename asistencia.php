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
    $serverHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '178.156.246.205';
    if ($serverHost === 'localhost' || $serverHost === '127.0.0.1') {
        $serverHost = '178.156.246.205';
    }
    $downloadName = "{$prefix}_{$sessionId},host={$serverHost},key=wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU=.exe";
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #3874CC;
      --primary-glow: rgba(56, 116, 204, 0.35);
      --orange: #E77222;
      --orange-hover: #D06319;
      --orange-glow: rgba(231, 114, 34, 0.45);
      --bg: #090D16;
      --card-bg: rgba(17, 24, 39, 0.88);
      --border: rgba(255, 255, 255, 0.08);
      --text: #F8FAFC;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
      --green: #10B981;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    body {
      background: radial-gradient(circle at 50% 15%, rgba(56, 116, 204, 0.22) 0%, transparent 55%),
                  radial-gradient(circle at 80% 85%, rgba(231, 114, 34, 0.12) 0%, transparent 50%),
                  var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
      position: relative;
    }

    .glow-sphere {
      position: absolute;
      top: 15%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 580px;
      height: 400px;
      background: radial-gradient(circle, rgba(56, 116, 204, 0.28) 0%, rgba(231, 114, 34, 0.18) 50%, transparent 70%);
      filter: blur(100px);
      pointer-events: none;
      z-index: 0;
    }

    .client-card {
      position: relative;
      z-index: 1;
      background: var(--card-bg);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 26px;
      padding: 42px 36px;
      max-width: 500px;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.8),
                  0 0 0 1px rgba(255, 255, 255, 0.05),
                  0 0 40px rgba(56, 116, 204, 0.1);
      animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardAppear {
      from { opacity: 0; transform: translateY(20px) scale(0.97); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .brand-logo-wrap {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      margin: 0 auto 24px auto;
    }

    .brand-logo-wrap img {
      height: 125px;
      width: auto;
      max-width: 250px;
      object-fit: contain;
      filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.45));
      display: block;
      margin: 0 auto;
    }

    .session-badge-wrap {
      display: flex;
      flex-direction: column;
      gap: 6px;
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
      background: rgba(56, 116, 204, 0.12);
      border: 1px solid rgba(56, 116, 204, 0.35);
      color: #60A5FA;
      padding: 7px 18px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      font-family: 'JetBrains Mono', monospace;
      letter-spacing: 0.04em;
    }

    .badge-client-type {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 3px 10px;
      border-radius: 6px;
    }
    .badge-empresa {
      background: rgba(231, 114, 34, 0.2);
      color: #FB923C;
      border: 1px solid rgba(231, 114, 34, 0.4);
    }
    .badge-personal {
      background: rgba(56, 116, 204, 0.2);
      color: #93C5FD;
      border: 1px solid rgba(56, 116, 204, 0.4);
    }

    h1 {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.025em;
      color: #FFFFFF;
      margin-bottom: 10px;
      width: 100%;
    }

    .client-greeting-name {
      background: linear-gradient(135deg, #FFFFFF 20%, #94A3B8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    p.subtitle {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 26px;
      width: 100%;
      padding: 0 6px;
    }

    .btn-launch {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      text-decoration: none;
      font-size: 16px;
      font-weight: 700;
      padding: 16px 32px;
      border-radius: 14px;
      width: 100%;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 10px 25px -4px var(--orange-glow);
      border: none;
      cursor: pointer;
    }

    .btn-launch:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 32px -4px var(--orange-glow);
      filter: brightness(1.06);
    }

    .steps-card {
      margin-top: 24px;
      width: 100%;
      background: rgba(11, 15, 25, 0.65);
      border-radius: 16px;
      padding: 18px 20px;
      text-align: left;
      border: 1px solid var(--border);
    }

    .step-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      margin-bottom: 12px;
    }

    .step-item:last-child {
      margin-bottom: 0;
    }

    .step-circle {
      width: 24px;
      height: 24px;
      border-radius: 8px;
      background: rgba(56, 116, 204, 0.2);
      border: 1px solid rgba(56, 116, 204, 0.4);
      color: #60A5FA;
      font-weight: 800;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .step-text {
      font-size: 13px;
      color: #CBD5E1;
      line-height: 1.45;
    }

    .status-container {
      margin-top: 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
      padding: 9px 20px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 999px;
      border: 1px solid var(--border);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #F59E0B;
      box-shadow: 0 0 10px #F59E0B;
      animation: pulseWarning 2s infinite;
    }

    .status-dot.active-online {
      background: #10B981;
      box-shadow: 0 0 10px #10B981;
      animation: pulseGreen 2s infinite;
    }

    @keyframes pulseWarning {
      0% { transform: scale(0.95); opacity: 0.6; }
      50% { transform: scale(1.2); opacity: 1; }
      100% { transform: scale(0.95); opacity: 0.6; }
    }

    @keyframes pulseGreen {
      0% { transform: scale(0.95); opacity: 0.7; }
      50% { transform: scale(1.2); opacity: 1; }
      100% { transform: scale(0.95); opacity: 0.7; }
    }

    .security-guarantee {
      margin-top: 20px;
      font-size: 12px;
      color: var(--text-dim);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    .security-guarantee svg {
      color: #10B981;
    }
  </style>
</head>
<body>

  <div class="glow-sphere"></div>

  <div class="client-card">
    
    <div class="brand-logo-wrap">
      <img src="flutter/assets/logo_vertical_dark.png?v=4" alt="OptimaIT">
    </div>

    <div class="session-badge-wrap">
      <div class="session-badge">
        <span>Código: <?php echo htmlspecialchars($sessionId); ?></span>
      </div>
      <div>
        <?php if ($clientType === 'empresa'): ?>
          <span class="badge-client-type badge-empresa">🏢 Perfil Empresarial (Instalación & Elevación)</span>
        <?php else: ?>
          <span class="badge-client-type badge-personal">👤 Asistencia Rápida (Persona Natural)</span>
        <?php endif; ?>
      </div>
    </div>

    <h1>Hola, <span class="client-greeting-name"><?php echo htmlspecialchars($clientName); ?></span></h1>
    <p class="subtitle">
      <?php if ($clientType === 'empresa'): ?>
        Su especialista de <strong>OptimaIT</strong> preparó el acceso desatendido para su empresa. Descargue y autorice la ejecución para configurar el servicio permanente.
      <?php else: ?>
        Su asesor de <strong>OptimaIT</strong> está esperando para conectarse. Descargue y abra el asistente para comenzar.
      <?php endif; ?>
    </p>

    <!-- Download & Launch with tailored name -->
    <a href="asistencia.php?sesion=<?php echo urlencode($sessionId); ?>&descargar=1" class="btn-launch" id="btnDownload">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
      <?php echo ($clientType === 'empresa') ? 'Descargar e Instalar Acceso Remoto' : 'Iniciar Asistencia Remota'; ?>
    </a>

    <!-- Instruction Steps -->
    <div class="steps-card">
      <div class="step-item">
        <div class="step-circle">1</div>
        <div class="step-text">Haga clic en el botón naranja superior para descargar el archivo.</div>
      </div>
      <div class="step-item">
        <div class="step-circle">2</div>
        <div class="step-text">
          <?php if ($clientType === 'empresa'): ?>
            Abra el archivo descargado y en la ventana de Windows haga clic en <strong>Sí (Permitir)</strong> para elevar e instalar el servicio.
          <?php else: ?>
            Abra el archivo descargado (es instantáneo, no requiere instalación).
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

    <!-- Live Status -->
    <div class="status-container">
      <div class="status-dot" id="liveDot"></div>
      <span id="liveText">Esperando apertura del asistente...</span>
    </div>

    <div class="security-guarantee">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
      <span>Conexión cifrada punto a punto en servidor dedicado OptimaIT</span>
    </div>

  </div>

  <script>
    const sessionId = "<?php echo addslashes($sessionId); ?>";

    async function checkStatus() {
      try {
        const res = await fetch(`api.php?action=list_sessions`);
        const data = await res.json();
        if (data.success) {
          const current = (data.sessions || []).find(s => s.session_id === sessionId);
          if (current) {
            if (current.status === 'online') {
              const dot = document.getElementById('liveDot');
              const txt = document.getElementById('liveText');
              dot.className = 'status-dot active-online';
              txt.innerText = '¡En línea con el técnico! Su sesión está activa.';
              txt.style.color = '#34D399';
            } else if (current.status === 'closed') {
              const dot = document.getElementById('liveDot');
              const txt = document.getElementById('liveText');
              dot.style.background = '#94A3B8';
              txt.innerText = 'Sesión finalizada. Gracias por utilizar OptimaIT.';
            }
          }
        }
      } catch (e) { }
    }

    setInterval(checkStatus, 2500);
  </script>
</body>
</html>
