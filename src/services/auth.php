<?php
require_once __DIR__ . '/banco.php';

function appConfig()
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $config;
}

function login($email, $password)
{
    try {
        $rows = banco_get('usuarios', ['select' => '*', 'email' => 'eq.' . $email, 'limit' => 1]);
        $user = $rows[0] ?? null;
        $ok = false;
        if ($user) {
            if (!empty($user['senha_hash'])) {
                $ok = password_verify($password, $user['senha_hash']);
            } else {
                if (function_exists('banco_mock_enabled') && banco_mock_enabled()) {
                    $ok = ($password === 'mudar123');
                }
            }
        }
        if (!$ok) {
            $_SESSION['error'] = 'Credenciais inválidas. Verifique e tente novamente.';
            return false;
        }
        unset($user['senha_hash']);
        $_SESSION['user'] = $user;
        try { require_once __DIR__ . '/store.php'; logActivity('login', 'usuario', $user['id'] ?? 0, 'Usuário entrou no sistema'); } catch (Exception $e) {}
        return $user;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        return false;
    }
}

function register($name, $email, $password) {
    try {
        $dup = banco_get('usuarios', ['select' => 'id', 'email' => 'eq.' . $email, 'limit' => 1]);
        if (!empty($dup)) return ['error' => 'E-mail já cadastrado.'];

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $profile = [
            'nome' => $name,
            'email' => $email,
            'senha_hash' => $hash,
            'papel' => 'user',
            'ativo' => 1
        ];

        $res = banco_insert('usuarios', $profile);
        if (!empty($res)) {
            try { require_once __DIR__ . '/store.php'; logActivity('register', 'usuario', $res[0]['id'] ?? 0, 'Usuário registrado: ' . $email); } catch (Exception $e) {}
            $_SESSION['user'] = [ 'id' => $res[0]['id'] ?? null, 'nome' => $name, 'email' => $email, 'papel' => 'user', 'ativo' => 1 ];
            try { logActivity('login', 'usuario', $res[0]['id'] ?? 0, 'Login automático após cadastro'); } catch (Exception $e) {}
            return ['success' => true, 'id' => $res[0]['id']];
        }
        return ['error' => 'Erro ao criar usuário.'];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function loginDemo($email, $password)
{
    return false;
}
