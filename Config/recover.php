<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/services/store.php';
header('Content-Type: application/json');
function respond($arr){ echo json_encode($arr); exit; }
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { $data = $_POST; }
$enabled = (string)config('RECOVERY_ENABLED','0') === '1';
if (!$enabled) { respond(['error'=>'erro','message'=>'Recuperação desativada. Defina RECOVERY_ENABLED=1 e SMTP/SendGrid no .env']); }
$email = trim($data['email'] ?? '');
if ($email === '') { respond(['error'=>'erro','message'=>'Informe o e-mail']); }
$from = (string)config('EMAIL_FROM','no-reply@example.com');
$html = '<p>Solicitação de redefinição recebida para: '.htmlspecialchars($email).'</p><p>Se você não solicitou, ignore este e-mail.</p>';
$ok = sendEmail($email, 'Recuperação de senha', $html, $from, config('APP_NAME','Aplicativo'));
if ($ok) { respond(['success'=>'sucesso','message'=>'E-mail de instruções enviado (se configurado).']); }
respond(['error'=>'erro','message'=>'Envio de e-mail não configurado. Ajuste SENDGRID_API_KEY ou SMTP no .env']);
