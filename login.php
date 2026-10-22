<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['novadesk_user'])) {
    header('Location: tecnico.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión - OptimaIT</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #3874CC;
      --primary-hover: #295FB3;
      --primary-glow: rgba(56, 116, 204, 0.4);
      --orange: #E77222;
      --orange-hover: #D06319;
      --orange-glow: rgba(231, 114, 34, 0.45);
      --bg: #090D16;
      --surface: rgba(15, 23, 42, 0.85);
      --border: rgba(255, 255, 255, 0.08);
      --text: #F8FAFC;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
    }

    body {
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow-x: hidden;
    }

    /* Ambient Lighting Mesh */
    body::before {
      content: '';
      position: fixed;
      top: -20%;
      left: 15%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(231, 114, 34, 0.12) 0%, transparent 70%);
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
      background: radial-gradient(circle, rgba(56, 116, 204, 0.15) 0%, transparent 70%);
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
      opacity: 0.045;
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
    .wm-orange {
      opacity: 0.055;
      color: var(--orange);
    }
    .wm-blue {
      opacity: 0.06;
      color: var(--primary);
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

    .login-container {
      width: 100%;
      max-width: 440px;
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
      opacity: 0.65;
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
        transparent 65deg,
        rgba(231, 114, 34, 0.9) 110deg,
        rgba(56, 116, 204, 0.95) 160deg,
        rgba(231, 114, 34, 0.8) 200deg,
        transparent 250deg,
        transparent 360deg
      );
      animation: rotateBorderBeam 5s linear infinite;
    }

    .card-beam-border {
      position: relative;
      border-radius: 26px;
      padding: 1.8px;
      overflow: hidden;
      box-shadow: 0 35px 80px -15px rgba(0, 0, 0, 0.9),
                  0 0 35px -5px rgba(231, 114, 34, 0.15);
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
        transparent 65deg,
        #E77222 110deg,
        #3874CC 155deg,
        #FFA24C 195deg,
        transparent 245deg,
        transparent 360deg
      );
      animation: rotateBorderBeam 5s linear infinite;
      z-index: 0;
    }

    @keyframes rotateBorderBeam {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .login-card {
      background: linear-gradient(160deg, rgba(16, 23, 38, 0.94) 0%, rgba(9, 14, 26, 0.98) 100%);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border-radius: 24px;
      padding: 40px 36px;
      position: relative;
      z-index: 1;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .logo-wrap {
      text-align: center;
      margin-bottom: 24px;
    }

    .logo-wrap img {
      height: 48px;
      filter: drop-shadow(0 4px 15px rgba(231, 114, 34, 0.35));
    }

    .login-header {
      text-align: center;
      margin-bottom: 28px;
    }

    .login-header h1 {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #FFFFFF;
      margin-bottom: 6px;
    }

    .login-header p {
      font-size: 13px;
      color: var(--text-muted);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: #CBD5E1;
      margin-bottom: 8px;
      letter-spacing: 0.01em;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      color: #64748B;
      pointer-events: none;
      transition: color 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .input-field {
      width: 100%;
      background: rgba(8, 12, 22, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.09);
      border-radius: 12px;
      padding: 13px 44px 13px 42px;
      font-size: 14px;
      color: #FFFFFF;
      outline: none;
      transition: all 0.25s ease;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .input-field:focus {
      background: rgba(8, 12, 22, 0.9);
      border-color: var(--orange);
      box-shadow: 0 0 0 3px var(--orange-glow), inset 0 2px 4px rgba(0, 0, 0, 0.4);
    }

    .input-field:focus + .input-icon,
    .input-wrapper:focus-within .input-icon {
      color: var(--orange);
    }

    .toggle-pwd {
      position: absolute;
      right: 12px;
      background: transparent;
      border: none;
      color: #64748B;
      cursor: pointer;
      padding: 6px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .toggle-pwd:hover {
      color: #CBD5E1;
      background: rgba(255, 255, 255, 0.05);
    }

    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, var(--orange) 0%, #D06319 100%);
      color: #FFFFFF;
      border: none;
      padding: 14px 20px;
      border-radius: 12px;
      font-size: 14.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 6px 20px -3px var(--orange-glow);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 8px;
      user-select: none;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px -2px var(--orange-glow);
      filter: brightness(1.06);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .btn-login:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .error-alert {
      display: none;
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #FCA5A5;
      padding: 11px 14px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      align-items: center;
      gap: 9px;
      animation: shake 0.35s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-6px); }
      40%, 80% { transform: translateX(6px); }
    }

    .footer-note {
      text-align: center;
      margin-top: 24px;
      font-size: 12px;
      color: var(--text-dim);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
  </style>
</head>
<body>

  <!-- Watermark Background Layer with System & Remote Support Icons -->
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

  <div class="login-container">
    <!-- Glowing Ambient Shadow Layer -->
    <div class="card-beam-glow"></div>
    
    <!-- Animated Moving Beam Border -->
    <div class="card-beam-border">
      <div class="login-card">
        <div class="logo-wrap">
          <img src="flutter/assets/logo_dark.png?v=2" alt="OptimaIT Logo">
        </div>

        <div class="login-header">
          <h1>Centro de Control</h1>
          <p>Inicia sesión para gestionar el soporte técnico remoto</p>
        </div>

        <div id="errorAlert" class="error-alert">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <span id="errorMessage">Credenciales incorrectas</span>
        </div>

        <form id="loginForm" onsubmit="handleLogin(event)">
          <div class="form-group">
            <label class="form-label" for="usernameInput">Usuario</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </span>
              <input type="text" id="usernameInput" class="input-field" placeholder="Ingresa tu usuario" required autocomplete="username" autofocus>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="passwordInput">Contraseña</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </span>
              <input type="password" id="passwordInput" class="input-field" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
              <button type="button" class="toggle-pwd" onclick="togglePasswordVisibility()" title="Mostrar/ocultar contraseña">
                <svg id="eyeIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-login" id="btnLogin">
            <span id="btnText">Ingresar al Panel</span>
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m-7-7l7 7-7 7"></path></svg>
          </button>
        </form>

        <div class="footer-note">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          <span>Acceso restringido y cifrado para soporte técnico</span>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePasswordVisibility() {
      const pwd = document.getElementById('passwordInput');
      const icon = document.getElementById('eyeIcon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
      } else {
        pwd.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
      }
    }

    async function handleLogin(e) {
      e.preventDefault();
      const u = document.getElementById('usernameInput').value.trim();
      const p = document.getElementById('passwordInput').value;
      const btn = document.getElementById('btnLogin');
      const btnText = document.getElementById('btnText');
      const errorBox = document.getElementById('errorAlert');
      const errorMsg = document.getElementById('errorMessage');

      errorBox.style.display = 'none';
      btn.disabled = true;
      btnText.innerText = "Verificando...";

      try {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', u);
        formData.append('password', p);

        const res = await fetch('api.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          btnText.innerText = "¡Acceso concedido!";
          btn.style.background = 'linear-gradient(135deg, #10B981 0%, #059669 100%)';
          setTimeout(() => {
            window.location.href = 'tecnico.php';
          }, 350);
        } else {
          errorMsg.innerText = data.error || "Credenciales incorrectas";
          errorBox.style.display = 'flex';
          btn.disabled = false;
          btnText.innerText = "Ingresar al Panel";
          document.getElementById('passwordInput').value = '';
          document.getElementById('passwordInput').focus();
        }
      } catch (err) {
        errorMsg.innerText = "Error de conexión con el servidor";
        errorBox.style.display = 'flex';
        btn.disabled = false;
        btnText.innerText = "Ingresar al Panel";
      }
    }
  </script>
</body>
</html>
