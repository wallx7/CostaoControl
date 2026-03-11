<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/banco.php';

function ai_http_request($method, $path, $payload = null) {
    $base = config('AI_SERVICE_URL', '');
    if ($base === '') return null;
    $url = rtrim($base, '/') . $path;
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json'];
    $token = config('AI_SECRET_KEY', '');
    if (!empty($token)) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    ai_close($ch);
    if ($resp === false || $code < 200 || $code >= 300) return null;
    $json = json_decode((string)$resp, true);
    return is_array($json) ? $json : null;
}

function ai_close($ch) {
    try { curl_dispose($ch); } catch (Throwable $e) {}
}

function getAIDashboardData() {
    $rows = equipamentosList();
    if (!is_array($rows)) $rows = [];
    $total = count($rows);
    $statusCounts = ['in_use' => 0, 'in_stock' => 0, 'maintenance' => 0, 'discarded' => 0];
    $typeCounts = [];
    foreach ($rows as $e) {
        $st = strtolower((string)($e['status'] ?? ''));
        if ($st !== '') {
            if (strpos($st, 'uso') !== false) $statusCounts['in_use']++;
            else if (strpos($st, 'estoque') !== false) $statusCounts['in_stock']++;
            else if (strpos($st, 'manuten') !== false) $statusCounts['maintenance']++;
            else if (strpos($st, 'descart') !== false) $statusCounts['discarded']++;
        }
        $tipo = (string)($e['tipo'] ?? 'Geral');
        if ($tipo === '') $tipo = 'Geral';
        $typeCounts[$tipo] = ($typeCounts[$tipo] ?? 0) + 1;
    }

    $health = ai_http_request('GET', '/api/health', null);
    $serviceHealth = [
        'status' => $health && isset($health['status']) ? $health['status'] : 'offline',
        'models_loaded' => $health['models_loaded'] ?? 0,
        'redis_connected' => $health['redis_connected'] ?? false
    ];

    $analytics = ai_http_request('GET', '/api/equipment/analytics', null);
    if (is_array($analytics)) {
        $total = $analytics['total_equipment'] ?? $total;
        $statusDist = $analytics['status_distribution'] ?? [];
        $typeDist = $analytics['type_distribution'] ?? [];
        $statusCounts = [
            'in_use' => (int)($statusDist['Em uso'] ?? $statusDist['in_use'] ?? 0),
            'in_stock' => (int)($statusDist['Em estoque'] ?? $statusDist['in_stock'] ?? 0),
            'maintenance' => (int)($statusDist['Manutenção'] ?? $statusDist['maintenance'] ?? 0),
            'discarded' => (int)($statusDist['Descartado'] ?? $statusDist['discarded'] ?? 0)
        ];
        $typeCounts = is_array($typeDist) ? $typeDist : $typeCounts;
    }

    $topType = '';
    foreach ($typeCounts as $k => $v) { if ($topType === '' || $v > ($typeCounts[$topType] ?? 0)) $topType = $k; }
    $pred = [];
    if ($topType !== '') {
        $predReq = ['equipment_type' => $topType, 'days_ahead' => 30];
        $predRes = ai_http_request('POST', '/api/predict/demand', $predReq);
        if (is_array($predRes) && isset($predRes['predictions']) && is_array($predRes['predictions'])) $pred = $predRes['predictions'];
    }

    $modelsStatus = [];
    $ml = (int)($serviceHealth['models_loaded'] ?? 0);
    if ($ml > 0) $modelsStatus[] = 'demand';
    if ($ml > 1) $modelsStatus[] = 'anomaly';
    if ($ml > 2) $modelsStatus[] = 'lifespan';

    $problems = [];
    if (($serviceHealth['status'] ?? 'offline') !== 'healthy') {
        $problems[] = [
            'code' => 'AI_OFFLINE',
            'message' => 'Serviço de IA indisponível',
            'severity' => 'high',
            'source' => 'ai_service'
        ];
    }
    if ($total === 0) {
        $problems[] = [
            'code' => 'NO_EQUIPMENT',
            'message' => 'Nenhum equipamento cadastrado',
            'severity' => 'info',
            'source' => 'inventario'
        ];
    }

    return [
        'service_health' => $serviceHealth,
        'analytics' => [
            'total_equipment' => $total,
            'status_distribution' => $statusCounts,
            'type_distribution' => $typeCounts
        ],
        'models_status' => $modelsStatus,
        'demand_predictions' => $pred,
        'anomalies' => [],
        'maintenance_recommendations' => [],
        'problems_and_diagnostics' => $problems
    ];
}

?>
