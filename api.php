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
        
        $host = $_SERVER['HTTP_HOST'] ?? '10.4.4.40';
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
        @exec('netstat -ano -p tcp | findstr 21116 | findstr ESTABLISHED', $netOut);
        foreach ($netOut as $nLine) {
            if (preg_match('/:\d+\s+([0-9.]+):\d+\s+ESTABLISHED/', $nLine, $m)) {
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
        
        $sessions = getSessions();
        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['rustdesk_id'] = $rustdeskId;
            $sessions[$sessionId]['status'] = 'online';
            $sessions[$sessionId]['last_seen'] = time();
            if ($installed) {
                $sessions[$sessionId]['installed'] = true;
            }
            $sessions[$sessionId]['client_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
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
                'client_name' => 'Cliente Directo',
                'client_type' => 'personal',
                'group_name' => 'Personas Naturales',
                'password' => $password,
                'status' => 'online',
                'rustdesk_id' => $rustdeskId,
                'created_at' => date('H:i:s'),
                'last_seen' => time(),
                'installed' => $installed
            ];
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
            saveSessions($sessions);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
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
        $targetUser = trim($_REQUEST['target_user'] ?? '');
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
