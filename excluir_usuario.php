<?php

//  excluir_usuario.php — Somente administrador

include "includes/auth.php";

if ($sessao_perfil !== 'administrador') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$id = (int) $_GET['id'];

// Impede o admin de excluir a si mesmo
if ($id > 0 && $id != $sessao_id) {
    $sql       = "DELETE FROM usuarios WHERE id = $id";
    $resultado = mysqli_query($conn, $sql);

    if (!$resultado) {
        die("Erro ao excluir: " . mysqli_error($conn));
    }
}

mysqli_close($conn);

header("Location: /usuarios.php");
exit;
