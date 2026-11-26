<?php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$dataFile = __DIR__ . '/data/sessions.json';
$groupsFile = __DIR__ . '/data/groups.json';
$usersFile = __DIR__ . '/data/users.json';
$loginAttemptsFile = __DIR__ . '/data/login_attempts.json';

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

/**
 * Obtiene el usuario autenticado verificando que siga existiendo activamente en data/users.json.
 * Si el usuario fue eliminado por un administrador, la sesión se destruye al instante.
 */
function getAuthenticatedUser() {
    if (empty($_SESSION['optimait_user']['username'])) {
        return null;
    }
    $users = getUsers();
    $uKey = strtolower($_SESSION['optimait_user']['username']);
    if (!isset($users[$uKey])) {
        // El usuario fue eliminado del archivo de usuarios: destruir sesión
        unset($_SESSION['optimait_user']);
        session_destroy();
        return null;
    }
    // Sincronizar datos por si cambiaron de nombre o rol
    $_SESSION['optimait_user']['name'] = $users[$uKey]['name'] ?? $_SESSION['optimait_user']['name'];
    $_SESSION['optimait_user']['role'] = $users[$uKey]['role'] ?? $_SESSION['optimait_user']['role'];
    return $_SESSION['optimait_user'];
}

/**
 * Exige que el operador tenga una sesión activa y válida.
 * Si no está autenticado o su usuario fue eliminado, devuelve 401 Unauthorized y aborta.
 */
function requireAuth() {
    $user = getAuthenticatedUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'auth_error' => true,
            'error' => 'Sesión no válida o expirada. Por favor inicie sesión nuevamente.'
        ]);
        exit;
    }
    return $user;
}

/**
 * Exige que el operador autenticado tenga rol de administrador.
 */
function requireAdmin() {
    $user = requireAuth();
    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Acceso denegado. Se requieren permisos de administrador.'
        ]);
        exit;
    }
    return $user;
}

/**
 * Previene ataques de fuerza bruta en el login limitando intentos por IP
 */
function checkLoginRateLimit($ip) {
    global $loginAttemptsFile;
    if (!file_exists($loginAttemptsFile)) return true;
    $attempts = json_decode(file_get_contents($loginAttemptsFile), true);
    if (!is_array($attempts) || empty($attempts[$ip])) return true;
    
    $ipData = $attempts[$ip];
    $count = $ipData['count'] ?? 0;
    $lastAttempt = $ipData['last_attempt'] ?? 0;
    
    // Bloquear por 2 minutos (120s) si hay 5 o más fallos
    if ($count >= 5 && (time() - $lastAttempt < 120)) {
        return false;
    }
    return true;
}

function recordLoginAttempt($ip, $success) {
    global $loginAttemptsFile;
    $attempts = [];
    if (file_exists($loginAttemptsFile)) {
        $attempts = json_decode(file_get_contents($loginAttemptsFile), true) ?: [];
    }
    $now = time();
    // Limpieza de IPs con más de 1 hora de inactividad
    foreach ($attempts as $k => $v) {
        if ($now - ($v['last_attempt'] ?? 0) > 3600) {
            unset($attempts[$k]);
        }
    }
    if ($success) {
        unset($attempts[$ip]);
    } else {
        $prevCount = $attempts[$ip]['count'] ?? 0;
        $prevTime = $attempts[$ip]['last_attempt'] ?? 0;
        if ($now - $prevTime > 120) {
            $prevCount = 0;
        }
        $attempts[$ip] = [
            'count' => $prevCount + 1,
            'last_attempt' => $now
        ];
    }
    file_put_contents($loginAttemptsFile, json_encode($attempts, JSON_PRETTY_PRINT), LOCK_EX);
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

function getClientPublicIp() {
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function isPrivateNetworkIp($ip) {
    if (empty($ip)) return true;
    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
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

/**
 * Consulta en tiempo real al servidor de encuentro (hbbs) en el puerto 21115
 * el estado exacto de presencia (Online / Offline) de los IDs de RustDesk.
 */
function queryRustDeskOnlinePeers(array $peerIds): array {
    $cleanIds = [];
    foreach ($peerIds as $p) {
        $p = trim((string)$p);
        if (!empty($p) && preg_match('/^[0-9]+$/', $p)) {
            $cleanIds[] = $p;
        }
    }
    if (empty($cleanIds)) return [];

    $idBytes = 'optima-probe';
    $peersPayload = '';
    foreach ($cleanIds as $pid) {
        $peersPayload .= chr(0x12) . chr(strlen($pid)) . $pid;
    }
    $onlineReq = chr(0x0a) . chr(strlen($idBytes)) . $idBytes . $peersPayload;
    $reqLen = strlen($onlineReq);

    // Encode varint length for reqLen
    $lenBytes = '';
    $tempLen = $reqLen;
    while ($tempLen > 0x7f) {
        $lenBytes .= chr(($tempLen & 0x7f) | 0x80);
        $tempLen >>= 7;
    }
    $lenBytes .= chr($tempLen);

    // Protobuf field 23 wire type 2: (23 << 3) | 2 = 186 = 0xba, 0x01
    $msg = chr(0xba) . chr(0x01) . $lenBytes . $onlineReq;
    $msgLen = strlen($msg);
    $hdr = ($msgLen <= 0x3f) ? chr($msgLen << 2) : pack('v', ($msgLen << 2) | 1);

    // En Docker o VPS, probar localhost primero y luego la IP pública
    $hosts = ['127.0.0.1', '155.133.197.176'];
    $resp = null;
    foreach ($hosts as $h) {
        $fp = @fsockopen($h, 21115, $errno, $errstr, 0.4);
        if ($fp) {
            stream_set_timeout($fp, 0, 400000); // 400ms timeout
            fwrite($fp, $hdr . $msg);
            $resp = fread($fp, 1024);
            fclose($fp);
            if (!empty($resp) && strlen($resp) >= 6) {
                break;
            }
        }
    }

    if (empty($resp) || strlen($resp) < 6) return [];

    // Decodificar respuesta: campo 24 (OnlineResponse) -> campo 1 (states: bytes)
    $idx = strpos($resp, chr(0x0a), 2);
    if ($idx === false || $idx + 1 >= strlen($resp)) return [];
    $statesLen = ord($resp[$idx + 1]);
    $states = substr($resp, $idx + 2, $statesLen);

    $results = [];
    foreach ($cleanIds as $i => $pid) {
        $byteIndex = intdiv($i, 8);
        if ($byteIndex < strlen($states)) {
            $byteVal = ord($states[$byteIndex]);
            $bitMask = 0x80 >> ($i % 8);
            $results[$pid] = ($byteVal & $bitMask) !== 0;
        } else {
            $results[$pid] = false;
        }
    }
    return $results;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create_session':
        requireAuth();
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
        requireAuth();
        $sessions = getSessions();
        $now = time();
        $cleaned = [];
        $hasChanges = false;

        // Recolectar IDs de RustDesk registrados para consultar su presencia en tiempo real con hbbs
        $peerIdsToQuery = [];
        foreach ($sessions as $s) {
            $rId = trim((string)($s['rustdesk_id'] ?? ''));
            if (!empty($rId) && preg_match('/^[0-9]+$/', $rId)) {
                $peerIdsToQuery[] = $rId;
            }
        }
        $peerOnlineMap = !empty($peerIdsToQuery) ? queryRustDeskOnlinePeers($peerIdsToQuery) : [];

        foreach ($sessions as $id => $s) {
            $lastActivity = $s['last_seen'] ?? 0;
            if ($now - $lastActivity < 86400 * 30) { // Retener 30 días para inventario
                $rId = trim((string)($s['rustdesk_id'] ?? ''));
                $hasDirectHbbsResult = isset($peerOnlineMap[$rId]);

                if ($hasDirectHbbsResult) {
                    if ($peerOnlineMap[$rId] === true) {
                        // Si el cliente envía latidos HTTP pero lleva más de 10 segundos sin responder
                        // (por ejemplo: apagó el Wi-Fi o suspendió), marcar INACTIVO de inmediato a los 10s
                        $hasActiveHeartbeatAgent = !empty($s['last_heartbeat']);
                        if ($hasActiveHeartbeatAgent && ($now - $s['last_heartbeat'] > 10)) {
                            if ($s['status'] !== 'idle' && $s['status'] !== 'closed') {
                                $s['status'] = 'idle';
                                $hasChanges = true;
                            }
                        } else {
                            if ($s['status'] !== 'online') {
                                $s['status'] = 'online';
                                $hasChanges = true;
                            }
                            $s['last_seen'] = $now;
                        }
                    } else {
                        if ($s['status'] !== 'idle' && $s['status'] !== 'closed') {
                            $s['status'] = 'idle';
                            $hasChanges = true;
                        }
                    }
                } else {
                    // Respaldo por pulsos (heartbeat) si hbbs no responde (ventana de 10 segundos)
                    $timeoutSeconds = 10;
                    if ($now - $lastActivity > $timeoutSeconds) {
                        if ($s['status'] !== 'idle' && $s['status'] !== 'closed') {
                            $s['status'] = 'idle';
                            $hasChanges = true;
                        }
                    } else {
                        if ($s['status'] !== 'online' && !empty($s['rustdesk_id'])) {
                            $s['status'] = 'online';
                            $hasChanges = true;
                        }
                    }
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
        requireAuth();
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
            $detectedIp = getClientPublicIp();
            if (!empty($detectedIp) && !isPrivateNetworkIp($detectedIp)) {
                $sessions[$sessionId]['client_ip'] = $detectedIp;
            } else if (empty($sessions[$sessionId]['client_ip'])) {
                $sessions[$sessionId]['client_ip'] = $detectedIp;
            }
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
                'client_ip' => getClientPublicIp()
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
            $sessions[$sessionId]['last_heartbeat'] = time();
            $sessions[$sessionId]['status'] = 'online';

            $detectedIp = getClientPublicIp();
            if (!empty($detectedIp) && !isPrivateNetworkIp($detectedIp)) {
                $sessions[$sessionId]['client_ip'] = $detectedIp;
            }

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
        requireAuth();
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
        requireAuth();
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
        requireAuth();
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
        requireAuth();
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
        requireAuth();
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
        requireAuth();
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
        $ip = getClientPublicIp();
        if (!checkLoginRateLimit($ip)) {
            echo json_encode([
                'success' => false,
                'error' => 'Demasiados intentos fallidos. Por seguridad, espere 2 minutos antes de reintentar.'
            ]);
            exit;
        }

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

                // Prevenir fijación de sesión regenerando ID de cookie de sesión
                session_regenerate_id(true);
                recordLoginAttempt($ip, true);

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
        recordLoginAttempt($ip, false);
        echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
        break;

    case 'logout':
        unset($_SESSION['optimait_user']);
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'get_current_user':
        $user = getAuthenticatedUser();
        if ($user) {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false
            ]);
        }
        break;

    case 'list_users':
        requireAdmin();
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
        requireAdmin();
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
        $admin = requireAdmin();
        $targetUser = trim($_REQUEST['target_user'] ?? $_REQUEST['username'] ?? '');
        $uKey = strtolower($targetUser);

        if (empty($targetUser)) {
            echo json_encode(['success' => false, 'error' => 'Usuario no especificado']);
            exit;
        }

        if ($uKey === strtolower($admin['username'] ?? '')) {
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
