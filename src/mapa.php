<?php include 'partials/main.php' ?>

<?php
if (!$_SESSION['user'] || !isset($_SESSION['user'])) {
    header('Location: auth-signin.php');
    exit();
}
require_once 'services/store.php';
$items = equipamentosList();
$dbSpots = mapHotspotsList();
$hotspots = is_array($dbSpots) ? $dbSpots : [];
$mapPath = 'assets/images/vila.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_spots') {
    $raw = $_POST['spots'] ?? '[]';
    $spots = json_decode($raw, true);
    $ok = mapHotspotsReplace(is_array($spots) ? $spots : []);
    header('Content-Type: application/json');
    echo json_encode(['ok' => $ok]);
    exit;
}
?>

<head>
    <?php $subTitle = "Mapa"; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>

<body>
    <div class="wrapper">
        <?php $subTitle = "Mapa"; include 'partials/topbar.php'; ?>
        <?php include 'partials/main-nav.php'; ?>

        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Mapa da Vila</h5>
                                <style>
                                    .map-zoom-container{position:relative;width:100%;height:100vh;overflow:hidden;border-radius:0;margin:0;padding:0}
                                    .map-inner{position:absolute;left:0;top:0;width:100%;height:100%;transform-origin:50% 50%;transition:transform .25s ease;}
                                    .map-img{display:block;width:100%;height:100%;object-fit:cover}
                                    .map-spot{position:absolute;transform:translate(-50%,-50%);} .map-spot .btn{--bs-btn-padding-y:.25rem;--bs-btn-padding-x:.5rem;--bs-btn-font-size:.75rem}
                                    .editing .map-spot{cursor:move}
                                    .map-tools{position:absolute;top:8px;left:8px;z-index:5;display:flex;gap:.25rem}
                                    .card-body{padding:0}
                                    .map-menu{position:absolute;z-index:10;background:#f8f9fa;border:1px solid rgba(0,0,0,.1);border-radius:.375rem;box-shadow:0 4px 14px rgba(0,0,0,.2);display:flex;flex-direction:column;overflow:hidden;min-width:160px}
                                    .map-menu-btn{display:block;padding:.5rem .6rem;background:transparent;border:0;text-align:left;width:100%;color:#212529}
                                    .map-menu-btn + .map-menu-btn{border-top:1px solid rgba(0,0,0,.08)}
                                    .map-menu-btn:hover{background:rgba(0,0,0,.06)}
                                </style>
                                <div id="vila3d" class="map-zoom-container">
                                    <div class="map-inner" id="mapInner">
                                        <img src="<?php echo $mapPath ?>" alt="Mapa do Costão" class="map-img" id="mapImg">
                                        <div class="map-tools" id="mapTools">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-light" id="btnMapEdit">Editar</button>
                                                <button type="button" class="btn btn-sm btn-outline-light" id="btnMapSave">Salvar</button>
                                                <button type="button" class="btn btn-sm btn-outline-light" id="btnMapCreate">Criar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

            <div class="modal fade" id="mapNameModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nome do ponto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="form-control" id="mapNameInput" placeholder="Novo ponto">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="mapNameOk">OK</button>
                            <button type="button" class="btn btn-light" id="mapNameCancel" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="mapDeleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar exclusão</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="password" class="form-control" id="mapDeleteInput" placeholder="Senha">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" id="mapDeleteOk">Excluir</button>
                            <button type="button" class="btn btn-light" id="mapDeleteCancel" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php' ?>
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
    (function(){
      var inner=document.getElementById('mapInner');
      var cont=document.getElementById('vila3d');
      function adjustMapHeight(){ if(!cont) return; var top=cont.getBoundingClientRect().top; var h=Math.max(240, window.innerHeight-top); cont.style.height=h+'px'; }
      window.addEventListener('resize', adjustMapHeight);
      adjustMapHeight();
      var editing=false; var creating=false; var spots=[]; var draggingSpot=false;
      var nameModalEl=document.getElementById('mapNameModal');
      var nameInput=document.getElementById('mapNameInput');
      var nameOk=document.getElementById('mapNameOk');
      var nameCancel=document.getElementById('mapNameCancel');
      var nameModal=new bootstrap.Modal(nameModalEl);
      var delModalEl=document.getElementById('mapDeleteModal');
      var delInput=document.getElementById('mapDeleteInput');
      var delOk=document.getElementById('mapDeleteOk');
      var delCancel=document.getElementById('mapDeleteCancel');
      var delModal=new bootstrap.Modal(delModalEl);
      var PASS_HASH='3be84471f5d00eefce3b4c43ce5d86366a1bb496e95563b4f90ba924d7c3a25e';
      async function sha256Hex(s){ var d=await crypto.subtle.digest('SHA-256', new TextEncoder().encode(s)); var a=Array.from(new Uint8Array(d)); return a.map(function(b){return b.toString(16).padStart(2,'0');}).join(''); }
      function askName(def,cb){
        nameInput.value=def||'';
        nameModal.show();
        setTimeout(function(){ try{ nameInput.focus(); nameInput.select(); }catch(e){} }, 120);
        function onOk(){ var v=nameInput.value; cleanup(); nameModal.hide(); cb(v); }
        function onCancel(){ cleanup(); cb(null); }
        function onKey(e){ if(e.key==='Enter'){ onOk(); } }
        function cleanup(){ nameOk.removeEventListener('click',onOk); nameCancel.removeEventListener('click',onCancel); nameModalEl.removeEventListener('keydown',onKey); }
        nameOk.addEventListener('click',onOk);
        nameCancel.addEventListener('click',onCancel);
        nameModalEl.addEventListener('keydown',onKey);
      }
      function askDelete(cb){
        delInput.value=''; delInput.classList.remove('is-invalid');
        delModal.show();
        setTimeout(function(){ try{ delInput.focus(); }catch(e){} },120);
        async function onOk(){ var v=delInput.value; var h=await sha256Hex(v); if(h===PASS_HASH){ cleanup(); delModal.hide(); cb(true); } else { delInput.classList.add('is-invalid'); } }
        function onCancel(){ cleanup(); cb(false); }
        function onKey(e){ if(e.key==='Enter'){ onOk(); } }
        function cleanup(){ delOk.removeEventListener('click',onOk); delCancel.removeEventListener('click',onCancel); delModalEl.removeEventListener('keydown',onKey); }
        delOk.addEventListener('click',onOk);
        delCancel.addEventListener('click',onCancel);
        delModalEl.addEventListener('keydown',onKey);
      }
      var ctxMenu=null;
      function hideMenu(){ if(ctxMenu){ ctxMenu.remove(); ctxMenu=null; } }
      function showMenu(px,py,idx){
        hideMenu();
        ctxMenu=document.createElement('div');
        ctxMenu.className='map-menu';
        ctxMenu.style.left=px+'px';
        ctxMenu.style.top=py+'px';
        var b1=document.createElement('button'); b1.className='map-menu-btn fw-semibold'; b1.textContent='Renomear';
        b1.onclick=function(){ askName(spots[idx].nome||'', function(nm){ if(nm!==null){ spots[idx].nome=nm; render(); } hideMenu(); }); };
        var b2=document.createElement('button'); b2.className='map-menu-btn text-danger'; b2.textContent='Excluir';
        b2.onclick=function(){ askDelete(function(ok){ if(!ok) return; spots.splice(idx,1); render(); hideMenu(); }); };
        ctxMenu.appendChild(b1); ctxMenu.appendChild(b2);
        inner.appendChild(ctxMenu);
      }
      document.addEventListener('click',function(e){ if(ctxMenu && !e.target.closest('.map-menu')) hideMenu(); });
      document.addEventListener('keydown',function(e){ if(e.key==='Escape') hideMenu(); });
      function render(){
        Array.prototype.slice.call(inner.querySelectorAll('.map-spot')).forEach(function(el){ el.parentNode.removeChild(el); });
        spots.forEach(function(s,idx){
          var el=document.createElement('div'); el.className='map-spot'; el.style.left=s.x+'%'; el.style.top=s.y+'%';
          var btn=document.createElement('button'); btn.className='btn btn-soft-primary btn-sm'; btn.textContent=s.nome||'';
          if(!editing){ btn.addEventListener('click',function(){ window.showPredio(s.nome||''); }); }
          el.appendChild(btn); inner.appendChild(el);
          if(editing){ makeDraggable(el,idx,btn); }
        });
      }
      function makeDraggable(el,index,btn){
        var dragging=false;
        function posFromMouse(ev){ var rect=inner.getBoundingClientRect(); var x=(ev.clientX-rect.left)/rect.width*100; var y=(ev.clientY-rect.top)/rect.height*100; return {x:Math.max(0,Math.min(100,x)), y:Math.max(0,Math.min(100,y))}; }
        function posFromTouch(ev){ var t=ev.touches&&ev.touches[0]; if(!t) return null; var rect=inner.getBoundingClientRect(); var x=(t.clientX-rect.left)/rect.width*100; var y=(t.clientY-rect.top)/rect.height*100; return {x:Math.max(0,Math.min(100,x)), y:Math.max(0,Math.min(100,y))}; }
        function start(ev){ dragging=true; draggingSpot=true; ev.preventDefault(); }
        function move(ev){ if(!dragging||!editing) return; var p = ev.touches?posFromTouch(ev):posFromMouse(ev); if(!p) return; spots[index].x=p.x; spots[index].y=p.y; el.style.left=p.x+'%'; el.style.top=p.y+'%'; }
        function end(){ dragging=false; draggingSpot=false; }
        el.addEventListener('mousedown',start); document.addEventListener('mousemove',move); document.addEventListener('mouseup',end);
        
        el.addEventListener('touchstart',start,{passive:false}); document.addEventListener('touchmove',move,{passive:false}); document.addEventListener('touchend',end,{passive:false});
        el.oncontextmenu=function(ev){ if(editing){ ev.preventDefault(); var rect=inner.getBoundingClientRect(); showMenu(ev.clientX-rect.left, ev.clientY-rect.top, index); } };
      }
      async function save(){
        var ok=false;
        try{
          var resp = await fetch('mapa.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=save_spots&spots='+encodeURIComponent(JSON.stringify(spots)) });
          var t = await resp.text();
          try{ var j=JSON.parse(t); ok=!!(j&&j.ok); }catch(e){ ok = resp.ok; }
        }catch(e){}
        try{ localStorage.setItem('mapHotspots', JSON.stringify(spots)); }catch(e){}
        if(bEdit) bEdit.classList.remove('active'); editing=false; inner.classList.remove('editing');
      }
      function load(){
        try{ var raw=localStorage.getItem('mapHotspots'); if(raw){ spots=JSON.parse(raw)||[]; } else { spots=(window.MAP_SPOTS_DEFAULT||[]); } }catch(e){ spots=(window.MAP_SPOTS_DEFAULT||[]); }
        render();
      }
      inner.addEventListener('click',function(ev){
        if(!creating) return;
        if(draggingSpot) return;
        if(ev.target && ev.target.closest('.map-spot')) return;
        var rect=inner.getBoundingClientRect();
        var x=(ev.clientX-rect.left)/rect.width*100; var y=(ev.clientY-rect.top)/rect.height*100;
        askName('Novo ponto', function(nm){ if(nm===null) return; spots.push({nome:nm||'Ponto',x:x,y:y}); render(); creating=false; if(bCreate){ bCreate.classList.remove('active'); } });
      });
      var bEdit=document.getElementById('btnMapEdit'); var bSave=document.getElementById('btnMapSave'); var bCreate=document.getElementById('btnMapCreate');
      function setEdit(on){ editing=on; if(bEdit){ bEdit.classList.toggle('active', on); } inner.classList.toggle('editing', on); render(); }
      if(bEdit) bEdit.addEventListener('click',function(){ setEdit(!editing); });
      if(bSave) bSave.addEventListener('click',function(){ save(); });
      if(bCreate) bCreate.addEventListener('click',function(){ creating=!creating; bCreate.classList.toggle('active', creating); });
      window.MAP_SPOTS_DEFAULT = <?php echo json_encode($hotspots, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
      load();
    })();
    </script>
</body>

</html>
