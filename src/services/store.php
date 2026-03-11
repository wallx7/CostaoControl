<?php
require_once __DIR__ . '/banco.php';

function storeInit()
{
}

function equipamentosList($q = '', $status = '', $tipo = '', $localizacao = '', $patrimonio = '', $serie = '', $limit = null, $offset = null)
{
    $params = ['select' => '*', 'order' => 'criado_em.desc'];
    if ($q !== '') {
        $params['or'] = '(nome.ilike.*' . $q . '*,numero_serie.ilike.*' . $q . '*,patrimonio.ilike.*' . $q . '*,localizacao.ilike.*' . $q . '* )';
    }
    if ($status !== '') { $params['status'] = 'eq.' . $status; }
    if ($tipo !== '') { $params['tipo'] = 'eq.' . $tipo; }
    if ($localizacao !== '') { $params['localizacao'] = 'ilike.*' . $localizacao . '*'; }
    if ($patrimonio !== '') { $params['patrimonio'] = 'ilike.*' . $patrimonio . '*'; }
    if ($serie !== '') { $params['numero_serie'] = 'ilike.*' . $serie . '*'; }
    if ($limit !== null) { $params['limit'] = (int)$limit; }
    if ($offset !== null) { $params['offset'] = (int)$offset; }
    return banco_get('equipamentos', $params);
}

function equipamentosCount()
{
    try {
        $all = equipamentosList();
        return is_array($all) ? count($all) : 0;
    } catch (Exception $e) {
        return 0;
    }
}

function equipamentoAdd($data)
{
    $keys = ['nome','tipo','marca','modelo','especificacoes','numero_serie','patrimonio','departamento','localizacao','status','data_aquisicao','garantia_fim','fornecedor','valor'];
    $vals = [];
    foreach ($keys as $k) { $vals[$k] = $data[$k] ?? null; }
    $precoRaw = $data['preco'] ?? null;
    if ($precoRaw !== null && $precoRaw !== '') {
        $norm = is_string($precoRaw) ? preg_replace('/[^0-9,\.]/','', $precoRaw) : $precoRaw;
        if (is_string($norm)) { $norm = str_replace('.', '', $norm); $norm = str_replace(',', '.', $norm); }
        $vals['valor'] = is_numeric($norm) ? number_format((float)$norm, 2, '.', '') : '0.00';
    }
    if (!$vals['nome']) $vals['nome'] = 'Item';
    if (!$vals['tipo']) $vals['tipo'] = 'Geral';
    if (!$vals['status']) $vals['status'] = 'Em uso';
    if (!$vals['valor']) $vals['valor'] = 0;
    $res = banco_insert('equipamentos', $vals);
    if (!empty($res)) {
        $id = $res[0]['id'];
        logActivity('create', 'equipamento', $id, 'Equipamento criado: ' . $vals['nome']);
        return $id;
    }
    return 0;
}

function equipamentoGet($id)
{
    $res = banco_get('equipamentos', ['select' => '*', 'id' => 'eq.' . $id, 'limit' => 1]);
    return $res[0] ?? null;
}

function equipamentoUpdate($id, $data)
{
    $existing = equipamentoGet($id);
    if (!$existing) return false;
    $data['atualizado_em'] = date('c');
    $existingFields = array_keys($existing);
    $filtered = [];
    foreach ($data as $k => $v) {
        if (in_array($k, $existingFields, true) || $k === 'atualizado_em') {
            $filtered[$k] = $v;
        }
    }
    $res = banco_update_admin('equipamentos', ['id' => 'eq.' . $id], $filtered);
    $ok = (is_array($res) && count($res) > 0) || $res === null;
    if ($ok) {
        $nome = $data['nome'] ?? $existing['nome'];
        logActivity('update', 'equipamento', $id, 'Equipamento atualizado: ' . $nome);
        return true;
    }
    return false;
}

function equipamentoDelete($id)
{
    $existing = equipamentoGet($id);
    if ($existing) {
        banco_delete('equipamentos', ['id' => 'eq.' . $id]);
        logActivity('delete', 'equipamento', $id, 'Equipamento excluído: ' . $existing['nome']);
        return true;
    }
    return false;
}

function categoriasList()
{
    return banco_get('categorias', ['select' => '*', 'order' => 'nome.asc']);
}

function categoriaGet($id)
{
    $rows = banco_get('categorias', ['select' => '*', 'id' => 'eq.' . (int)$id, 'limit' => 1]);
    return $rows[0] ?? null;
}

function categoriaAdd($nome, $icon = 'solar:box-bold-duotone')
{
    $res = banco_insert('categorias', ['nome' => $nome, 'icon' => $icon]);
    if (!empty($res)) {
        $id = $res[0]['id'] ?? null;
        if ($id) logActivity('create', 'categoria', $id, 'Categoria criada: ' . $nome);
        return true;
    }
    return false;
}

function categoriaUpdate($id, $nome, $icon)
{
    $payload = ['nome' => $nome, 'icon' => $icon];
    $res = banco_update('categorias', ['id' => 'eq.' . (int)$id], $payload);
    $ok = (is_array($res) && count($res) > 0) || $res === null;
    if ($ok) {
        logActivity('update', 'categoria', (int)$id, 'Categoria atualizada: ' . $nome);
        return true;
    }
    return false;
}

function categoriaDelete($id)
{
    banco_delete('categorias', ['id' => 'eq.' . (int)$id]);
    logActivity('delete', 'categoria', (int)$id, 'Categoria excluída');
    return true;
}

function logActivity($acao, $entidade, $entidadeId, $msg)
{
    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
        $admins = banco_get('usuarios', ['select' => 'id', 'papel' => 'eq.admin', 'limit' => 1]);
        if (!empty($admins)) $userId = $admins[0]['id'];
    }
    if ($userId) {
        banco_insert_admin('logs_atividades', [
            'usuario_id' => $userId,
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'mensagem' => $msg
        ]);
    }
}

function logsList($limit = 50)
{
    return banco_get('logs_atividades', ['select' => '*', 'order' => 'criado_em.desc', 'limit' => $limit]);
}

function logAdd($usuarioId, $acao, $entidade, $entidadeId, $payload = [])
{
    $mensagem = is_array($payload) ? json_encode($payload) : (string)$payload;
    banco_insert_admin('logs_atividades', [
        'usuario_id' => $usuarioId,
        'acao' => $acao,
        'entidade' => $entidade,
        'entidade_id' => $entidadeId,
        'mensagem' => $mensagem
    ]);
}

function termoAdd($arg1, $equipamentoId = null, $usuario = null, $obs = '')
{
    if (is_array($arg1)) {
        $res = banco_insert('termos', $arg1);
        if (!empty($res)) {
            $id = $res[0]['id'] ?? null;
            if ($id) logActivity('create', 'termo', $id, 'Termo criado: ' . ($arg1['tipo'] ?? ''));
            return $id;
        }
        return 0;
    } else {
        $tipo = $arg1;
        $payload = [
            'tipo' => $tipo,
            'equipamento_id' => $equipamentoId,
            'usuario_responsavel' => $usuario,
            'observacoes' => $obs
        ];
        if (is_string($usuario) && $usuario !== '') {
            $payload['usuario_responsavel'] = $usuario;
        }
        $res = banco_insert('termos', $payload);
        if (!empty($res)) {
            $id = $res[0]['id'] ?? null;
            if ($id) logActivity('create', 'termo', $id, 'Termo criado: ' . $tipo);
            return $id;
        }
        return 0;
    }
}

function termosList()
{
    $rows = banco_get('termos', ['select' => '*,equipamentos(nome)', 'order' => 'data_geracao.desc']);
    $out = [];
    foreach ($rows as $row) {
        $t = $row;
        $t['equipamento_nome'] = isset($row['equipamentos']['nome']) ? $row['equipamentos']['nome'] : 'N/A';
        if (!isset($t['equip_nome'])) { $t['equip_nome'] = $t['equipamento_nome']; }
        if (!isset($t['equip_id'])) { $t['equip_id'] = $row['equipamento_id'] ?? null; }
        if (!isset($t['codigo'])) { $t['codigo'] = 'T-' . (int)($row['id'] ?? 0); }
        if (!isset($t['colab_nome'])) { $t['colab_nome'] = $row['usuario_responsavel'] ?? ''; }
        if (!isset($t['status'])) { $t['status'] = ((int)($row['assinado'] ?? 0) ? 'assinado' : 'pendente'); }
        if (!isset($t['criado_em'])) { $t['criado_em'] = $row['data_geracao'] ?? ''; }
        if (!isset($t['assinado_em'])) { $t['assinado_em'] = $row['assinado_em'] ?? ''; }
        $out[] = $t;
    }
    return $out;
}

function colaboradoresList()
{
    $rows = banco_get('colaboradores', ['select' => 'id,nome,email,cpf,departamento,cargo,ativo', 'order' => 'nome.asc']);
    $out = [];
    foreach ($rows as $r) {
        if (!isset($r['celula'])) { $r['celula'] = $r['departamento'] ?? null; }
        $out[] = $r;
    }
    return $out;
}

function colaboradorAdd($nome, $email = '', $cpf = '', $departamento = '', $cargo = '', $ativo = 1)
{
    $payload = [
        'nome' => $nome,
        'email' => $email ?: null,
        'cpf' => $cpf ?: null,
        'departamento' => $departamento ?: null,
        'cargo' => $cargo ?: null,
        'ativo' => (int)$ativo
    ];
    try {
        $res = banco_insert('colaboradores', $payload);
        if (!empty($res)) {
            $id = $res[0]['id'] ?? null;
            if ($id) logActivity('create', 'colaborador', (int)$id, 'Colaborador criado: ' . $nome);
            return true;
        }
        return false;
    } catch (Exception $e) {
        if (!empty($payload['email'])) {
            $dup = banco_get('colaboradores', ['select' => 'id', 'email' => 'eq.' . $payload['email'], 'limit' => 1]);
            $cid = $dup[0]['id'] ?? null;
            if ($cid) {
                banco_update('colaboradores', ['id' => 'eq.' . (int)$cid], $payload);
                logActivity('update', 'colaborador', (int)$cid, 'Colaborador atualizado: ' . $nome);
                return true;
            }
        }
        return false;
    }
}

function colaboradorUpdate($id, $data)
{
    $payload = [];
    foreach (['nome','email','cpf','departamento','cargo','ativo'] as $k) {
        if (array_key_exists($k, $data)) $payload[$k] = $data[$k];
    }
    $res = banco_update('colaboradores', ['id' => 'eq.' . (int)$id], $payload);
    $ok = (is_array($res) && count($res) > 0) || $res === null;
    if ($ok) {
        logActivity('update', 'colaborador', (int)$id, 'Colaborador atualizado');
        return true;
    }
    return false;
}

function colaboradorRenameAndPropagate($id, $novoNome)
{
    $rows = banco_get('colaboradores', ['select' => 'id,nome', 'id' => 'eq.' . (int)$id, 'limit' => 1]);
    if (empty($rows)) return false;
    return colaboradorUpdate((int)$id, ['nome' => $novoNome]);
}

function termoDevolucaoCriar($equipId, $colabNome, $obs = 'Devolução gerada pela interface')
{
    $id = termoAdd('Devolução', (int)$equipId, (string)$colabNome, (string)$obs);
    return (int)$id;
}

function usersList()
{
    return banco_get('usuarios', ['select' => 'id,nome,email', 'order' => 'nome.asc']);
}

function anexosList($entidadeId, $entidade = 'equipamento')
{
    $rows = banco_get('anexos', ['select' => '*', 'entidade' => 'eq.' . $entidade, 'entidade_id' => 'eq.' . (int)$entidadeId, 'order' => 'criado_em.desc']);
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id' => $r['id'] ?? null,
            'caminho' => $r['caminho_arquivo'] ?? '',
            'caminho_arquivo' => $r['caminho_arquivo'] ?? '',
            'nome_original' => $r['nome_original'] ?? '',
            'tipo' => $r['tipo_mime'] ?? '',
            'tipo_mime' => $r['tipo_mime'] ?? '',
            'criado_em' => $r['criado_em'] ?? null
        ];
    }
    return $out;
}

function anexoAdd($entidadeId, $tipo, $nomeOriginal, $caminhoRel, $size = 0, $entidade = 'equipamento')
{
    $ext = strtolower(pathinfo((string)$nomeOriginal, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
    elseif ($ext === 'png') $mime = 'image/png';
    elseif ($ext === 'gif') $mime = 'image/gif';
    elseif ($ext === 'pdf') $mime = 'application/pdf';
    elseif ($ext === 'doc') $mime = 'application/msword';
    elseif ($ext === 'docx') $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    elseif ($ext === 'xls') $mime = 'application/vnd.ms-excel';
    elseif ($ext === 'xlsx') $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    elseif ($ext === 'txt') $mime = 'text/plain';
    $payload = [
        'entidade' => $entidade,
        'entidade_id' => (int)$entidadeId,
        'caminho_arquivo' => $caminhoRel,
        'nome_original' => $nomeOriginal,
        'tipo_mime' => $mime
    ];
    $res = banco_insert('anexos', $payload);
    return !empty($res);
}

function mapHotspotsList()
{
    try {
        $rows = banco_get('map_hotspots', ['select' => 'id,nome,x,y', 'order' => 'id.asc']);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'nome' => $r['nome'] ?? 'Ponto',
                'x' => (float)($r['x'] ?? 0),
                'y' => (float)($r['y'] ?? 0)
            ];
        }
        return $out;
    } catch (Exception $e) {
        return [];
    }
}

function mapHotspotsReplace($spots)
{
    if (!is_array($spots)) return false;
    try {
        banco_delete('map_hotspots', ['id' => 'gt.0']);
    } catch (Exception $e) {}
    $payload = [];
    foreach ($spots as $s) {
        $payload[] = [
            'nome' => $s['nome'] ?? 'Ponto',
            'x' => (float)($s['x'] ?? 0),
            'y' => (float)($s['y'] ?? 0)
        ];
    }
    if (count($payload) === 0) return true;
    try {
        $res = banco_insert('map_hotspots', $payload);
        return !empty($res);
    } catch (Exception $e) {
        return false;
    }
}

function termoGet($id)
{
    $rows = banco_get('termos', ['select' => '*,equipamentos(nome)', 'id' => 'eq.' . $id, 'limit' => 1]);
    $row = $rows[0] ?? null;
    if (!$row) return null;
    $termo = [
        'id' => $row['id'] ?? (int)$id,
        'tipo' => $row['tipo'] ?? '',
        'codigo' => $row['codigo'] ?? ('T-' . (int)$id),
        'equip_nome' => isset($row['equipamentos']['nome']) ? $row['equipamentos']['nome'] : ($row['equip_nome'] ?? ''),
        'equip_id' => $row['equipamento_id'] ?? ($row['equip_id'] ?? null),
        'colab_nome' => $row['usuario_responsavel'] ?? ($row['colab_nome'] ?? ''),
        'colab_doc' => $row['colab_doc'] ?? '',
        'local' => $row['local'] ?? '',
        'data_prevista' => $row['data_prevista'] ?? '',
        'observacoes' => $row['observacoes'] ?? '',
        'status' => (($row['assinado'] ?? 0) ? 'assinado' : 'pendente'),
        'criado_em' => $row['data_geracao'] ?? ($row['criado_em'] ?? null),
        'assinado_em' => $row['assinado_em'] ?? null,
        'assinaturas' => []
    ];
    $anexos = banco_get('anexos', ['select' => '*', 'entidade' => 'eq.termo', 'entidade_id' => 'eq.' . $termo['id']]);
    foreach ($anexos as $a) {
        $name = strtolower((string)($a['nome_original'] ?? ''));
        if (strpos($name, 'colab') !== false) {
            $termo['assinaturas']['colaborador'] = $a['caminho_arquivo'] ?? '';
            if (empty($termo['assinado_em']) && !empty($a['criado_em'])) { $termo['assinado_em'] = $a['criado_em']; }
        } elseif (strpos($name, 'resp') !== false) {
            $termo['assinaturas']['responsavel'] = $a['caminho_arquivo'] ?? '';
            if (empty($termo['assinado_em']) && !empty($a['criado_em'])) { $termo['assinado_em'] = $a['criado_em']; }
        }
    }
    return $termo;
}

function saveSignatureForTermo($termoId, $dataUrl, $role = 'colaborador')
{
    if (!is_string($dataUrl) || strpos($dataUrl, 'data:') !== 0) return null;
    $parts = explode(',', $dataUrl, 2);
    if (count($parts) < 2) return null;
    $meta = $parts[0];
    $b64 = $parts[1];
    $mime = 'image/png';
    if (stripos($meta, 'image/jpeg') !== false || stripos($meta, 'image/jpg') !== false) { $mime = 'image/jpeg'; }
    $bin = base64_decode($b64);
    if ($bin === false) return null;
    $tmp = tempnam(sys_get_temp_dir(), 'sig_');
    file_put_contents($tmp, $bin);
    $ext = $mime === 'image/png' ? 'png' : 'jpg';
    $fname = $role . '_' . uniqid('', true) . '.' . $ext;
    $object = 'assinaturas/' . (int)$termoId . '/' . $fname;
    try {
        banco_storage_ensure_bucket('termos');
        banco_storage_upload('termos', $object, $tmp, $mime);
    } catch (Exception $e) {
        @unlink($tmp);
        return null;
    }
    @unlink($tmp);
    $public = banco_storage_public_url('termos', $object);
    banco_insert('anexos', [
        'entidade' => 'termo',
        'entidade_id' => (int)$termoId,
        'caminho_arquivo' => $public,
        'nome_original' => $fname,
        'tipo_mime' => $mime
    ]);
    return $public;
}

function termoAssinar($id, $assinaturas)
{
    $payload = ['assinado' => 1];
    $uid = isset($assinaturas['usuario_id']) ? (int)$assinaturas['usuario_id'] : 0;
    if ($uid > 0) {
        $u = banco_get('colaboradores', ['select' => 'nome,email', 'id' => 'eq.' . $uid, 'limit' => 1]);
        $payload['usuario_responsavel'] = ($u[0]['nome'] ?? null);
    }
    if (!empty($assinaturas['colaborador'])) {
        saveSignatureForTermo((int)$id, (string)$assinaturas['colaborador'], 'colab');
    }
    banco_update('termos', ['id' => 'eq.' . $id], $payload);
    if ($uid > 0) { @sendTermoEmail((int)$id, $uid); }
    return true;
}

function checklistSave($equipamentoId, $items, $statusFinal)
{
    $res = banco_insert('checklists', [
        'equipamento_id' => $equipamentoId,
        'itens_json' => $items,
        'status_final' => $statusFinal
    ]);
    if (!empty($res)) {
        $id = $res[0]['id'] ?? null;
        if ($id) logActivity('create', 'checklist', $id, 'Checklist criado para equipamento #' . $equipamentoId);
        return true;
    }
    return false;
}

function checklistDefaults()
{
    return [
        'av_kaspersky' => 0,
        'remote_rustdesk' => 0,
        'updates_ok' => 0,
        'encryption_ok' => 0,
        'backup_ok' => 0,
        'asset_tagged' => 0,
        'notes' => '',
        'updated_by' => null,
        'scheduled_at' => null,
        'assigned_to' => null,
        'assigned_to_id' => null,
        'assigned_by' => null,
        'assign_reason' => null,
        'assign_requested_at' => null,
        'status_final' => 'Pendente'
    ];
}

function checklistGet($equipamentoId)
{
    $rows = banco_get('checklists', ['select' => '*', 'equipamento_id' => 'eq.' . $equipamentoId, 'order' => 'id.desc', 'limit' => 1]);
    $base = checklistDefaults();
    $row = $rows[0] ?? null;
    if ($row) {
        $items = $row['itens_json'] ?? [];
        if (!is_array($items)) {
            $items = json_decode((string)$items, true) ?: [];
        }
        $base = array_merge($base, $items);
        if (isset($row['status_final'])) $base['status_final'] = $row['status_final'];
    }
    return $base;
}

function checklistSet($equipamentoId, $items)
{
    $current = checklistGet($equipamentoId);
    $merged = array_merge($current, $items);
    $allOk = ($merged['av_kaspersky'] ?? 0) && ($merged['remote_rustdesk'] ?? 0) && ($merged['updates_ok'] ?? 0) && ($merged['encryption_ok'] ?? 0) && ($merged['backup_ok'] ?? 0) && ($merged['asset_tagged'] ?? 0) && empty($merged['scheduled_at']);
    $statusFinal = $allOk ? 'OK' : (!empty($merged['scheduled_at']) ? 'Agendado' : 'Pendente');
    return checklistSave($equipamentoId, $merged, $statusFinal);
}

function getDashboardData()
{
    $rows = equipamentosWarehouseList();
    $total = is_array($rows) ? count($rows) : 0;
    $valor = 0;
    $manutencao = 0;
    foreach ($rows as $e) {
        $valor += (float)($e['valor'] ?? 0);
        if (stripos($e['status'] ?? '', 'Manuten') !== false) $manutencao++;
    }
    return [
        'total_equipamentos' => $total,
        'valor_total_estoque' => $valor,
        'manutencoes_pendentes' => $manutencao
    ];
}

function equipamentosWarehouseList()
{
    $params = [
        'select' => '*',
        'order' => 'criado_em.desc',
        'localizacao' => 'not.is.null',
        'status' => 'not.ilike.*dispon*'
    ];
    return banco_get('equipamentos', $params);
}

function equipamentosWarehouseCount()
{
    return equipamentosWarehouseCountExact();
}

function equipamentosWarehouseCountExact()
{
    $params = [
        'select' => 'id',
        'localizacao' => 'not.is.null',
        'status' => 'not.ilike.*dispon*'
    ];
    return (int)banco_count('equipamentos', $params);
}

function equipamentosSystemList()
{
    $params = [
        'select' => '*',
        'order' => 'criado_em.desc',
        'status' => 'not.ilike.*dispon*',
        'nome' => 'not.ilike.*Equipamento *'
    ];
    return banco_get('equipamentos', $params);
}

function equipamentosSystemCount()
{
    $params = [
        'select' => 'id',
        'status' => 'not.ilike.*dispon*',
        'nome' => 'not.ilike.*Equipamento *'
    ];
    return (int)banco_count('equipamentos', $params);
}

function oldSignaturesList($limit = 20, $offset = 0)
{
    $params = ['select' => '*', 'entidade' => 'eq.assinatura_antiga', 'order' => 'id.desc', 'limit' => (int)$limit, 'offset' => (int)$offset];
    return banco_get('anexos', $params);
}

function oldSignatureImport($tmpPath, $originalName)
{
    $ext = strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if (in_array($ext, ['jpg','jpeg'])) $mime = 'image/jpeg';
    elseif ($ext === 'png') $mime = 'image/png';
    elseif ($ext === 'gif') $mime = 'image/gif';
    elseif ($ext === 'pdf') $mime = 'application/pdf';
    $object = 'old/' . uniqid('', true) . '_' . basename((string)$originalName);
    banco_storage_ensure_bucket('assinaturas_antigas');
    banco_storage_upload('assinaturas_antigas', $object, $tmpPath, $mime);
    $public = banco_storage_public_url('assinaturas_antigas', $object);
    banco_insert('anexos', [
        'entidade' => 'assinatura_antiga',
        'entidade_id' => 0,
        'caminho_arquivo' => $public,
        'nome_original' => $originalName,
        'tipo_mime' => $mime
    ]);
    return $public;
}

function getAIDataForTraining() {
    return [
        'estoque_baixo' => [],
        'alertas' => []
    ];
}

function getUserActivities($userId, $limit = 10) {
    return banco_get('logs_atividades', ['select' => '*', 'usuario_id' => 'eq.' . $userId, 'order' => 'criado_em.desc', 'limit' => $limit]);
}

function sendEmail($to, $subject, $html, $fromEmail = null, $fromName = null) {
    $fromEmail = $fromEmail ?: config('EMAIL_FROM', 'no-reply@localhost');
    $fromName = $fromName ?: config('APP_NAME', 'Costão Control');
    $sendgrid = config('SENDGRID_API_KEY', '');
    if ($sendgrid) {
        $payload = [
            'personalizations' => [[ 'to' => [[ 'email' => $to ]] ]],
            'from' => [ 'email' => $fromEmail, 'name' => $fromName ],
            'subject' => $subject,
            'content' => [[ 'type' => 'text/html', 'value' => $html ]]
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $sendgrid,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        ($ch);
        return $code >= 200 && $code < 300;
    }
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . ($fromName ? ($fromName . ' <' . $fromEmail . '>') : $fromEmail);
    $headersStr = implode("\r\n", $headers);
    return mail($to, $subject, $html, $headersStr);
}

function sendTermoEmail($termoId, $usuarioId) {
    $termo = termoGet($termoId);
    if (!$termo) return false;
    $u = banco_get('colaboradores', ['select' => 'id,nome,email', 'id' => 'eq.' . (int)$usuarioId, 'limit' => 1]);
    $user = $u[0] ?? null;
    if (!$user) return false;
    $to = $user['email'] ?? '';
    if (!$to) return false;
    $appUrl = config('APP_URL', 'http://localhost:8000');
    $link = rtrim($appUrl, '/') . '/termo-assinar.php?id=' . (int)$termoId;
    $subject = 'Termo ' . ($termo['tipo'] ?? '') . ' — ' . ($termo['codigo'] ?? ('T-' . (int)$termoId));
    $html = '<p>Olá ' . htmlspecialchars($user['nome'] ?? '') . ',</p>' .
            '<p>Você possui um termo para validação/assinatura.</p>' .
            '<p><a href="' . htmlspecialchars($link) . '" target="_blank">Abrir termo</a></p>' .
            '<p>Equipamento: ' . htmlspecialchars($termo['equip_nome'] ?? '') . '</p>' .
            '<p>Código: ' . htmlspecialchars($termo['codigo'] ?? '') . '</p>' .
            '<p>Se você não reconhece esta solicitação, ignore este e-mail.</p>';
    $ok = sendEmail($to, $subject, $html);
    if ($ok) {
        $uid = $_SESSION['user']['id'] ?? null;
        if ($uid) logAdd((int)$uid, 'email', 'termo', (int)$termoId, ['to' => $to, 'subject' => $subject]);
        return true;
    }
    return false;
}
