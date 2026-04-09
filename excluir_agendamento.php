<?php

//  excluir_agendamento.php

include "includes/auth.php";

if ($sessao_perfil === 'cliente') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$id = (int) $_GET['id'];

if ($id > 0) {
    $sql       = "DELETE FROM agendamentos WHERE id = $id";
    $resultado = mysqli_query($conn, $sql);

    if (!$resultado) {
        die("Erro ao excluir: " . mysqli_error($conn));
    }
}

mysqli_close($conn);

header("Location: /agendamentos.php");
exit;
