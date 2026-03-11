<?php
require_once __DIR__ . '/../config/config.php';

function banco_mock_enabled() {
    $mock = (string)config('BANCO_MOCK', '0') === '1';
    if ($mock) return true;
    return !(Config::getInstance()->isSupabaseConfigured());
}

function banco_mock_dir() {
    return realpath(__DIR__ . '/../../database') . DIRECTORY_SEPARATOR . 'mock';
}

function banco_mock_path($table) {
    $dir = banco_mock_dir();
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    return $dir . DIRECTORY_SEPARATOR . $table . '.json';
}

function banco_mock_load($table) {
    $path = banco_mock_path($table);
    if (!is_file($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode((string)$raw, true);
    return is_array($data) ? $data : [];
}

function banco_mock_save($table, $rows) {
    $path = banco_mock_path($table);
    @file_put_contents($path, json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return true;
}

function banco_lower($s) {
    return function_exists('mb_strtolower') ? mb_strtolower((string)$s, 'UTF-8') : strtolower((string)$s);
}

function banco_pos($haystack, $needle) {
    if (function_exists('mb_strpos')) {
        return mb_strpos($haystack, $needle, 0, 'UTF-8');
    }
    return strpos($haystack, $needle);
}

function banco_mock_like($haystack, $needle) {
    $h = banco_lower((string)$haystack);
    $n = banco_lower((string)$needle);
    return $n === '' || banco_pos($h, $n) !== false;
}

function banco_mock_apply_filters($rows, $params) {
    $filters = [];
    foreach ($params as $k => $v) {
        if (in_array($k, ['select','order','limit','offset','or'], true)) continue;
        $filters[$k] = $v;
    }
    $orExpr = isset($params['or']) ? (string)$params['or'] : '';
    $out = [];
    foreach ($rows as $row) {
        $ok = true;
        foreach ($filters as $field => $expr) {
            $expr = (string)$expr;
            if (stripos($expr, 'eq.') === 0) {
                $val = substr($expr, 3);
                $rv = $row[$field] ?? null;
                if ((string)$rv !== (string)$val) { $ok = false; break; }
            } elseif (stripos($expr, 'ilike.*') === 0) {
                $needle = trim($expr);
                $needle = preg_replace('/^ilike\.\*/i', '', $needle);
                $needle = preg_replace('/\*$/', '', $needle);
                if (!banco_mock_like($row[$field] ?? '', $needle)) { $ok = false; break; }
            } elseif (stripos($expr, 'not.ilike.*') === 0) {
                $needle = trim($expr);
                $needle = preg_replace('/^not\.ilike\.\*/i', '', $needle);
                $needle = preg_replace('/\*$/', '', $needle);
                if (banco_mock_like($row[$field] ?? '', $needle)) { $ok = false; break; }
            } elseif (stripos($expr, 'not.is.null') === 0) {
                $rv = $row[$field] ?? null;
                if ($rv === null || $rv === '') { $ok = false; break; }
            } elseif (stripos($expr, 'gt.') === 0) {
                $val = (float)substr($expr, 3);
                $rv = (float)($row[$field] ?? 0);
                if (!($rv > $val)) { $ok = false; break; }
            }
        }
        if ($ok && $orExpr !== '') {
            $s = trim($orExpr);
            if ($s !== '' && $s[0] === '(') $s = trim($s, " ()");
            $parts = array_filter(array_map('trim', explode(',', $s)));
            $matchAny = false;
            foreach ($parts as $p) {
                $a = explode('.', $p, 2);
                if (count($a) < 2) continue;
                $field = $a[0];
                $rest = $a[1];
                if (stripos($rest, 'ilike.*') === 0) {
                    $needle = preg_replace('/^ilike\.\*/i', '', $rest);
                    $needle = preg_replace('/\*$/', '', $needle);
                    if (banco_mock_like($row[$field] ?? '', $needle)) { $matchAny = true; break; }
                }
            }
            if (!$matchAny) $ok = false;
        }
        if ($ok) $out[] = $row;
    }
    if (!empty($params['order'])) {
        $ord = (string)$params['order'];
        $parts = explode('.', $ord);
        $field = $parts[0] ?? '';
        $dir = strtolower($parts[1] ?? 'asc');
        usort($out, function($a, $b) use ($field, $dir) {
            $av = $a[$field] ?? null; $bv = $b[$field] ?? null;
            if ($av == $bv) return 0;
            $res = ($av < $bv) ? -1 : 1;
            return $dir === 'desc' ? -$res : $res;
        });
    }
    $off = isset($params['offset']) ? (int)$params['offset'] : 0;
    $lim = isset($params['limit']) ? (int)$params['limit'] : null;
    if ($off > 0 || $lim !== null) {
        $out = array_slice($out, $off, $lim ?? null);
    }
    return $out;
}

function curl_dispose($ch) {
    try {
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID < 80400) {
            if (is_resource($ch) || (class_exists('CurlHandle') && $ch instanceof CurlHandle)) {
                @curl_close($ch);
            }
        }
    } catch (Throwable $e) {}
    unset($ch);
}

function banco_headers($useService = false) {
    $anon = config('BANCO_ANON_KEY');
    $service = config('BANCO_SERVICE_KEY');
    $sessionToken = $_SESSION['banco_token'] ?? null;
    $preferService = ($useService && !empty($service));
    $key = $preferService ? $service : $anon;
    $authToken = $preferService ? $service : ($sessionToken ?: $anon);
    return [
        'apikey: ' . $key,
        'Authorization: Bearer ' . $authToken,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
}

function banco_request($method, $endpoint, $data = null, $params = [], $useService = false) {
    $base = rtrim(config('BANCO_URL'), '/');
    $url = $base . $endpoint;
    if (!empty($params)) { $url .= '?' . http_build_query($params); }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, banco_headers($useService));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $insecure = (string)config('BANCO_INSECURE', '1') === '1';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$insecure);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $insecure ? 0 : 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CostaoControl/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($data !== null) { curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) { throw new Exception(curl_error($ch)); }
    curl_dispose($ch);
    $dec = json_decode($resp, true);
    if ($code >= 400) {
        $detail = '';
        if (is_array($dec)) { $detail = $dec['message'] ?? $dec['error_description'] ?? $dec['error'] ?? ''; }
        else if (is_string($resp)) { $detail = trim($resp); }
        if ($detail === '') { $detail = 'Erro desconhecido'; }
        throw new Exception("Banco API ($code): $detail");
    }
    return $dec;
}

function banco_mysql_enabled() {
    $mockOff = ((string)config('BANCO_MOCK','1') !== '1');
    $driver = strtolower((string)config('BANCO_DRIVER','mysql'));
    $dsn = (string)config('DB_DSN','');
    return $mockOff && ($driver === 'mysql' || $dsn !== '');
}

function banco_pdo() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = (string)config('DB_DSN','mysql:host=127.0.0.1;dbname=costao_control;charset=utf8mb4');
    $user = (string)config('DB_USER','root');
    $pass = (string)config('DB_PASS','');
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    return $pdo;
}

function banco_build_where_mysql($params, &$bind) {
    $where = [];
    foreach ($params as $k => $expr) {
        if (in_array($k, ['select','order','limit','offset','or'], true)) continue;
        $expr = (string)$expr;
        if (stripos($expr, 'eq.') === 0) {
            $val = substr($expr, 3);
            $p = ':w_' . $k . '_' . count($bind);
            $where[] = "`$k` = $p";
            $bind[$p] = $val;
        } elseif (stripos($expr, 'ilike.*') === 0) {
            $needle = preg_replace('/^ilike\.\*/i', '', $expr);
            $needle = preg_replace('/\*$/', '', $needle);
            $p = ':w_' . $k . '_' . count($bind);
            $where[] = "`$k` LIKE $p";
            $bind[$p] = '%' . $needle . '%';
        } elseif (stripos($expr, 'not.ilike.*') === 0) {
            $needle = preg_replace('/^not\.ilike\.\*/i', '', $expr);
            $needle = preg_replace('/\*$/', '', $needle);
            $p = ':w_' . $k . '_' . count($bind);
            $where[] = "`$k` NOT LIKE $p";
            $bind[$p] = '%' . $needle . '%';
        } elseif (stripos($expr, 'not.is.null') === 0) {
            $where[] = "(`$k` IS NOT NULL AND `$k` <> '')";
        } elseif (stripos($expr, 'gt.') === 0) {
            $val = substr($expr, 3);
            $p = ':w_' . $k . '_' . count($bind);
            $where[] = "`$k` > $p";
            $bind[$p] = $val;
        }
    }
    return $where ? (' WHERE ' . implode(' AND ', $where)) : '';
}

function banco_get($table, $params = []) {
    if (banco_mock_enabled()) {
        $rows = banco_mock_load($table);
        return banco_mock_apply_filters($rows, $params);
    }
    if (banco_mysql_enabled()) {
        $pdo = banco_pdo();
        $cols = '*';
        if (!empty($params['select']) && $params['select'] !== '*') {
            $cols = $params['select'];
            if (strpos($cols, 'equipamentos(') !== false) { $cols = '*'; }
        }
        $sql = "SELECT $cols FROM `$table`";
        $bind = [];
        $sql .= banco_build_where_mysql($params, $bind);
        if (!empty($params['order'])) {
            $ord = $params['order'];
            $parts = explode('.', $ord);
            $field = preg_replace('/[^a-zA-Z0-9_]/','', $parts[0] ?? '');
            $dir = strtoupper($parts[1] ?? 'ASC');
            $dir = in_array($dir, ['ASC','DESC'], true) ? $dir : 'ASC';
            if ($field !== '') $sql .= " ORDER BY `$field` $dir";
        }
        if (isset($params['limit'])) {
            $lim = (int)$params['limit'];
            $off = (int)($params['offset'] ?? 0);
            $sql .= " LIMIT $off, $lim";
        }
        $st = $pdo->prepare($sql);
        foreach ($bind as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        return $st->fetchAll();
    }
    if (!isset($params['select'])) $params['select'] = '*';
    return banco_request('GET', '/rest/v1/' . $table, null, $params);
}

function banco_count($table, $params = []) {
    if (banco_mock_enabled()) {
        $rows = banco_mock_load($table);
        $filtered = banco_mock_apply_filters($rows, $params);
        return count($filtered);
    }
    if (banco_mysql_enabled()) {
        $pdo = banco_pdo();
        $sql = "SELECT COUNT(*) AS c FROM `$table`";
        $bind = [];
        $sql .= banco_build_where_mysql($params, $bind);
        $st = $pdo->prepare($sql);
        foreach ($bind as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        $row = $st->fetch();
        return (int)($row['c'] ?? 0);
    }
    if (!isset($params['select'])) $params['select'] = 'id';
    $base = rtrim(config('BANCO_URL'), '/');
    $url = $base . '/rest/v1/' . $table;
    if (!empty($params)) { $url .= '?' . http_build_query($params); }
    $hdr = banco_headers(false);
    $out = [];
    foreach ($hdr as $h) { if (stripos($h, 'Prefer:') !== 0) $out[] = $h; }
    $out[] = 'Prefer: count=exact';
    $out[] = 'Range: 0-0';
    $out[] = 'Range-Unit: items';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $out);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HEADER, true);
    $insecure = (string)config('BANCO_INSECURE', '1') === '1';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$insecure);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $insecure ? 0 : 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CostaoControl/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hsz = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_dispose($ch);
    if ($resp === false) return 0;
    $headersRaw = substr($resp, 0, $hsz);
    $total = 0;
    foreach (explode("\r\n", $headersRaw) as $line) {
        if (stripos($line, 'Content-Range:') === 0) {
            $val = trim(substr($line, strlen('Content-Range:')));
            $p = strrpos($val, '/');
            if ($p !== false) { $n = substr($val, $p+1); if (is_numeric($n)) $total = (int)$n; }
        }
    }
    if ($code >= 400) return 0;
    return $total;
}

function banco_insert($table, $data) {
    if (banco_mock_enabled()) {
        $rows = banco_mock_load($table);
        $multi = isset($data[0]) && is_array($data[0]);
        $items = $multi ? $data : [$data];
        $maxId = 0;
        foreach ($rows as $r) { $maxId = max($maxId, (int)($r['id'] ?? 0)); }
        $inserted = [];
        foreach ($items as $it) {
            if (!isset($it['id'])) { $it['id'] = ++$maxId; }
            if (!isset($it['criado_em'])) { $it['criado_em'] = date('c'); }
            $rows[] = $it;
            $inserted[] = $it;
        }
        banco_mock_save($table, $rows);
        return $inserted;
    }
    if (banco_mysql_enabled()) {
        $pdo = banco_pdo();
        $multi = isset($data[0]) && is_array($data[0]);
        $items = $multi ? $data : [$data];
        $inserted = [];
        foreach ($items as $it) {
            $fields = array_keys($it);
            $colsSql = '`' . implode('`,`', $fields) . '`';
            $ph = array_map(function($f){ return ':' . $f; }, $fields);
            $valsSql = implode(',', $ph);
            $sql = "INSERT INTO `$table` ($colsSql) VALUES ($valsSql)";
            $st = $pdo->prepare($sql);
            foreach ($it as $k => $v) { $st->bindValue(':' . $k, $v); }
            $st->execute();
            $id = (int)$pdo->lastInsertId();
            $it['id'] = $id ?: ($it['id'] ?? null);
            $inserted[] = $it;
        }
        return $inserted;
    }
    return banco_request('POST', '/rest/v1/' . $table, $data);
}

function banco_insert_admin($table, $data) {
    if (banco_mock_enabled() || banco_mysql_enabled()) { return banco_insert($table, $data); }
    return banco_request('POST', '/rest/v1/' . $table, $data, [], true);
}

function banco_update($table, $params, $data) {
    if (banco_mock_enabled()) {
        $rows = banco_mock_load($table);
        $updated = [];
        $filters = [];
        foreach ($params as $k => $v) { $filters[$k] = $v; }
        foreach ($rows as $idx => $row) {
            $match = true;
            foreach ($filters as $field => $expr) {
                $expr = (string)$expr;
                if (stripos($expr, 'eq.') === 0) {
                    $val = substr($expr, 3);
                    if ((string)($row[$field] ?? '') !== (string)$val) { $match = false; break; }
                }
            }
            if ($match) {
                $rows[$idx] = array_merge($row, $data);
                $updated[] = $rows[$idx];
            }
        }
        banco_mock_save($table, $rows);
        return $updated;
    }
    if (banco_mysql_enabled()) {
        $pdo = banco_pdo();
        $set = [];
        $bind = [];
        foreach ($data as $k => $v) {
            $p = ':s_' . $k;
            $set[] = "`$k` = $p";
            $bind[$p] = $v;
        }
        $sql = "UPDATE `$table` SET " . implode(',', $set);
        $sql .= banco_build_where_mysql($params, $bind);
        $st = $pdo->prepare($sql);
        foreach ($bind as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        return null;
    }
    return banco_request('PATCH', '/rest/v1/' . $table, $data, $params);
}

function banco_update_admin($table, $params, $data) {
    if (banco_mock_enabled() || banco_mysql_enabled()) { return banco_update($table, $params, $data); }
    return banco_request('PATCH', '/rest/v1/' . $table, $data, $params, true);
}

function banco_delete($table, $params) {
    if (banco_mock_enabled()) {
        $rows = banco_mock_load($table);
        $filters = [];
        foreach ($params as $k => $v) { $filters[$k] = $v; }
        $out = [];
        foreach ($rows as $row) {
            $match = true;
            foreach ($filters as $field => $expr) {
                $expr = (string)$expr;
                if (stripos($expr, 'eq.') === 0) {
                    $val = substr($expr, 3);
                    if ((string)($row[$field] ?? '') !== (string)$val) { $match = false; break; }
                } elseif (stripos($expr, 'gt.') === 0) {
                    $val = substr($expr, 3);
                    if (!((float)($row[$field] ?? 0) > (float)$val)) { $match = false; break; }
                }
            }
            if (!$match) $out[] = $row;
        }
        banco_mock_save($table, $out);
        return true;
    }
    if (banco_mysql_enabled()) {
        $pdo = banco_pdo();
        $bind = [];
        $sql = "DELETE FROM `$table`";
        $sql .= banco_build_where_mysql($params, $bind);
        $st = $pdo->prepare($sql);
        foreach ($bind as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        return true;
    }
    return banco_request('DELETE', '/rest/v1/' . $table, null, $params);
}

function banco_storage_headers($contentType = 'application/octet-stream') {
    $base = banco_headers(true);
    $out = [];
    foreach ($base as $h) {
        if (stripos($h, 'Content-Type:') === 0) continue;
        $out[] = $h;
    }
    $out[] = 'Content-Type: ' . $contentType;
    $out[] = 'x-upsert: true';
    return $out;
}

function banco_storage_ensure_bucket($bucket) {
    if (banco_mock_enabled()) {
        $dir = realpath(__DIR__ . '/..');
        if ($dir === false) $dir = __DIR__ . '/..';
        $path = $dir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $bucket;
        if (!is_dir($path)) { @mkdir($path, 0777, true); }
        return;
    }
    $base = rtrim(config('BANCO_URL'), '/');
    $url = $base . '/storage/v1/bucket';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, banco_headers(true));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name'=>$bucket,'public'=>true]));
    $insecure = (string)config('BANCO_INSECURE', '1') === '1';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$insecure);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $insecure ? 0 : 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CostaoControl/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_dispose($ch);
    if ($code >= 400 && $code !== 409) {
        throw new Exception('Falha ao criar bucket: ' . $resp);
    }
}

function banco_storage_upload($bucket, $objectPath, $localFile, $contentType = 'application/octet-stream') {
    if (banco_mock_enabled()) {
        if (!is_file($localFile)) throw new Exception('Arquivo não encontrado para upload');
        $root = realpath(__DIR__ . '/..');
        if ($root === false) $root = __DIR__ . '/..';
        $dest = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $bucket . DIRECTORY_SEPARATOR . str_replace(['\\','/'], DIRECTORY_SEPARATOR, $objectPath);
        $dd = dirname($dest);
        if (!is_dir($dd)) { @mkdir($dd, 0777, true); }
        @copy($localFile, $dest);
        return true;
    }
    $base = rtrim(config('BANCO_URL'), '/');
    $url = $base . '/storage/v1/object/' . rawurlencode($bucket) . '/' . str_replace('%2F','/',rawurlencode($objectPath));
    if (!is_file($localFile)) throw new Exception('Arquivo não encontrado para upload');
    $data = file_get_contents($localFile);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, banco_storage_headers($contentType));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $insecure = (string)config('BANCO_INSECURE', '1') === '1';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$insecure);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $insecure ? 0 : 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CostaoControl/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_dispose($ch);
        throw new Exception($err);
    }
    curl_dispose($ch);
    if ($code >= 400) {
        throw new Exception('Falha no upload: ' . $resp);
    }
    return true;
}

function banco_storage_public_url($bucket, $objectPath) {
    if (banco_mock_enabled()) {
        $base = rtrim(config('APP_URL', 'http://localhost:8000'), '/');
        return $base . '/assets/uploads/' . $bucket . '/' . $objectPath;
    }
    $base = rtrim(config('BANCO_URL'), '/');
    return $base . '/storage/v1/object/public/' . $bucket . '/' . $objectPath;
}
?>
