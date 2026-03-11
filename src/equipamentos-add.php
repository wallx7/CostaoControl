<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) {
        $erro = 'Token inválido';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $so = trim($_POST['so'] ?? '');
        $cpu = trim($_POST['cpu'] ?? '');
        $ram = trim($_POST['ram'] ?? '');
        $armazenamento = trim($_POST['armazenamento'] ?? '');
        $ram = preg_replace('/\D/', '', $ram);
        $armazenamento = preg_replace('/\D/', '', $armazenamento);
        $fornecedor = trim($_POST['fornecedor'] ?? '');
        $numeroSerie = trim($_POST['numero_serie'] ?? '');
        $localizacao = trim($_POST['localizacao'] ?? '');
        $status = trim($_POST['status'] ?? 'Em uso');
        $preco = trim($_POST['preco'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $dataCompra = trim($_POST['data_compra'] ?? '');
        $garantia = trim($_POST['garantia'] ?? '');
        $patrimonio = trim($_POST['patrimonio'] ?? '');
        if ($nome === '' || $categoria === '') {
            $erro = 'Preencha os campos obrigatórios (Nome e Categoria)';
        } else {
            foreach (equipamentosList() as $e) {
                if ($numeroSerie !== '' && ($e['numero_serie'] ?? '') === $numeroSerie) { $erro = 'Número de série já cadastrado'; break; }
                if ($patrimonio !== '' && ($e['patrimonio'] ?? '') === $patrimonio) { $erro = 'Patrimônio já cadastrado'; break; }
            }
            if (!$erro) {
                $id = equipamentoAdd([
                    'nome' => $nome,
                    'tipo' => $categoria,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'especificacoes' => json_encode(['cpu'=>$cpu,'ram'=>$ram,'disco'=>$armazenamento,'os'=>$so,'desc'=>$descricao,'fornec'=>$fornecedor]),
                    'fornecedor' => $fornecedor,
                    'numero_serie' => $numeroSerie,
                    'patrimonio' => $patrimonio,
                    'localizacao' => $localizacao,
                    'status' => $status,
                    'preco' => $preco,
                    'descricao' => $descricao,
                    'data_aquisicao' => $dataCompra,
                    'garantia_fim' => $garantia
                ]);
                $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
                logActivity('equipamentoAdd', 'equipamentos', (int)$id, 'nome=' . $nome . ', numero_serie=' . $numeroSerie . ', patrimonio=' . $patrimonio);
                $next = $_POST['next_action'] ?? '';
                if ($next === 'termo') {
                    header('Location: termo-criar.php?equip_id='.(int)$id);
                } else {
                    header('Location: inventario-dashboard.php');
                }
                exit;
            }
        }
    }
}
// Carregar categorias para o formulário
$categorias = [];
try { $categorias = categoriasList(); } catch (Exception $e) { $categorias = []; }
if (!is_array($categorias)) { $categorias = []; }
$categoriaAtual = $_POST['categoria'] ?? '';
?>
<head>
    <?php $subTitle = 'Adicionar Equipamento'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
    <div class="wrapper">
        <?php $subTitle = 'Adicionar Equipamento'; include 'partials/topbar.php'; ?>
        <?php include 'partials/main-nav.php'; ?>
        <div class="page-content">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-xl-3 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <img src="assets/images/product/p-1.png" alt="" class="img-fluid rounded bg-light">
                                <div class="mt-3">
                                    <h4>Ativo de TI <span class="fs-14 text-muted ms-1">(Inventário)</span></h4>
                                    <div class="mt-2">
                                        <label class="form-label">Nome do Ativo</label>
                                        <div class="form-control bg-light-subtle"></div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label">Local</label>
                                        <div class="form-control bg-light-subtle"></div>
                                    </div>
                                    <h5 class="text-dark fw-medium mt-3">Preço :</h5>
                                    <h4 id="preco-left" class="fw-semibold text-dark mt-2">R$ 0,00</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9 col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Adicionar Foto do Produto</h4>
                            </div>
                            <div class="card-body">
                                <div class="dropzone bg-light-subtle py-5">
                                    <div class="fallback">
                                        <input name="file" type="file" multiple="multiple">
                                    </div>
                                    <div class="dz-message needsclick">
                                        <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                        <h3 class="mt-4">Envie suas imagens aqui, ou <span class="text-primary">clique para selecionar</span></h3>
                                        <span class="text-muted fs-13">Recomenda-se 1600 x 1200 (4:3). Permitidos: PNG, JPG e GIF</span>
                                    </div>
                                </div>
                                <ul class="list-unstyled mb-0" id="dropzone-preview">
                                    <li class="mt-2" id="dropzone-preview-list">
                                        <div class="border rounded">
                                            <div class="d-flex p-2">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar-sm bg-light rounded">
                                                        <img data-dz-thumbnail class="img-fluid rounded d-block" src="#" alt="Dropzone-Image" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="pt-1">
                                                        <h5 class="fs-14 mb-1" data-dz-name></h5>
                                                        <p class="fs-13 text-muted mb-0" data-dz-size></p>
                                                        <strong class="error text-primary" data-dz-errormessage></strong>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ms-3">
                                                    <button data-dz-remove class="btn btn-sm btn-primary">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Informações de TI</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($erro): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($erro) ?></div>
                                <?php endif; ?>
                                <form method="post">
                                    <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                    <input type="hidden" name="next_action" id="next_action" value="">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="product-name" class="form-label">Nome do Ativo</label>
                                                <input type="text" id="product-name" name="nome" class="form-control" placeholder="Ex.: Notebook Dell" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="product-categories" class="form-label">Categoria do Ativo</label>
                                            <select class="form-control" id="product-categories" name="categoria" data-choices data-placeholder="Selecione a categoria" required>
                                                <option value="">Selecione</option>
                                                <?php
                                                if (empty($categorias)) {
                                                    $fallback = ['Desktop','Notebook','Celular','Tablet'];
                                                    foreach ($fallback as $nome) {
                                                        $sel = ($categoriaAtual === $nome) ? 'selected' : '';
                                                        echo '<option value="' . htmlspecialchars($nome) . '" ' . $sel . '>' . htmlspecialchars($nome) . '</option>';
                                                    }
                                                } else {
                                                    foreach ($categorias as $c) {
                                                        $nome = is_array($c) ? ($c['nome'] ?? '') : (string)$c;
                                                        if ($nome === '') continue;
                                                        $sel = ($categoriaAtual === $nome) ? 'selected' : '';
                                                        echo '<option value="' . htmlspecialchars($nome) . '" ' . $sel . '>' . htmlspecialchars($nome) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label for="fabricante" class="form-label">Fabricante</label>
                                                <input type="text" id="fabricante" name="marca" class="form-control" placeholder="Ex.: Dell, HP, Lenovo">
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="mb-3">
                                                <label for="modelo" class="form-label">Modelo</label>
                                                <input type="text" id="modelo" name="modelo" class="form-control" placeholder="Ex.: Latitude 5420">
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <label for="sistema-operacional" class="form-label">Sistema Operacional</label>
                                            <select class="form-control" id="sistema-operacional" name="so" data-choices data-placeholder="Selecione o SO">
                                                <option value="">Selecione</option>
                                                <option value="Windows">Windows</option>
                                                <option value="macOS">macOS</option>
                                                <option value="Linux">Linux</option>
                                                <option value="Android">Android</option>
                                                <option value="iOS">iOS</option>
                                                <option value="Outro">Outro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                 <label for="cpu" class="form-label">Processador (CPU)</label>
                                                 <input type="text" id="cpu" name="cpu" class="form-control" placeholder="Ex.: Intel i5-1145G7">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                 <label for="ram" class="form-label">Memória RAM</label>
                                                 <input type="text" inputmode="numeric" pattern="\d+" title="Somente números" id="ram" name="ram" class="form-control" placeholder="Ex.: 16">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                 <label for="armazenamento" class="form-label">Armazenamento</label>
                                                 <input type="text" inputmode="numeric" pattern="\d+" title="Somente números" id="armazenamento" name="armazenamento" class="form-control" placeholder="Ex.: 512">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label for="fornecedor" class="form-label">Fornecedor</label>
                                                <input type="text" id="fornecedor" name="fornecedor" class="form-control" placeholder="Ex.: NDD, Meta, Dell Store">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label for="numero-serie" class="form-label">Número de Série</label>
                                                <input type="text" id="numero-serie" name="numero_serie" class="form-control" placeholder="Ex.: SN123456789">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label for="localizacao" class="form-label">Localização</label>
                                                <input type="text" id="localizacao" name="localizacao" class="form-control" placeholder="Ex.: Escritório TI / Sala 3">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <label for="status-ativo" class="form-label">Status</label>
                                            <select class="form-control" id="status-ativo" name="status" data-choices>
                                                <option value="Em uso" selected>Em uso</option>
                                                <option value="Em estoque">Em estoque</option>
                                                <option value="Manutenção">Manutenção</option>
                                                <option value="Descartado">Descartado</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="mb-3">
                                                <label for="preco" class="form-label">Preço</label>
                                                <input type="text" inputmode="numeric" id="preco" name="preco" class="form-control" placeholder="Ex.: 3.999,90">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Descrição</label>
                                                <textarea class="form-control bg-light-subtle" id="description" name="descricao" rows="7" placeholder="Descrição do ativo de TI"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Aquisição</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <label for="data-compra" class="form-label">Data de Compra</label>
                                                    <input type="date" id="data-compra" name="data_compra" class="form-control">
                                                </div>
                                                <div class="col-lg-4">
                                                    <label for="garantia" class="form-label">Garantia até</label>
                                                    <input type="date" id="garantia" name="garantia" class="form-control">
                                                </div>
                                                <div class="col-lg-4">
                                                    <label for="patrimonio" class="form-label">Tag Patrimônio</label>
                                                    <input type="text" id="patrimonio" name="patrimonio" class="form-control" placeholder="Ex.: PAT-000123">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-light mb-3 rounded">
                                        <div class="row justify-content-end g-2">
                                            <div class="col-lg-2">
                                                <button type="button" id="btn-salvar" class="btn btn-outline-secondary w-100">Salvar</button>
                                            </div>
                                            <div class="col-lg-2">
                                                <a href="inventario-dashboard.php" class="btn btn-primary w-100">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="modal fade" id="confirmTermoModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Criar termo agora?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Deseja criar e assinar o termo agora ou apenas salvar o equipamento?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" id="confirmTermoNo" class="btn btn-outline-secondary" data-bs-dismiss="modal">Só salvar</button>
                                                <button type="button" class="btn btn-primary" id="confirmTermoYes">Criar termo agora</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-danger">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title text-danger">Formulário incompleto</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <div class="d-flex flex-column align-items-center gap-2">
                                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                                        <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-48 text-danger icon-shake"></iconify-icon>
                                                    </div>
                                                    <div class="text-muted">Preencha os campos obrigatórios para continuar.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Entendi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include 'partials/footer.php' ?>
            </div>
        </div>
    </div>
    <?php include 'partials/vendor-scripts.php' ?>
    <script src="assets/js/pages/app-ecommerce-product.js"></script>
    <script src="assets/js/components/form-fileupload.js"></script>
    <script>
    (function(){
      var nameRight=document.getElementById('product-name');
      var locRight=document.getElementById('localizacao');
      var precoInput=document.getElementById('preco');
      var precoLeft=document.getElementById('preco-left');
      function formatNumberBR(digits){
        digits=(digits||'').replace(/\D/g,'');
        var value=parseInt(digits||'0',10)/100;
        return new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(value);
      }
      function formatBRLFromDigits(digits){
        var num=formatNumberBR(digits);
        return 'R$\u00a0'+num;
      }
      
      if(precoInput){
        var updatePrice=function(){
          var masked=formatNumberBR(precoInput.value);
          precoInput.value=masked;
          var formatted='R$\u00a0'+masked;
          if(precoLeft){precoLeft.textContent=formatted;}
        };
        updatePrice();
        precoInput.addEventListener('input',updatePrice);
        precoInput.addEventListener('blur',updatePrice);
      }
      var ram=document.getElementById('ram');
      var disco=document.getElementById('armazenamento');
      function digitsOnly(el){ if(!el) return; var apply=function(){ el.value=(el.value||'').replace(/\D/g,''); }; apply(); el.addEventListener('input',apply); el.addEventListener('blur',apply); }
      digitsOnly(ram); digitsOnly(disco);
      var salvar=document.getElementById('btn-salvar');
      var next=document.getElementById('next_action');
      var form=document.querySelector('form[method="post"]');
      if(salvar&&form&&next){
        salvar.addEventListener('click',function(){
          if(!form.checkValidity()){ var elErr=document.getElementById('errorModal'); var modalErr=new bootstrap.Modal(elErr); modalErr.show(); return; }
          var el=document.getElementById('confirmTermoModal');
          var yes=document.getElementById('confirmTermoYes');
          var modal=new bootstrap.Modal(el);
          var onlySaveBtn=document.getElementById('confirmTermoNo');
          yes.onclick=function(){ next.value='termo'; modal.hide(); form.submit(); };
          if(onlySaveBtn){ onlySaveBtn.addEventListener('click',function(){ next.value=''; modal.hide(); form.submit(); }); }
          modal.show();
        });
      }
    })();
    </script>
    <style>
    @keyframes shakeX{0%,100%{transform:translateX(0)}20%{transform:translateX(-6px)}40%{transform:translateX(6px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}
    .icon-shake{animation:shakeX .5s ease}
    </style>
</body>
</html>
