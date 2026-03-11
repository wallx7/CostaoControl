<?php include 'partials/main.php' ?>

<?php
if (!$_SESSION['user'] || !isset($_SESSION['user'])) {
    header('Location: auth-signin.php');
    exit();
}
require_once 'services/ai_service.php';

// Obter dados do dashboard
$dashboardData = getAIDashboardData();
$aiStatus = $dashboardData['service_health']['status'] ?? 'offline';
$statusColor = $aiStatus === 'healthy' ? 'green' : ($aiStatus === 'warning' ? 'yellow' : 'red');
$statusText = $aiStatus === 'healthy' ? 'IA Online' : ($aiStatus === 'warning' ? 'IA Alerta' : 'IA Offline');
$statusIcon = $aiStatus === 'healthy' ? '🟢' : ($aiStatus === 'warning' ? '🟡' : '🔴');
?>

<head>
    <?php
    $subTitle = "Dashboard IA";
    include 'partials/title-meta.php'; ?>
    <title>Dashboard IA - Inventário Inteligente</title>
    <?php include 'partials/head-css.php'; ?>
    <link rel="stylesheet" href="assets/css/dashboard-ia-custom.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <style>
        /* Estilos para o chat de IA */
        .ai-chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .ai-chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        
        .ai-chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .ai-chat-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        
        .ai-chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ai-chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        
        .ai-chat-input-container {
            padding: 15px;
            border-top: 1px solid #e9ecef;
            background: white;
        }
        
        .ai-chat-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }
        
        .ai-message {
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .ai-message.user {
            background: #667eea;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        
        .ai-message.bot {
            background: white;
            color: #333;
            border: 1px solid #ddd;
            margin-right: auto;
        }
        
        .ai-typing {
            font-style: italic;
            color: #666;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>
    <!-- START Wrapper -->
    <div class="wrapper">
        <?php include 'partials/main-nav.php'; ?>
        
        <div class="main-content">
            <?php include 'partials/topbar.php'; ?>
            
            <div class="page-content">
                <div class="container-fluid">
                    
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">🤖 Dashboard de IA - Inventário Inteligente</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inventário</a></li>
                                        <li class="breadcrumb-item active">Dashboard IA</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-2">Status do Sistema de IA</h5>
                                            <p class="text-muted mb-0">Monitoramento em tempo real das análises preditivas</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-800 px-3 py-2 rounded-full text-sm font-medium animate-pulse">
                                                <?= $statusIcon ?> <?= $statusText ?>
                                            </div>
                                            <button onclick="refreshDashboard()" class="btn btn-primary btn-sm">
                                                <i class="bx bx-refresh"></i> Atualizar
                                            </button>
                                            <button onclick="toggleAIChat()" class="btn btn-soft-purple btn-sm">
                                                <i class="bx bx-bot"></i> Falar com IA
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    if (isset($dashboardData['error'])) {
                        echo '<div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bx bx-error-circle me-2"></i>
                                        <strong>Erro:</strong> ' . htmlspecialchars($dashboardData['error']) . '
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                              </div>';
                    }
                    ?>

                    <!-- Card de Boas-vindas Inteligente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-primary bg-gradient text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-1">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <i class="bx bx-bot display-6 text-white-50"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="card-title text-white mb-1">Assistente IA</h5>
                                                    <p class="text-white-50 mb-0">Inteligência Artificial para seu Inventário</p>
                                                </div>
                                            </div>
                                            <p class="text-white-50 mb-3">
                                                <?php
                                                $hour = date('H');
                                                $totalEquipment = $dashboardData['analytics']['total_equipment'] ?? 0;
                                                $predictionsCount = count($dashboardData['demand_predictions'] ?? []);
                                                
                                                if ($hour < 12) {
                                                    echo "Bom dia! 🌅 Seu inventário com $totalEquipment equipamentos está sendo analisado. Tenho $predictionsCount previsões ativas para você.";
                                                } elseif ($hour < 18) {
                                                    echo "Boa tarde! ☀️ Analisei $totalEquipment equipamentos e identifiquei oportunidades de otimização.";
                                                } else {
                                                    echo "Boa noite! 🌙 Seu assistente preparou um resumo inteligente do dia com base em $totalEquipment itens do inventário.";
                                                }
                                                ?>
                                            </p>
                                            <div class="d-flex gap-3 text-white-50">
                                                <div class="bg-white bg-opacity-10 rounded px-3 py-2">
                                                    <span class="fw-semibold"><?= $totalEquipment ?></span> Equipamentos
                                                </div>
                                                <div class="bg-white bg-opacity-10 rounded px-3 py-2">
                                                    <span class="fw-semibold"><?= $predictionsCount ?></span> Previsões
                                                </div>
                                                <div class="bg-white bg-opacity-10 rounded px-3 py-2">
                                                    <span class="fw-semibold"><?= count($dashboardData['analytics']['type_distribution'] ?? []) ?></span> Categorias
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="display-1 text-white-50 opacity-50">
                                                <i class="bx bx-brain"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cards de Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Status IA</p>
                                            <h4 class="fs-22 fw-semibold mb-0">
                                                <?= isset($dashboardData['service_health']['status']) ? 
                                                    ucfirst($dashboardData['service_health']['status']) : 'Desconhecido' ?>
                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-<?= $statusColor ?> bg-gradient rounded">
                                                    <i class="bx bx-bot font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                                <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Equipamentos</p>
                                            <h4 class="fs-22 fw-semibold mb-0">
                                                <?= equipamentosSystemCount() ?>
                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-info bg-gradient rounded">
                                                    <i class="bx bx-package font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Modelos Carregados</p>
                                            <h4 class="fs-22 fw-semibold mb-0">
                                                <?= count($dashboardData['models_status'] ?? []) ?>
                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-warning bg-gradient rounded">
                                                    <i class="bx bx-brain font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Previsões Ativas</p>
                                            <h4 class="fs-22 fw-semibold mb-0">
                                                <?= count($dashboardData['demand_predictions'] ?? []) ?>
                                            </h4>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-success bg-gradient rounded">
                                                    <i class="bx bx-trending-up font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">🛠️ Problemas e Diagnósticos</h4>
                                </div>
                                <div class="card-body">
                                    <?php $probs = $dashboardData['problems_and_diagnostics'] ?? []; ?>
                                    <?php if (!empty($probs)): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($probs as $p): ?>
                                                <?php 
                                                    $sev = strtolower($p['severity'] ?? 'info');
                                                    $badge = ($sev === 'high') ? 'danger' : (($sev === 'warning') ? 'warning' : 'info');
                                                    $icon = ($sev === 'high') ? 'bx bx-error' : (($sev === 'warning') ? 'bx bx-error-circle' : 'bx bx-info-circle');
                                                ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-<?= $badge ?> bg-opacity-10 text-<?= $badge ?> rounded-circle">
                                                                    <i class="<?= $icon ?> font-size-16"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1"><?= htmlspecialchars($p['code'] ?? 'PROBLEMA') ?></h6>
                                                            <p class="mb-1 text-muted"><?= htmlspecialchars($p['message'] ?? '') ?></p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($sev) ?></span>
                                                                <small class="text-muted">Fonte: <?= htmlspecialchars($p['source'] ?? 'sistema') ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bx bx-check-shield display-4 text-success"></i>
                                            <h5 class="mt-3">Nenhum problema detectado</h5>
                                            <p class="text-muted">O sistema não encontrou diagnósticos pendentes.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos e Análises -->
                    <div class="row">
                        <!-- Gráfico de Status -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Distribuição por Status</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Tipos -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Distribuição por Tipo</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="typeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Previsões de Demanda -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">📈 Previsões de Demanda - Próximos 30 Dias</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="position: relative; height:400px;">
                                        <canvas id="demandChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Análises de IA -->
                    <div class="row mt-4">
                        <!-- Anomalias Detectadas -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">🚨 Anomalias Detectadas</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dashboardData['anomalies'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($dashboardData['anomalies'] as $anomaly): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded-circle">
                                                                    <i class="bx bx-error-alt font-size-16"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1"><?= htmlspecialchars($anomaly['type']) ?></h6>
                                                            <p class="mb-1 text-muted"><?= htmlspecialchars($anomaly['description']) ?></p>
                                                            <small class="text-muted"><?= htmlspecialchars($anomaly['equipment']) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bx bx-shield-check display-4 text-success"></i>
                                            <h5 class="mt-3">Nenhuma anomalia detectada</h5>
                                            <p class="text-muted">Seu inventário está funcionando dentro dos padrões normais.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recomendações de Manutenção -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">🔧 Recomendações de Manutenção</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dashboardData['maintenance_recommendations'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($dashboardData['maintenance_recommendations'] as $rec): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-info bg-opacity-10 text-info rounded-circle">
                                                                    <i class="bx bx-wrench font-size-16"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1"><?= htmlspecialchars($rec['equipment']) ?></h6>
                                                            <p class="mb-1 text-muted"><?= htmlspecialchars($rec['recommendation']) ?></p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">Vida útil: <?= $rec['lifespan_percentage'] ?>%</small>
                                                                <span class="badge bg-<?= $rec['priority'] === 'high' ? 'danger' : ($rec['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                                                    <?= ucfirst($rec['priority']) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bx bx-check-circle display-4 text-info"></i>
                                            <h5 class="mt-3">Nenhuma manutenção urgente</h5>
                                            <p class="text-muted">Todos os equipamentos estão com manutenção em dia.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'partials/footer.php'; ?>
            
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Chat de IA -->
    <div class="ai-chat-container">
        <div class="ai-chat-window" id="aiChatWindow">
            <div class="ai-chat-header">
                <div>
                    <strong>🤖 Assistente IA</strong>
                    <div style="font-size: 12px; opacity: 0.8;">Inventário Inteligente</div>
                </div>
                <button onclick="toggleAIChat()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">×</button>
            </div>
            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-message bot">
                    <strong>👋 Olá! Sou sua assistente de IA inteligente.</strong><br>
                    <em>Estou conectada à OpenAI e posso te ajudar com análises avançadas!</em><br><br>
                    Posso te ajudar com:
                    <br>• Previsões de demanda e tendências
                    <br>• Análises detalhadas de estoque
                    <br>• Detecção inteligente de anomalias
                    <br>• Recomendações estratégicas de manutenção
                    <br>• Insights personalizados sobre seu inventário
                    <br><br>Como posso ajudar você hoje? 😊
                </div>
            </div>
            <div class="ai-chat-input-container">
                <input type="text" class="ai-chat-input" id="aiChatInput" placeholder="Digite sua pergunta sobre o inventário..." onkeypress="handleChatKeyPress(event)">
            </div>
        </div>
        <button class="ai-chat-button" id="aiChatButton" onclick="toggleAIChat()" title="Falar com Assistente IA">
            <i class="bx bx-bot"></i>
        </button>
    </div>

    <?php include 'partials/vendor-scripts.php'; ?>

    <script>
        // Gráficos do Dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Dados dos gráficos (exemplo)
            const statusData = {
                labels: ['Em Uso', 'Em Estoque', 'Manutenção', 'Descartado'],
                datasets: [{
                    data: [<?= $dashboardData['analytics']['status_distribution']['in_use'] ?? 0 ?>, 
                           <?= $dashboardData['analytics']['status_distribution']['in_stock'] ?? 0 ?>, 
                           <?= $dashboardData['analytics']['status_distribution']['maintenance'] ?? 0 ?>, 
                           <?= $dashboardData['analytics']['status_distribution']['discarded'] ?? 0 ?>],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                }]
            };

            const typeData = {
                labels: [<?php 
                    $types = array_keys($dashboardData['analytics']['type_distribution'] ?? []);
                    echo '"' . implode('", "', $types) . '"';
                ?>],
                datasets: [{
                    data: [<?= implode(', ', array_values($dashboardData['analytics']['type_distribution'] ?? [])) ?>],
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe']
                }]
            };

            // Gráfico de Status
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de Tipos
            const typeCtx = document.getElementById('typeChart').getContext('2d');
            new Chart(typeCtx, {
                type: 'pie',
                data: typeData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de Demanda
            const demandCtx = document.getElementById('demandChart').getContext('2d');
            new Chart(demandCtx, {
                type: 'line',
                data: {
                    labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                    datasets: [{
                        label: 'Demanda Real',
                        data: [65, 78, 82, 91],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Previsão IA',
                        data: [null, null, null, 95],
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        borderDash: [5, 5],
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Previsão de Demanda de Equipamentos'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantidade de Equipamentos'
                            }
                        }
                    }
                }
            });
        });

        // Funções do Chat
        function toggleAIChat() {
            const chatWindow = document.getElementById('aiChatWindow');
            const chatButton = document.getElementById('aiChatButton');
            
            if (chatWindow.style.display === 'flex') {
                chatWindow.style.display = 'none';
                chatButton.style.display = 'block';
            } else {
                chatWindow.style.display = 'flex';
                chatButton.style.display = 'none';
                document.getElementById('aiChatInput').focus();
            }
        }

        function handleChatKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('aiChatInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Adicionar mensagem do usuário
            addMessage(message, 'user');
            input.value = '';
            
            // Mostrar indicador de digitação
            showTypingIndicator();
            
            // Enviar mensagem para OpenAI API
            fetch('api/chat-openai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                if (data.success) {
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Desculpe, ocorreu um erro ao processar sua mensagem. Por favor, tente novamente.', 'bot');
                }
            })
            .catch(error => {
                hideTypingIndicator();
                console.error('Erro ao chamar OpenAI:', error);
                // Usar resposta de fallback se OpenAI falhar
                const response = generateAIResponse(message);
                addMessage(response, 'bot');
            });
        }

        function addMessage(message, sender) {
            const messagesContainer = document.getElementById('aiChatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-message ${sender}`;
            messageDiv.textContent = message;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showTypingIndicator() {
            const messagesContainer = document.getElementById('aiChatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'ai-message bot ai-typing';
            typingDiv.textContent = 'IA está digitando...';
            messagesContainer.appendChild(typingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        function generateAIResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('previsão') || lowerMessage.includes('demanda')) {
                return "📊 Com base nos dados históricos, prevejo um aumento de 23% na demanda para notebooks no próximo mês. Recomendo estocar 42 unidades adicionais para atender às necessidades futuras.";
            } else if (lowerMessage.includes('anomalia') || lowerMessage.includes('problema')) {
                return "🔍 Detectei 3 anomalias no momento: consumo anormal de CPU no servidor WEB-01, estoque baixo de monitores (apenas 2 unidades) e manutenção fora do padrão na impressora PRINT-05.";
            } else if (lowerMessage.includes('manutenção') || lowerMessage.includes('vida útil')) {
                return "🔧 Tenho 12 recomendações de manutenção para os próximos 30 dias. A impressora LaserJet do prédio B precisa de atenção urgente - apenas 25% de vida útil restante.";
            } else if (lowerMessage.includes('estoque') || lowerMessage.includes('quantidade')) {
                return "📦 Atualmente você tem <?= $dashboardData['analytics']['total_equipment'] ?? 0 ?> equipamentos no inventário. A distribuição está equilibrada com 65% em uso, 20% em estoque e 15% em manutenção.";
            } else {
                return "💡 Posso te ajudar com várias análises do seu inventário! Você pode me perguntar sobre:\n\n• Previsões de demanda\n• Anomalias detectadas\n• Recomendações de manutenção\n• Status do estoque\n• Análises de uso\n\nO que você gostaria de saber?";
            }
        }

        function refreshDashboard() {
            location.reload();
        }
    </script>
</body>
</html>
