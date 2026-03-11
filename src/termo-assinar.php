<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $pendentes = array_values(array_filter(termosList(), function($t){ return ($t['status'] ?? '') === 'pendente'; }));
    $id = isset($pendentes[0]['id']) ? (int)$pendentes[0]['id'] : 0;
}
$termo = $id>0 ? termoGet($id) : null;
$colabList = colaboradoresList();
$erro = null;
$ok = null;
if (!$termo) { header('Location: termos-list.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) {
        $erro = 'Token inválido';
    } else {
        if (isset($_POST['send_email']) && $_POST['send_email'] === '1') {
            $usuario_id = (int)($_POST['usuario_id'] ?? 0);
            if ($usuario_id <= 0) {
                $erro = 'Selecione o colaborador para envio por e-mail';
            } else {
                if (sendTermoEmail($id, $usuario_id)) {
                    $ok = 'E-mail enviado com sucesso';
                } else {
                    $erro = 'Falha ao enviar e-mail';
                }
            }
        } else {
            $usuario_id = (int)($_POST['usuario_id'] ?? 0);
            $colab_sign = $_POST['colab_sign'] ?? '';
            if ($usuario_id <= 0 || $colab_sign === '') {
                $erro = 'Selecione o colaborador e capture a assinatura';
            } else {
                termoAssinar($id, ['colaborador'=>$colab_sign, 'usuario_id'=>$usuario_id]);
                $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
                logActivity('sign', 'termo', (int)$id, 'codigo=' . $termo['codigo'] . ', colaborador_id=' . $usuario_id);
                header('Location: termos-list.php');
                exit;
            }
        }
    }
}
?>
<head>
    <?php $subTitle = 'Assinar Termo'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
    <style>
    @media print {
        .main-nav, .topbar, .btn, .button-sm-hover { display:none !important; }
        .card, .page-content, .container-xxl { box-shadow:none !important; background:#fff; }
    }
    .sig-pad { border:1px dashed #637; border-radius:8px; background:#fff; touch-action:none; cursor:crosshair; }
    </style>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Assinar Termo'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title m-0">Termo <?php echo htmlspecialchars($termo['tipo']) ?> — <?php echo htmlspecialchars($termo['equip_nome'] ?? $termo['codigo']) ?></h4>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-light" onclick="window.print()">Gerar PDF</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($erro): ?><div class="alert alert-danger"><?php echo htmlspecialchars($erro) ?></div><?php endif; ?>
                            <?php if ($ok): ?><div class="alert alert-success"><?php echo htmlspecialchars($ok) ?></div><?php endif; ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Detalhes do Termo</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <?php $fmt=function($s){ if(!$s) return ''; try{ $dt=new DateTime((string)$s, new DateTimeZone('UTC')); $dt->setTimezone(new DateTimeZone('America/Sao_Paulo')); return $dt->format('d/m/Y \\à\\s H:i \\h\\o\\r\\a\\s'); }catch(Exception $e){ $ts=strtotime((string)$s); return $ts?date('d/m/Y \\à\\s H:i \\h\\o\\r\\a\\s',$ts):($s??''); } }; ?>
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <tr><td class="fw-medium">Tipo</td><td class="text-end"><?php echo htmlspecialchars($termo['tipo'] ?? '') ?></td></tr>
                                                <tr><td class="fw-medium">Equipamento</td><td class="text-end"><?php echo htmlspecialchars($termo['equip_nome'] ?? '') ?></td></tr>
                                                <tr><td class="fw-medium">Colaborador</td><td class="text-end"><?php echo htmlspecialchars($termo['colab_nome'] ?? '') ?></td></tr>
                                                <tr><td class="fw-medium">Criado</td><td class="text-end"><?php echo htmlspecialchars($fmt($termo['criado_em'] ?? '')) ?></td></tr>
                                                <tr><td class="fw-medium">Assinado</td><td class="text-end"><?php echo htmlspecialchars($fmt($termo['assinado_em'] ?? '')) ?></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php /* seção de itens mais caros removida conforme solicitação */ ?>
                            <?php $isSigned = ($termo['status'] ?? '') === 'assinado'; ?>
                            <?php if (!$isSigned): ?>
                            <form method="post" id="formAssinar">
                                <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Colaborador</label>
                                        <select class="form-select" name="usuario_id" required>
                                            <option value="">Selecione</option>
                                            <?php foreach ($colabList as $c): ?>
                                                <option value="<?php echo (int)$c['id'] ?>"><?php echo htmlspecialchars($c['nome']) ?> (<?php echo htmlspecialchars($c['email'] ?? '') ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <h6>Assinatura do Colaborador</h6>
                                        <canvas id="padColab" class="sig-pad" width="900" height="240"></canvas>
                                        <div class="mt-2 d-flex gap-2 justify-content-end">
                                            <button class="btn btn-soft-danger btn-sm" type="button" onclick="clearPad('padColab')">Limpar</button>
                                            <button class="btn btn-primary btn-sm" type="button" onclick="submitSign()">Finalizar Assinatura</button>
                                        </div>
                                        <input type="hidden" name="colab_sign" id="colab_sign">
                                    </div>
                                </div>
                                
                            </form>
                            <?php else: ?>
                            <div class="card">
                                <div class="card-header"><h6 class="mb-0">Assinatura do Colaborador</h6></div>
                                <div class="card-body text-center">
                                    <?php $sig = $termo['assinaturas']['colaborador'] ?? ''; ?>
                                    <?php if ($sig): ?>
                                        <img src="<?php echo htmlspecialchars($sig) ?>" alt="Assinatura" class="img-fluid" style="max-height:220px; object-fit:contain">
                                    <?php else: ?>
                                        <div class="text-muted">Assinatura não encontrada.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php' ?>
        </div>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
<script>
function initPad(canvasId){
  var c=document.getElementById(canvasId); var ctx=c.getContext('2d');
  ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height);
  ctx.strokeStyle='#000'; ctx.lineWidth=3; ctx.lineCap='round';
  var drawing=false;
  function pos(e){
    var r=c.getBoundingClientRect();
    var x,y; if(e.touches&&e.touches[0]){x=e.touches[0].clientX;y=e.touches[0].clientY;} else {x=e.clientX;y=e.clientY;}
    var scaleX=c.width/r.width, scaleY=c.height/r.height;
    return {x:(x-r.left)*scaleX, y:(y-r.top)*scaleY};
  }
  function start(e){ drawing=true; var p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); e.preventDefault(); }
  function move(e){ if(!drawing) return; var p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); e.preventDefault(); }
  function end(e){ drawing=false; e.preventDefault(); }
  c.addEventListener('mousedown',start); c.addEventListener('mousemove',move); window.addEventListener('mouseup',end);
  c.addEventListener('touchstart',start,{passive:false}); c.addEventListener('touchmove',move,{passive:false}); c.addEventListener('touchend',end,{passive:false});
}
function clearPad(id){ var c=document.getElementById(id); var ctx=c.getContext('2d'); ctx.clearRect(0,0,c.width,c.height); ctx.fillStyle='#fff'; ctx.fillRect(0,0,c.width,c.height); ctx.strokeStyle='#000'; }
function submitSign(){
  var colab=document.getElementById('padColab').toDataURL('image/png');
  document.getElementById('colab_sign').value=colab;
  document.getElementById('formAssinar').submit();
}
initPad('padColab');
 
</script>
</body>
</html>
