<?php include 'partials/main.php' ?>
<?php
if (!$_SESSION['user'] || !isset($_SESSION['user'])) { header('Location: auth-signin.php'); exit(); }
$dir = __DIR__ . '/assets/images/assinaturas';
$files = [];
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f==='.'||$f==='..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['png','jpg','jpeg','gif'])) $files[] = $f;
    }
    usort($files, function($a,$b) use($dir){ return filemtime($dir.'/'.$b) <=> filemtime($dir.'/'.$a); });
}
$items = [];
$i = 1;
foreach ($files as $f) { $items[] = ['nome'=>'Assinatura '.$i, 'url'=>'assets/images/assinaturas/'.$f]; $i++; }
?>
<head>
    <?php $subTitle = 'Assinaturas antigas'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Assinaturas antigas'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card"><div class="card-body">
                        <h4 class="card-title">Assinaturas antigas</h4>
                        <div class="row g-3">
                            <?php if (empty($items)): ?>
                                <div class="col-12 text-muted">Nenhuma assinatura encontrada.</div>
                            <?php else: foreach ($items as $it): ?>
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="border rounded p-2 h-100 d-flex flex-column">
                                        <div class="ratio ratio-1x1 mb-2"><img src="<?php echo htmlspecialchars($it['url']) ?>" class="img-fluid rounded" alt=""></div>
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <span class="fw-medium"><?php echo htmlspecialchars($it['nome']) ?></span>
                                            <a href="<?php echo htmlspecialchars($it['url']) ?>" target="_blank" class="btn btn-sm btn-soft-primary">Abrir</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
</body>
</html>
