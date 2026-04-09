<?php

//  login.php

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['usuario_id'])) {
    header("Location: /dashboard.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    include "includes/conexao.php";

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql       = "SELECT * FROM usuarios WHERE email = '$email' AND ativo = 1";
    $resultado = mysqli_query($conn, $sql);

    if (mysqli_num_rows($resultado) > 0) {

        $usuario = mysqli_fetch_assoc($resultado);

        if (password_verify($senha, $usuario['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nome']   = $usuario['nome'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];

            mysqli_close($conn);

            header("Location: /dashboard.php");
            exit;
        } else {
            $erro = "E-mail ou senha inválidos.";
        }

    } else {
        $erro = "E-mail ou senha inválidos.";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — AgendaFlex</title>
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    body {
      background: var(--text);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: -200px; right: -200px;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(45,148,104,0.2) 0%, transparent 70%);
      pointer-events: none;
    }

    body::after {
      content: '';
      position: absolute;
      bottom: -150px; left: -100px;
      width: 450px; height: 450px;
      background: radial-gradient(circle, rgba(201,146,42,0.15) 0%, transparent 70%);
      pointer-events: none;
    }

    .login-box {
      background: var(--surface);
      border-radius: 24px;
      padding: 3rem;
      width: 420px;
      max-width: 95vw;
      position: relative;
      z-index: 1;
      box-shadow: var(--shadow-lg);
      animation: fadeUp 0.4s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .login-brand {
      text-align: center;
      margin-bottom: 2.2rem;
    }

    .login-brand .licon {
      width: 56px; height: 56px;
      background: var(--text);
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
      margin: 0 auto 1rem;
    }

    .login-brand h1 {
      font-family: 'Fraunces', serif;
      font-size: 1.8rem;
      color: var(--text);
    }

    .login-brand p {
      font-size: 0.82rem;
      color: var(--muted);
      margin-top: 4px;
    }

    .login-divider {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--muted);
      text-align: center;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    .login-divider::before,
    .login-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .btn-login {
      width: 100%;
      padding: 0.85rem;
      background: var(--text);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      font-family: 'Outfit', sans-serif;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.2s;
      margin-top: 0.5rem;
    }
    .btn-login:hover { opacity: 0.85; }
  </style>
</head>
<body>

<div class="login-box">
  <div class="login-brand">
    <div class="licon">📋</div>
    <h1>AgendaFlex</h1>
    <p>Sistema de Agendamento Profissional</p>
  </div>

  <?php if ($erro): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <div class="login-divider">Acesse sua conta</div>

  <form method="POST" action="/login.php">

    <div class="form-group">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email"
             placeholder="seu@email.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha"
             placeholder="••••••••">
    </div>

    <button type="submit" class="btn-login">Entrar no sistema →</button>

  </form>
</div>

</body>
</html>
