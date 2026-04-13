<?php

//  includes/auth


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header("Location: /login.php");
    exit;
}

$sessao_id     = $_SESSION['usuario_id'];
$sessao_nome   = $_SESSION['usuario_nome'];
$sessao_perfil = $_SESSION['usuario_perfil'];

// Retorna as iniciais do nome para o avatar
function iniciais($nome) {
    $partes = explode(' ', trim($nome));
    $ini = strtoupper(substr($partes[0], 0, 1));
    if (isset($partes[1])) {
        $ini .= strtoupper(substr($partes[1], 0, 1));
    }
    return $ini;
}
