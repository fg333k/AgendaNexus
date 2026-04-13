<?php

$servidor = "127.0.0.1";
$usuario  = "agendaflex"; 
$senha    = "12345";
$banco    = "agendaflex"; 

$conn = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conn) {
    die("Erro ao conectar: " . mysqli_connect_error());
}
