<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php
$items = equipamentosSystemList();
$total = equipamentosSystemCount();
$statusCounts = ['Em uso'=>0,'Em estoque'=>0,'Manutenção'=>0,'Descartado'=>0];
foreach ($items as $it) { $s = $it['status'] ?? 'Em uso'; if(isset($statusCounts[$s])) $statusCounts[$s]++; }
$porPredio = [];
foreach ($items as $it) { $loc = trim($it['localizacao'] ?? ''); if($loc==='') $loc='Sem localização'; $key = explode('/', $loc)[0]; $porPredio[$key] = ($porPredio[$key] ?? 0) + 1; }
?>
<head>
    <?php $subTitle = 'Dashboard Inventário'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Dashboard Inventário'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card"><div class="card-body"><h6 class="text-muted">Total de Equipamentos</h6><h3 class="mt-2"><?php echo $total ?></h3></div></div>
                </div>
                <div class="col-xl-3 col-md-6"><div class="card"><div class="card-body"><h6 class="text-muted">Em uso</h6><h3 class="mt-2"><?php echo $statusCounts['Em uso'] ?></h3></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="card"><div class="card-body"><h6 class="text-muted">Em estoque</h6><h3 class="mt-2"><?php echo $statusCounts['Em estoque'] ?></h3></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="card"><div class="card-body"><h6 class="text-muted">Manutenção</h6><h3 class="mt-2"><?php echo $statusCounts['Manutenção'] ?></h3></div></div></div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title">Mapa da Vila (Interativo)</h4></div>
                        <div class="card-body">
                            <div id="vila3d" class="position-relative" style="height:360px;background:#1f2937;border-radius:8px;">
                                <svg viewBox="0 0 600 360" width="100%" height="100%">
                                    <defs>
                                        <linearGradient id="roof" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#ef4444"/>
                                            <stop offset="100%" stop-color="#b91c1c"/>
                                        </linearGradient>
                                        <linearGradient id="wall" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#93c5fd"/>
                                            <stop offset="100%" stop-color="#3b82f6"/>
                                        </linearGradient>
                                    </defs>
                                    <g cursor="pointer" data-building="Prédio A" onclick="window.showPredio('Prédio A')">
                                        <polygon points="120,120 200,80 280,120 200,160" fill="url(#roof)"/>
                                        <polygon points="200,160 120,120 120,200 200,240 200,160" fill="url(#wall)" opacity="0.9"/>
                                        <polygon points="200,160 280,120 280,200 200,240 200,160" fill="#1e40af" opacity="0.9"/>
                                        <text x="200" y="260" text-anchor="middle" fill="#fff" font-size="14">Prédio A</text>
                                    </g>
                                    <g cursor="pointer" data-building="Prédio B" onclick="window.showPredio('Prédio B')">
                                        <polygon points="340,100 420,60 500,100 420,140" fill="url(#roof)"/>
                                        <polygon points="420,140 340,100 340,180 420,220 420,140" fill="url(#wall)" opacity="0.9"/>
                                        <polygon points="420,140 500,100 500,180 420,220 420,140" fill="#1e40af" opacity="0.9"/>
                                        <text x="420" y="240" text-anchor="middle" fill="#fff" font-size="14">Prédio B</text>
                                    </g>
                                    <g cursor="pointer" data-building="Datacenter" onclick="window.showPredio('Datacenter')">
                                        <rect x="80" y="220" width="140" height="80" fill="#0ea5e9"/>
                                        <rect x="85" y="225" width="35" height="30" fill="#111827"/>
                                        <rect x="125" y="225" width="35" height="30" fill="#111827"/>
                                        <rect x="165" y="225" width="35" height="30" fill="#111827"/>
                                        <text x="150" y="320" text-anchor="middle" fill="#fff" font-size="14">Datacenter</text>
                                    </g>
                                    <g cursor="pointer" data-building="Almoxarifado" onclick="window.showPredio('Almoxarifado')">
                                        <rect x="360" y="230" width="160" height="70" fill="#10b981"/>
                                        <text x="440" y="320" text-anchor="middle" fill="#fff" font-size="14">Almoxarifado</text>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title">Equipamentos por Prédio</h4></div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach($porPredio as $p=>$c): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($p) ?></span>
                                        <span class="badge bg-primary"><?php echo $c ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="predioModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="predioTitle">Prédio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="predioList"></div>
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
window.INVENTARIO = <?php echo json_encode($items, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
window.showPredio = function(nome){
  var list = (window.INVENTARIO||[]).filter(function(i){
    var loc = (i.localizacao||'');
    var predio = loc.split('/')[0];
    return predio===nome;
  });
  var html = '';
  if(list.length===0){ html = '<p class="text-muted">Nenhum equipamento neste prédio.</p>'; }
  else {
    html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Nome</th><th>Tipo</th><th>Status</th><th>Série</th></tr></thead><tbody>'+
      list.map(function(r){
        var st = r.status||'';
        return '<tr>'+
          '<td>#'+(r.id||'')+'</td>'+
          '<td>'+ (r.nome||'') +'</td>'+
          '<td>'+ (r.tipo||'') +'</td>'+
          '<td>'+ st +'</td>'+
          '<td>'+ (r.numero_serie||'') +'</td>'+
        '</tr>';
      }).join('')+
      '</tbody></table></div>';
  }
  document.getElementById('predioTitle').textContent = nome;
  document.getElementById('predioList').innerHTML = html;
  var m = new bootstrap.Modal(document.getElementById('predioModal'));
  m.show();
};
</script>
</body>
</html>
