<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php
$items = equipamentosList();
$seriesMes = array_fill(0,12,0);
foreach ($items as $it) {
    $dt = $it['criado_em'] ?? null;
    if ($dt) {
        $ts = strtotime($dt);
        if ($ts) { $m = (int)date('n', $ts) - 1; if ($m>=0 && $m<12) $seriesMes[$m]++; }
    }
}
?>
<head>
    <?php $subTitle = 'Desempenho'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Desempenho'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Desempenho</h4>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-light">Tudo</button>
                                    <button type="button" class="btn btn-sm btn-outline-light">1M</button>
                                    <button type="button" class="btn btn-sm btn-outline-light">6M</button>
                                    <button type="button" class="btn btn-sm btn-outline-light active">1A</button>
                                </div>
                            </div>
                            <div dir="ltr" class="mt-2">
                                <div id="dash-performance-chart" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'partials/footer.php' ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
<script>
var meses=['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
var seriesMes=<?php echo json_encode(array_values($seriesMes), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
(function(){
  var el=document.querySelector('#dash-performance-chart');
  if(!el) return;
  var perf={series:[{name:'Cadastrados',type:'bar',data:seriesMes}],chart:{height:420,type:'line',toolbar:{show:false}},xaxis:{categories:meses},colors:['#ff6c2f']};
  try{new ApexCharts(el,perf).render();}catch(e){}
})();
</script>
</body>
</html>