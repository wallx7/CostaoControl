<?php include 'partials/main.php' ?>

<?php
if (!$_SESSION['user'] || !isset($_SESSION['user'])) {
    header('Location: auth-signin.php');
    exit();
}
require_once 'services/store.php';
$items = equipamentosSystemList();
$statusCounts = ['Em uso'=>0,'Em estoque'=>0,'Manutenção'=>0,'Descartado'=>0];
foreach ($items as $it) { $s = $it['status'] ?? 'Em uso'; if(isset($statusCounts[$s])) $statusCounts[$s]++; }
$total = equipamentosSystemCount();
$mapSpots = mapHotspotsList();
$porPredio = [];
$predios = [];
if (is_array($mapSpots)) { foreach ($mapSpots as $s) { $nm = trim($s['nome'] ?? ''); if ($nm!=='') { $predios[$nm] = true; $porPredio[$nm] = 0; } } }
foreach ($items as $it) {
    $loc = trim($it['localizacao'] ?? '');
    $key = explode('/', $loc)[0];
    if ($key!=='' && isset($predios[$key])) { $porPredio[$key] = ($porPredio[$key] ?? 0) + 1; }
}
$recentItems = array_slice($items, 0, 5);
$maintenanceRaw = [];
foreach ($items as $it) {
    $cl = checklistGet($it['id']);
    $sch = $cl['scheduled_at'] ?? null;
    if ($sch) { $maintenanceRaw[] = ['nome'=>$it['nome'] ?? '', 'quando'=>$sch]; }
}
usort($maintenanceRaw, function($a,$b){ return strtotime($a['quando']) <=> strtotime($b['quando']); });
$maintenance = array_slice($maintenanceRaw, 0, 5);
?>

<head>
     <?php
    $subTitle = "Painel";
    include 'partials/title-meta.php'; ?>

       <?php include 'partials/head-css.php' ?>
</head>

<body>

     <!-- START Wrapper -->
     <div class="wrapper">

          <?php 
    $subTitle = "Bem-vindo!";
    include 'partials/topbar.php'; ?>
<?php include 'partials/main-nav.php'; ?>

          <!-- ==================================================== -->
          <!-- Start right Content here -->
          <!-- ==================================================== -->
          <div class="page-content">

               <!-- Start Container Fluid -->
               <div class="container-fluid">

                    <!-- Start here.... -->
                    <div class="row">
                        

                        <div class="col-xxl-12">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                             <h4 class="card-title">Desempenho</h4>
                                             <div>
                                                  <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#chartExpandModal" title="Ampliar gráfico"><i class="bx bx-fullscreen"></i></button>
                                                  <button type="button" class="btn btn-sm btn-outline-light" id="chart-dia">Dia</button>
                                                  <button type="button" class="btn btn-sm btn-outline-light active" id="chart-mes">Mês</button>
                                             </div>
                                        </div> <!-- end card-title-->

                                        <div dir="ltr">
                                             <div id="dash-performance-chart" class="apex-charts"></div>
                                        </div>
                                   </div> <!-- end card body -->
                              </div> <!-- end card -->
     </div> <!-- end col -->
     </div> <!-- end row -->

                    <!-- Modal: Expandir Gráfico -->
                    <div class="modal fade" id="chartExpandModal" tabindex="-1" aria-hidden="true">
                         <div class="modal-dialog modal-dialog-centered modal-xl">
                              <div class="modal-content">
                                   <div class="modal-header">
                                        <h5 class="modal-title">Desempenho</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                   </div>
                                   <div class="modal-body p-2">
                                        <div id="dash-performance-chart-expanded" class="apex-charts"></div>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <div class="row">
                         <div class="col-lg-4">
                              <div class="card uniform-card">
                                  <div class="card-body">
                                       <style>.uniform-card{height:560px}.uniform-card .card-body{height:100%}</style>
                                       <style>.dist-container{height:520px;overflow:hidden}</style>
                                       <div class="dist-container">
                                           <h5 class="card-title">Distribuição por Tipo</h5>
                                           <div id="conversions" class="apex-charts mb-2 mt-n2"></div>
                                           <div class="row text-center">
                                                <div class="col-6">
                                                     <p class="text-muted mb-2">Esta semana</p>
                                                     <h3 id="weekThis" class="text-dark mb-3">0</h3>
                                                </div>
                                                <div class="col-6">
                                                     <p class="text-muted mb-2">Semana passada</p>
                                                     <h3 id="weekPrev" class="text-dark mb-3">0</h3>
                                                </div>
                                           </div>
                                           <div class="text-center">
                                                <button type="button" class="btn btn-light shadow-none w-100">Ver detalhes</button>
                                           </div>
                                       </div>
                                  </div>
                              </div>
                         </div> <!-- end left chart card -->

                        <div class="col-lg-4">
                             <div class="card uniform-card">
                                 <div class="card-body">
                                       <h5 class="card-title">Itens mais caros</h5>
                                       <style>
                                           .rank-container{height:520px;overflow:hidden}
                                       </style>
                                       <?php
                                           $sorted = $items;
                                           usort($sorted, function($a,$b){ $va=(float)($a['valor']??0); $vb=(float)($b['valor']??0); return $vb<=>$va; });
                                           $topCaros = array_slice($sorted, 0, 5);
                                       ?>
                                       <div class="mt-1 rank-container">
                                           <div class="table-responsive">
                                               <table class="table table-sm mb-0">
                                                   <thead>
                                                       <tr>
                                                           <th>Nome</th>
                                                           <th>Valor</th>
                                                           <th>Status</th>
                                                           <th>Localização</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody>
                                                       <?php if (empty($topCaros)): ?>
                                                            <tr><td colspan="4" class="text-muted">Sem dados</td></tr>
                                                       <?php else: foreach ($topCaros as $e): ?>
                                                           <tr>
                                                               <td><?php echo htmlspecialchars($e['nome']??'') ?></td>
                                                               <td><?php $v=(float)($e['valor']??0); echo 'R$ '.number_format($v,2,',','.'); ?></td>
                                                               <td><?php echo htmlspecialchars($e['status']??'') ?></td>
                                                               <td><?php echo htmlspecialchars($e['localizacao']??'') ?></td>
                                                           </tr>
                                                       <?php endforeach; endif; ?>
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                       
                                  </div>
                             </div> <!-- end card-->
                         </div> <!-- end col -->

                         <div class="col-lg-4">
                              <div class="card card-height-100">
                                   <div class="card-header d-flex align-items-center justify-content-between gap-2">
                                        <h4 class="card-title flex-grow-1">Equipamentos por Prédio</h4>
                    <a href="mapa.php" class="btn btn-sm btn-soft-primary">Ver mapa</a>
                                   </div>
                                   <div class="table-responsive">
                                        <table class="table table-hover table-nowrap table-centered m-0">
                                             <thead class="bg-light bg-opacity-50">
                                                  <tr>
                                                       <th class="text-muted ps-3">Prédio</th>
                                                       <th class="text-muted">Quantidade</th>
                                                       <th class="text-muted">Ação</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php foreach ($porPredio as $p=>$c): ?>
                                                  <tr>
                                                       <td class="ps-3"><?php echo htmlspecialchars($p) ?></td>
                                                       <td><span class="badge bg-primary"><?php echo $c ?></span></td>
                                                       <td><a href="#" class="btn btn-light btn-sm" onclick="window.showPredio('<?php echo htmlspecialchars($p, ENT_QUOTES) ?>')">Ver</a></td>
                                                  </tr>
                                                  <?php endforeach; ?>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div> <!-- end col -->

                         <div class="col-xl-4 d-none">
                              <div class="card">
                                   <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="card-title">Recent Transactions</h4>
                                        <div>
                                             <a href="#!" class="btn btn-sm btn-primary">
                                                  <i class="bx bx-plus me-1"></i>Add
                                             </a>
                                        </div>
                                   </div> <!-- end card-header-->
                                   <div class="card-body p-0">
                                        <div class="px-3" data-simplebar style="max-height: 398px;">
                                             <table class="table table-hover mb-0 table-centered">
                                                  <tbody>
                                                       <tr>
                                                            <td>24 April, 2024</td>
                                                            <td>$120.55</td>
                                                            <td><span class="badge bg-success">Cr</span></td>
                                                            <td>Commisions </td>
                                                       </tr>
                                                       <tr>
                                                            <td>24 April, 2024</td>
                                                            <td>$9.68</td>
                                                            <td><span class="badge bg-success">Cr</span></td>
                                                            <td>Affiliates </td>
                                                       </tr>
                                                       <tr>
                                                            <td>20 April, 2024</td>
                                                            <td>$105.22</td>
                                                            <td><span class="badge bg-danger">Dr</span></td>
                                                            <td>Grocery </td>
                                                       </tr>
                                                       <tr>
                                                            <td>18 April, 2024</td>
                                                            <td>$80.59</td>
                                                            <td><span class="badge bg-success">Cr</span></td>
                                                            <td>Refunds </td>
                                                       </tr>
                                                       <tr>
                                                            <td>18 April, 2024</td>
                                                            <td>$750.95</td>
                                                            <td><span class="badge bg-danger">Dr</span></td>
                                                            <td>Bill Payments </td>
                                                       </tr>
                                                       <tr>
                                                            <td>17 April, 2024</td>
                                                            <td>$455.62</td>
                                                            <td><span class="badge bg-danger">Dr</span></td>
                                                            <td>Electricity </td>
                                                       </tr>
                                                       <tr>
                                                            <td>17 April, 2024</td>
                                                            <td>$102.77</td>
                                                            <td><span class="badge bg-success">Cr</span></td>
                                                            <td>Interest </td>
                                                       </tr>
                                                       <tr>
                                                            <td>16 April, 2024</td>
                                                            <td>$79.49</td>
                                                            <td><span class="badge bg-success">Cr</span></td>
                                                            <td>Refunds </td>
                                                       </tr>
                                                       <tr>
                                                            <td>05 April, 2024</td>
                                                            <td>$980.00</td>
                                                            <td><span class="badge bg-danger">Dr</span></td>
                                                            <td>Shopping</td>
                                                       </tr>
                                                  </tbody>
                                             </table>
                                        </div>
                                   </div> <!-- end card body -->
                              </div> <!-- end card-->
                         </div> <!-- end col-->
                    </div> <!-- end row -->

                    

               </div>
               <!-- End Container Fluid -->

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
          <!-- ==================================================== -->
          <!-- End Page Content -->
          <!-- ==================================================== -->

     </div>
     <!-- END Wrapper -->

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
    // Mapa removido da home: nenhum script de mapa é necessário aqui
     (function(){
      var tiposMap = {};
      (window.INVENTARIO||[]).forEach(function(i){ var t=(i.tipo||'Outro'); tiposMap[t]=(tiposMap[t]||0)+1; });
      var tipos = Object.keys(tiposMap);
      var valores = tipos.map(function(k){return tiposMap[k];});
      if (document.querySelector('#conversions')) {
        var options={chart:{height:360,type:'donut'},series:valores,labels:tipos};
        try { new ApexCharts(document.querySelector('#conversions'),options).render(); } catch(e){}
      }
      var nowW=new Date(); var startThis=new Date(nowW); startThis.setDate(nowW.getDate()-6); startThis.setHours(0,0,0,0);
      var startPrev=new Date(nowW); startPrev.setDate(nowW.getDate()-13); startPrev.setHours(0,0,0,0);
      var endPrev=new Date(nowW); endPrev.setDate(nowW.getDate()-7); endPrev.setHours(23,59,59,999);
      var wT=0,wP=0; (window.INVENTARIO||[]).forEach(function(i){ var d=i.criado_em?new Date(i.criado_em):null; if(!d) return; if(d>=startThis && d<=nowW) wT++; else if(d>=startPrev && d<=endPrev) wP++; });
      var wt=document.getElementById('weekThis'); if(wt) wt.textContent=wT; var wp=document.getElementById('weekPrev'); if(wp) wp.textContent=wP;
       var perfEl=document.querySelector('#dash-performance-chart');
       var chartMode='mes';
       function z(n){ return ('0'+n).slice(-2); }
       function agg(mode){
         var items=window.INVENTARIO||[]; var now=new Date();
         if(mode==='dia'){
           var cats=[],data=[];
           for(var i=29;i>=0;i--){ var d=new Date(now); d.setDate(now.getDate()-i); var key=d.getFullYear()+'-'+z(d.getMonth()+1)+'-'+z(d.getDate());
             cats.push(z(d.getDate())+'/'+z(d.getMonth()+1)); var c=0; for(var k=0;k<items.length;k++){ var dt=items[k].criado_em?new Date(items[k].criado_em):null; if(!dt) continue; var ky=dt.getFullYear()+'-'+z(dt.getMonth()+1)+'-'+z(dt.getDate()); if(ky===key) c++; }
             data.push(c);
           }
           return {cats:cats,data:data};
         }
         var months=[],data=[],mNow=now.getMonth(),yNow=now.getFullYear(),names=['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
         for(var i=11;i>=0;i--){ var m=(mNow - i + 12)%12; var y=yNow - Math.floor((i - mNow + 12)/12); var c=0; for(var k=0;k<items.length;k++){ var dt=items[k].criado_em?new Date(items[k].criado_em):null; if(!dt) continue; if(dt.getFullYear()===y && dt.getMonth()===m) c++; } months.push(names[m]); data.push(c); }
         return {cats:months,data:data};
       }
       var cfg=agg(chartMode);
       var perfChart=null;
       if(perfEl){
        var perf={series:[{name:'Cadastrados',type:'bar',data:cfg.data}],chart:{height:560,type:'line',toolbar:{show:false}},xaxis:{categories:cfg.cats},colors:['#f9b931']};
         try{ perfChart=new ApexCharts(perfEl,perf); perfChart.render(); }catch(e){}
       }
       var bDia=document.getElementById('chart-dia'), bMes=document.getElementById('chart-mes');
       function setActive(btn){ [bDia,bMes].forEach(function(b){ if(!b) return; b.classList.remove('active'); }); if(btn) btn.classList.add('active'); }
       function updateMode(mode,btn){ chartMode=mode; var r=agg(mode); try{ perfChart.updateOptions({xaxis:{categories:r.cats}}); perfChart.updateSeries([{name:'Cadastrados',type:'bar',data:r.data}]); }catch(e){} setActive(btn); }
       if(bDia) bDia.addEventListener('click',function(){ updateMode('dia',bDia); });
       if(bMes) bMes.addEventListener('click',function(){ updateMode('mes',bMes); });
       var expandedChart=null; var modalEl=document.getElementById('chartExpandModal');
       if(modalEl){
         modalEl.addEventListener('shown.bs.modal',function(){
           var target=document.querySelector('#dash-performance-chart-expanded');
           if(!target) return;
           var r=agg(chartMode);
           var perfBig={series:[{name:'Cadastrados',type:'bar',data:r.data}],chart:{height:560,type:'line',toolbar:{show:false}},xaxis:{categories:r.cats},colors:['#f9b931']};
           try{expandedChart=new ApexCharts(target,perfBig);expandedChart.render();}catch(e){}
         });
         modalEl.addEventListener('hidden.bs.modal',function(){ try{ if(expandedChart){ expandedChart.destroy(); expandedChart=null; } }catch(e){} });
       }
     })();
    (function(){
      var tips=[
        'Você pode adicionar ou mover vilas no mapa.',
        'Acesse “Ver mapa” para gerenciar prédios.',
        'Use a busca para encontrar equipamentos rapidamente.',
        'Crie termos e envie por e-mail para assinatura.'
      ];
      var el=document.querySelector('#tutorial-text');
      var i=0;
      function show(){ if(!el) return; el.textContent=tips[i]; i=(i+1)%tips.length; }
      show();
      setInterval(show,8000);
    })();
    </script>
    
</body>

</html>
