<?php
require_once __DIR__ . '/services/session.php';
require_once __DIR__ . '/services/auth.php';
$demo = appConfig()['auth']['demo_user'];
$user = [
  'id' => 1,
  'nome' => $demo['nome'],
  'email' => $demo['email'],
  'papel' => $demo['papel'],
];
setSessionUser($user);
header('Location: index.php');
exit;