<?php

$pagina_atual = $pagina_atual ?? '';

$titulos = [
    'dashboard'          => 'Dashboard',
    'agendamentos'       => 'Agendamentos',
    'novo_agendamento'   => 'Novo Agendamento',
    'editar_agendamento' => 'Editar Agendamento',
    'usuarios'           => 'Usuários',
    'novo_usuario'       => 'Novo Usuário',
    'editar_usuario'     => 'Editar Usuário',
];

$titulo_pagina = $titulos[$pagina_atual] ?? 'AgendaNexus';

$label_perfil = [
    'administrador' => 'Administrador',
    'profissional'  => 'Profissional',
    'cliente'       => 'Cliente',
][$sessao_perfil] ?? 'Cliente';

$badge_class = 'badge-' . $sessao_perfil;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo_pagina) ?> — AgendaNexus</title>
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="shortcut icon" href="../assets/images/Logo_tipo_AegndaNexus.ico" type="image/x-icon">
</head>
<body>
<div class="app-layout">

  <aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <img src="../assets/images/Logo_tipo_AegndaNexus.png" alt="">
    </div>
    <span>AgendaNexus</span>
  </div>

    <div class="sidebar-section">Principal</div>

    <a href="/dashboard.php"
       class="nav-item <?= $pagina_atual === 'dashboard' ? 'active' : '' ?>">
      <span class="nav-icon">◈</span> Dashboard
    </a>

    <a href="/agendamentos.php"
       class="nav-item <?= $pagina_atual === 'agendamentos' ? 'active' : '' ?>">
      <span class="nav-icon">📅</span> Agendamentos
    </a>

    <?php if ($sessao_perfil === 'administrador'): ?>
    <div class="sidebar-section">Cadastros</div>
    <a href="/usuarios.php"
       class="nav-item <?= $pagina_atual === 'usuarios' ? 'active' : '' ?>">
      <span class="nav-icon">👥</span> Usuários
    </a>
    <?php endif; ?>

    <div class="sidebar-bottom">
      <div class="user-chip">
        <div class="user-avatar">
          <?= htmlspecialchars(iniciais($sessao_nome)) ?>
        </div>
        <div class="user-info">
          <div class="uname"><?= htmlspecialchars($sessao_nome) ?></div>
          <div class="urole"><?= htmlspecialchars($label_perfil) ?></div>
        </div>
        <a href="/logout.php" class="btn-logout" title="Sair">⇥</a>
      </div>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div class="topbar-title"><?= htmlspecialchars($titulo_pagina) ?></div>
      <div class="topbar-right">
        <span class="badge-perfil <?= $badge_class ?>">
          <?= htmlspecialchars($label_perfil) ?>
        </span>
        <span class="topbar-date"><?= date('d/m/Y') ?></span>
      </div>
    </div>

    <div class="content">
