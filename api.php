<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$dataFile = __DIR__ . '/data/sessions.json';
$groupsFile = __DIR__ . '/data/groups.json';
$usersFile = __DIR__ . '/data/users.json';

function getUsers() {
    global $usersFile;
    if (!file_exists($usersFile)) {
        $defaultUsers = [
            'admin' => [
                'username' => 'admin',
                'name' => 'Administrador',
                'password' => password_hash('$0porT3..*', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        saveUsers($defaultUsers);
        return $defaultUsers;
    }
    $data = json_decode(file_get_contents($usersFile), true);
    if (!is_array($data) || empty($data)) {
        $defaultUsers = [
            'admin' => [
                'username' => 'admin',
                'name' => 'Administrador',
                'password' => password_hash('$0porT3..*', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        saveUsers($defaultUsers);
        return $defaultUsers;
    }
    return $data;
}

function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function getSessions() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        return [];
    }
    $content = file_get_contents($dataFile);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function saveSessions($sessions) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function getCustomGroups() {
    global $groupsFile;
    if (!file_exists($groupsFile)) {
        return ['Oficina Central', 'Sucursales', 'Soporte VIP'];
    }
    $data = json_decode(file_get_contents($groupsFile), true);
    return is_array($data) ? $data : ['Oficina Central', 'Sucursales', 'Soporte VIP'];
}

function saveCustomGroups($groups) {
    global $groupsFile;
    file_put_contents($groupsFile, json_encode(array_values(array_unique($groups)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create_session':
        $clientName = trim($_REQUEST['client_name'] ?? 'Cliente');
        if (empty($clientName)) {
            $clientName = 'Cliente ' . rand(100, 999);
        }
        $clientType = trim($_REQUEST['client_type'] ?? 'personal'); // 'personal' o 'empresa'
        if ($clientType !== 'empresa') {
            $clientType = 'personal';
        }
        
        $groupName = trim($_REQUEST['group_name'] ?? '');
        if (empty($groupName)) {
            $groupName = ($clientType === 'empresa') ? 'Empresas' : 'Personas Naturales';
        }

        $sessionId = 'OPT-' . rand(1000, 9999);
        $password = 'Optima' . rand(1000, 9999);
        
        $sessions = getSessions();
        $sessions[$sessionId] = [
            'session_id' => $sessionId,
            'client_name' => $clientName,
            'client_type' => $clientType, // 'personal' o 'empresa'
            'group_name' => $groupName,
            'password' => $password,
            'status' => 'waiting', // waiting, online, closed
            'rustdesk_id' => '',
            'created_at' => date('H:i:s'),
            'last_seen' => time(),
            'installed' => false
        ];
        saveSessions($sessions);
        
        $host = $_SERVER['HTTP_HOST'] ?? '155.133.197.176';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/OptimaIT/api.php');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        $basePath = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $joinUrl = "{$proto}://{$host}{$basePath}/asistencia.php?sesion={$sessionId}";

        echo json_encode([
            'success' => true,
            'session' => $sessions[$sessionId],
            'join_url' => $joinUrl
        ]);
        break;

    case 'list_sessions':
        $sessions = getSessions();
        $now = time();
        $cleaned = [];
        $hasChanges = false;

        // Auto-detect active RustDesk client connections to hbbs (port 21116)
        $establishedIps = [];
        $netOut = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @exec('netstat -ano -p tcp | findstr 21116 | findstr ESTABLISHED', $netOut);
        } else {
            @exec('ss -nt 2>/dev/null | grep :21116 | grep ESTAB || netstat -tn 2>/dev/null | grep :21116 | grep ESTABLISHED', $netOut);
        }
        foreach ($netOut as $nLine) {
            if (preg_match('/:\d+\s+([0-9.]+):\d+\s+ESTAB/', $nLine, $m) || preg_match('/:\d+\s+([0-9.]+):\d+\s+ESTABLISHED/', $nLine, $m)) {
                $establishedIps[$m[1]] = true;
            }
        }

        foreach ($sessions as $id => $s) {
            $lastActivity = $s['last_seen'] ?? 0;
            if ($now - $lastActivity < 86400 * 30) { // Retain 30 days for inventory
                $clientIp = $s['client_ip'] ?? '';

                // If remote machine has an active socket to hbbs, it is definitely online!
                $isSocketConnected = !empty($clientIp) && isset($establishedIps[$clientIp]);
                
                // If there's only 1 session with an ID and 1 active IP, automatically link it
                if (!$isSocketConnected && empty($clientIp) && count($establishedIps) === 1 && !empty($s['rustdesk_id'])) {
                    $singleIp = array_keys($establishedIps)[0];
                    $s['client_ip'] = $singleIp;
                    $clientIp = $singleIp;
                    $isSocketConnected = true;
                    $hasChanges = true;
                }

                if ($isSocketConnected) {
                    if ($s['status'] !== 'online') {
                        $s['status'] = 'online';
                        $hasChanges = true;
                    }
                    $s['last_seen'] = $now;
                } else if ($s['status'] === 'online' && ($now - $lastActivity > 90)) {
                    // Si el equipo no tiene socket activo y no ha reportado heartbeat en más de 90s, se marca inactivo
                    $s['status'] = 'idle';
                    $hasChanges = true;
                }
                // Backwards compatibility defaults
                if (empty($s['client_type'])) {
                    $s['client_type'] = 'personal';
                }
                if (empty($s['group_name'])) {
                    $s['group_name'] = ($s['client_type'] === 'empresa') ? 'General' : 'Personas Naturales';
                }
                $cleaned[$id] = $s;
            } else {
                $hasChanges = true;
            }
        }
        if ($hasChanges || count($cleaned) !== count($sessions)) {
            saveSessions($cleaned);
        }
        $list = array_values($cleaned);
        usort($list, function($a, $b) {
            return ($b['last_seen'] ?? 0) <=> ($a['last_seen'] ?? 0);
        });
        echo json_encode([
            'success' => true, 
            'sessions' => $list,
            'groups' => getCustomGroups()
        ]);
        break;

    case 'add_group':
        $name = trim($_REQUEST['group_name'] ?? '');
        if (!empty($name)) {
            $groups = getCustomGroups();
            $groups[] = $name;
            saveCustomGroups($groups);
            echo json_encode(['success' => true, 'groups' => getCustomGroups()]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Nombre de grupo vacío']);
        }
        break;

    case 'register_client':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $rustdeskId = trim($_REQUEST['rustdesk_id'] ?? '');
        $installed = isset($_REQUEST['installed']) && $_REQUEST['installed'] === '1';
        
        if (empty($sessionId) || empty($rustdeskId)) {
            echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
            exit;
        }

        // Parse sysinfo if provided
        $sysinfo = null;
        $rawPost = file_get_contents('php://input');
        if (!empty($_POST['sysinfo'])) {
            $sysinfo = is_array($_POST['sysinfo']) ? $_POST['sysinfo'] : json_decode($_POST['sysinfo'], true);
        } else if (!empty($rawPost)) {
            $parsed = json_decode($rawPost, true);
            if (!empty($parsed['sysinfo'])) {
                $sysinfo = $parsed['sysinfo'];
            }
        }

        $sessions = getSessions();
        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['rustdesk_id'] = $rustdeskId;
            $sessions[$sessionId]['status'] = 'online';
            $sessions[$sessionId]['last_seen'] = time();
            if ($installed) {
                $sessions[$sessionId]['installed'] = true;
            }
            $sessions[$sessionId]['client_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($sysinfo) {
                $sessions[$sessionId]['sysinfo'] = $sysinfo;
                if (!empty($sysinfo['ram']) || !empty($sysinfo['disks'])) {
                    $sessions[$sessionId]['metrics'] = [
                        'ram_used_pct' => $sysinfo['ram']['used_pct'] ?? 0,
                        'ram_used_gb' => $sysinfo['ram']['used_gb'] ?? 0,
                        'ram_total_gb' => $sysinfo['ram']['total_gb'] ?? 0,
                        'disk_used_pct' => $sysinfo['disks'][0]['used_pct'] ?? 0,
                        'disk_free_gb' => $sysinfo['disks'][0]['free_gb'] ?? 0,
                        'disk_total_gb' => $sysinfo['disks'][0]['total_gb'] ?? 0,
                        'updated_at' => date('H:i:s')
                    ];
                }
            }
            saveSessions($sessions);
            echo json_encode([
                'success' => true, 
                'message' => 'Cliente registrado con éxito',
                'password' => $sessions[$sessionId]['password'],
                'client_type' => $sessions[$sessionId]['client_type'] ?? 'personal'
            ]);
        } else {
            $password = 'Optima' . rand(1000, 9999);
            $sessions[$sessionId] = [
                'session_id' => $sessionId,
                'client_name' => $sysinfo['hostname'] ?? 'Cliente Directo',
                'client_type' => 'personal',
                'group_name' => 'Personas Naturales',
                'password' => $password,
                'status' => 'online',
                'rustdesk_id' => $rustdeskId,
                'created_at' => date('H:i:s'),
                'last_seen' => time(),
                'installed' => $installed,
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ];
            if ($sysinfo) {
                $sessions[$sessionId]['sysinfo'] = $sysinfo;
                if (!empty($sysinfo['ram']) || !empty($sysinfo['disks'])) {
                    $sessions[$sessionId]['metrics'] = [
                        'ram_used_pct' => $sysinfo['ram']['used_pct'] ?? 0,
                        'ram_used_gb' => $sysinfo['ram']['used_gb'] ?? 0,
                        'ram_total_gb' => $sysinfo['ram']['total_gb'] ?? 0,
                        'disk_used_pct' => $sysinfo['disks'][0]['used_pct'] ?? 0,
                        'disk_free_gb' => $sysinfo['disks'][0]['free_gb'] ?? 0,
                        'disk_total_gb' => $sysinfo['disks'][0]['total_gb'] ?? 0,
                        'updated_at' => date('H:i:s')
                    ];
                }
            }
            saveSessions($sessions);
            echo json_encode([
                'success' => true, 
                'message' => 'Sesión creada y registrada',
                'password' => $password,
                'client_type' => 'personal'
            ]);
        }
        break;

    case 'heartbeat':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $sessions = getSessions();
        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['last_seen'] = time();
            $sessions[$sessionId]['status'] = 'online';

            // Optional live metrics update and sysinfo
            $rawPost = file_get_contents('php://input');
            $metricsData = null;
            $sysinfoData = null;
            if (!empty($_POST['metrics'])) {
                $metricsData = is_array($_POST['metrics']) ? $_POST['metrics'] : json_decode($_POST['metrics'], true);
            }
            if (!empty($_POST['sysinfo'])) {
                $sysinfoData = is_array($_POST['sysinfo']) ? $_POST['sysinfo'] : json_decode($_POST['sysinfo'], true);
            }
            if (!empty($rawPost)) {
                $parsed = json_decode($rawPost, true);
                if (!empty($parsed['metrics'])) {
                    $metricsData = $parsed['metrics'];
                }
                if (!empty($parsed['sysinfo'])) {
                    $sysinfoData = $parsed['sysinfo'];
                }
            }
            if ($metricsData) {
                $metricsData['updated_at'] = date('H:i:s');
                $sessions[$sessionId]['metrics'] = $metricsData;
            }
            if ($sysinfoData) {
                $sessions[$sessionId]['sysinfo'] = $sysinfoData;
            }

            saveSessions($sessions);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'save_sysinfo':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $rustId = trim($_REQUEST['rustdesk_id'] ?? ($_REQUEST['id'] ?? ''));
        $sessions = getSessions();
        $targetKey = null;
        if (!empty($sessionId) && isset($sessions[$sessionId])) {
            $targetKey = $sessionId;
        } else if (!empty($rustId)) {
            foreach ($sessions as $k => $v) {
                if (($v['rustdesk_id'] ?? '') === $rustId) {
                    $targetKey = $k;
                    break;
                }
            }
        }

        if (!empty($targetKey) && isset($sessions[$targetKey])) {
            $rawPost = file_get_contents('php://input');
            $data = json_decode($rawPost, true) ?: $_POST;
            if (!empty($data['sysinfo'])) {
                $sysinfo = is_array($data['sysinfo']) ? $data['sysinfo'] : json_decode($data['sysinfo'], true);
                $sessions[$targetKey]['sysinfo'] = $sysinfo;
                if (!empty($sysinfo['ram']) || !empty($sysinfo['disks'])) {
                    $sessions[$targetKey]['metrics'] = [
                        'ram_used_pct' => $sysinfo['ram']['used_pct'] ?? 0,
                        'ram_used_gb' => $sysinfo['ram']['used_gb'] ?? 0,
                        'ram_total_gb' => $sysinfo['ram']['total_gb'] ?? 0,
                        'disk_used_pct' => $sysinfo['disks'][0]['used_pct'] ?? 0,
                        'disk_free_gb' => $sysinfo['disks'][0]['free_gb'] ?? 0,
                        'disk_total_gb' => $sysinfo['disks'][0]['total_gb'] ?? 0,
                        'updated_at' => date('H:i:s')
                    ];
                }
            }
            if (!empty($data['metrics'])) {
                $metrics = is_array($data['metrics']) ? $data['metrics'] : json_decode($data['metrics'], true);
                $metrics['updated_at'] = date('H:i:s');
                $sessions[$targetKey]['metrics'] = $metrics;
            }
            $sessions[$targetKey]['last_seen'] = time();
            $sessions[$targetKey]['status'] = 'online';
            saveSessions($sessions);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
        }
        break;

    case 'mark_online':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $id = preg_replace('/[^0-9]/', '', $_REQUEST['id'] ?? '');
        $sessions = getSessions();
        $updated = false;
        if (!empty($sessionId) && isset($sessions[$sessionId])) {
            $sessions[$sessionId]['last_seen'] = time();
            $sessions[$sessionId]['status'] = 'online';
            $updated = true;
        }
        if (!empty($id)) {
            foreach ($sessions as $sK => &$sVal) {
                if (($sVal['rustdesk_id'] ?? '') === $id) {
                    $sVal['status'] = 'online';
                    $sVal['last_seen'] = time();
                    $updated = true;
                }
            }
        }
        if ($updated) {
            saveSessions($sessions);
        }
        echo json_encode(['success' => true, 'updated' => $updated]);
        break;

    case 'close_session':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $deletePermanent = isset($_REQUEST['delete']) && $_REQUEST['delete'] === '1';
        $sessions = getSessions();
        if (isset($sessions[$sessionId])) {
            if ($deletePermanent) {
                unset($sessions[$sessionId]);
            } else {
                $sessions[$sessionId]['status'] = 'closed';
                $sessions[$sessionId]['closed_at'] = date('H:i:s');
            }
            saveSessions($sessions);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'reopen_session':
        $sessionId = trim($_REQUEST['session_id'] ?? '');
        $sessions = getSessions();
        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['status'] = !empty($sessions[$sessionId]['rustdesk_id']) ? 'online' : 'waiting';
            $sessions[$sessionId]['last_seen'] = time();
            saveSessions($sessions);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'download_tech_client':
        $file = __DIR__ . '/OptimaIT.exe';
        if (!file_exists($file)) {
            header("HTTP/1.0 404 Not Found");
            echo "Error: El archivo de consola técnica no está disponible en el servidor.";
            exit;
        }
        $serverHost = !empty($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : 'optimait.155.133.197.176.sslip.io';
        if ($serverHost === 'localhost' || $serverHost === '127.0.0.1' || $serverHost === '155.133.197.176') {
            $serverHost = 'optimait.155.133.197.176.sslip.io';
        }
        $downloadName = "OptimaIT_Instalador_Tecnico.exe";
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;

    case 'download_config_bat':
        $bat = "@echo off\r\n";
        $bat .= "chcp 65001 >nul\r\n";
        $bat .= "echo ========================================================\r\n";
        $bat .= "echo       CONFIGURANDO CONSOLA DE TECNICO - OPTIMA IT\r\n";
        $bat .= "echo ========================================================\r\n\r\n";
        $bat .= "set CFG_DIR=%APPDATA%\\RustDesk\\config\r\n";
        $bat .= "if not exist \"%CFG_DIR%\" mkdir \"%CFG_DIR%\"\r\n\r\n";
        $bat .= "set TOML_FILE=%CFG_DIR%\\RustDesk2.toml\r\n";
        $bat .= "set TOML_FILE_LEG=%CFG_DIR%\\RustDesk.toml\r\n\r\n";
        $bat .= "(\r\n";
        $bat .= "echo rendezvous_server = '155.133.197.176:21116'\r\n";
        $bat .= "echo relay_server = '155.133.197.176:21117'\r\n";
        $bat .= "echo nat_type = 1\r\n";
        $bat .= "echo serial = 999999999\r\n";
        $bat .= "echo.\r\n";
        $bat .= "echo [options]\r\n";
        $bat .= "echo approve-mode = 'click'\r\n";
        $bat .= "echo verification-method = 'both'\r\n";
        $bat .= "echo custom-rendezvous-server = '155.133.197.176:21116'\r\n";
        $bat .= "echo rendezvous-servers = '155.133.197.176:21116'\r\n";
        $bat .= "echo relay-server = '155.133.197.176:21117'\r\n";
        $bat .= "echo key = 'wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU='\r\n";
        $bat .= ") > \"%TOML_FILE%\"\r\n\r\n";
        $bat .= "copy /y \"%TOML_FILE%\" \"%TOML_FILE_LEG%\" >nul 2>&1\r\n\r\n";
        $bat .= "reg add \"HKCU\\Software\\Classes\\rustdesk\" /ve /d \"OptimaIT Soporte Remoto\" /f >nul 2>&1\r\n";
        $bat .= "reg add \"HKCU\\Software\\Classes\\rustdesk\" /v \"URL Protocol\" /t REG_SZ /d \"\" /f >nul 2>&1\r\n\r\n";
        $bat .= "echo [OK] Servidor Privado configurado: 155.133.197.176\r\n";
        $bat .= "echo [OK] Clave de seguridad enlazada.\r\n";
        $bat .= "echo [OK] Nombre en Windows actualizado a: OptimaIT Soporte Remoto.\r\n\r\n";
        $bat .= "echo ========================================================\r\n";
        $bat .= "echo  Listo! Ahora puedes conectarte a cualquier equipo con 1 clic.\r\n";
        $bat .= "echo ========================================================\r\n";
        $bat .= "timeout /t 5\r\n";

        header('Content-Description: File Transfer');
        header('Content-Type: application/bat');
        header('Content-Disposition: attachment; filename="Configurar_OptimaIT_Tecnico.bat"');
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($bat));
        echo $bat;
        exit;

    case 'download_reg_branding':
        $regContent = "Windows Registry Editor Version 5.00\r\n\r\n";
        $regContent .= "[HKEY_CURRENT_USER\\Software\\Classes\\rustdesk]\r\n";
        $regContent .= "@=\"OptimaIT Soporte Remoto\"\r\n";
        $regContent .= "\"URL Protocol\"=\"\"\r\n";
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="OptimaIT_Personalizar_Protocolo.reg"');
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($regContent));
        echo $regContent;
        exit;

    case 'launch_connection':
        $id = preg_replace('/[^0-9]/', '', $_REQUEST['id'] ?? '');
        $pwd = trim($_REQUEST['password'] ?? '');
        $mode = trim($_REQUEST['mode'] ?? '--connect');
        if (!in_array($mode, ['--connect', '--terminal', '--file-transfer'])) {
            $mode = '--connect';
        }
        
        if (!empty($id)) {
            $sessions = getSessions();
            foreach ($sessions as $sK => &$sVal) {
                if (($sVal['rustdesk_id'] ?? '') === $id) {
                    $sVal['status'] = 'online';
                    $sVal['last_seen'] = time();
                }
            }
            saveSessions($sessions);

            $exe = 'C:\\Users\\BryanRivas\\AppData\\Local\\rustdesk\\rustdesk.exe';
            if (!file_exists($exe)) {
                $exe = 'C:\\Program Files\\RustDesk\\rustdesk.exe';
            }
            if (!file_exists($exe)) {
                $exe = __DIR__ . DIRECTORY_SEPARATOR . 'OptimaIT.exe';
            }
            $cmd = "start \"\" \"{$exe}\" {$mode} {$id}";
            if (!empty($pwd)) {
                $cmd .= " --password " . escapeshellarg($pwd);
            }
            pclose(popen($cmd, "r"));
            echo json_encode(['success' => true, 'cmd' => $cmd]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID invalido']);
        }
        break;

    case 'login':
        $username = trim($_REQUEST['username'] ?? '');
        $password = $_REQUEST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Por favor complete todos los campos']);
            exit;
        }

        $users = getUsers();
        $uKey = strtolower($username);
        
        if (isset($users[$uKey])) {
            $user = $users[$uKey];
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                if ($password === $user['password']) {
                    $users[$uKey]['password'] = password_hash($password, PASSWORD_DEFAULT);
                    saveUsers($users);
                }
                $_SESSION['optimait_user'] = [
                    'username' => $user['username'],
                    'name' => $user['name'] ?? $user['username'],
                    'role' => $user['role'] ?? 'tecnico'
                ];
                echo json_encode([
                    'success' => true,
                    'user' => $_SESSION['optimait_user']
                ]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
        break;

    case 'logout':
        unset($_SESSION['optimait_user']);
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'get_current_user':
        if (!empty($_SESSION['optimait_user'])) {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => $_SESSION['optimait_user']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false
            ]);
        }
        break;

    case 'list_users':
        if (empty($_SESSION['optimait_user']) || ($_SESSION['optimait_user']['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado. Se requieren permisos de administrador.']);
            exit;
        }
        $users = getUsers();
        $list = [];
        foreach ($users as $u) {
            $list[] = [
                'username' => $u['username'],
                'name' => $u['name'] ?? $u['username'],
                'role' => $u['role'] ?? 'tecnico',
                'created_at' => $u['created_at'] ?? 'Reciente'
            ];
        }
        echo json_encode(['success' => true, 'users' => $list]);
        break;

    case 'create_user':
        if (empty($_SESSION['optimait_user']) || ($_SESSION['optimait_user']['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado. Se requieren permisos de administrador.']);
            exit;
        }
        $newUsername = trim($_REQUEST['new_username'] ?? '');
        $newName = trim($_REQUEST['new_name'] ?? '');
        $newPassword = $_REQUEST['new_password'] ?? '';
        $newRole = trim($_REQUEST['new_role'] ?? 'tecnico');

        if (!in_array($newRole, ['admin', 'tecnico'])) {
            $newRole = 'tecnico';
        }
        if (empty($newUsername) || empty($newPassword)) {
            echo json_encode(['success' => false, 'error' => 'Nombre de usuario y contraseña son obligatorios']);
            exit;
        }
        if (strlen($newPassword) < 4) {
            echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 4 caracteres']);
            exit;
        }

        $users = getUsers();
        $uKey = strtolower($newUsername);
        if (isset($users[$uKey])) {
            echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya existe']);
            exit;
        }

        $users[$uKey] = [
            'username' => $newUsername,
            'name' => !empty($newName) ? $newName : $newUsername,
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'role' => $newRole,
            'created_at' => date('Y-m-d H:i:s')
        ];
        saveUsers($users);
        echo json_encode(['success' => true, 'message' => "Usuario '{$newUsername}' creado exitosamente"]);
        break;

    case 'delete_user':
        if (empty($_SESSION['optimait_user']) || ($_SESSION['optimait_user']['role'] ?? '') !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado. Se requieren permisos de administrador.']);
            exit;
        }
        $targetUser = trim($_REQUEST['target_user'] ?? $_REQUEST['username'] ?? '');
        $uKey = strtolower($targetUser);

        if (empty($targetUser)) {
            echo json_encode(['success' => false, 'error' => 'Usuario no especificado']);
            exit;
        }

        if ($uKey === strtolower($_SESSION['optimait_user']['username'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
            exit;
        }

        $users = getUsers();
        if (!isset($users[$uKey])) {
            echo json_encode(['success' => false, 'error' => 'El usuario no existe']);
            exit;
        }

        $adminCount = 0;
        foreach ($users as $u) {
            if (($u['role'] ?? '') === 'admin') $adminCount++;
        }
        if (($users[$uKey]['role'] ?? '') === 'admin' && $adminCount <= 1) {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar el único administrador del sistema']);
            exit;
        }

        unset($users[$uKey]);
        saveUsers($users);
        echo json_encode(['success' => true, 'message' => "Usuario '{$targetUser}' eliminado exitosamente"]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
