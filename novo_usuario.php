<?php

//  novo_usuario.php — Somente administrador

include "includes/auth.php";

if ($sessao_perfil !== 'administrador') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$pagina_atual = 'usuarios';
$erro = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nome          = mysqli_real_escape_string($conn, $_POST['nome']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $telefone      = mysqli_real_escape_string($conn, $_POST['telefone']);
    $perfil        = $_POST['perfil'];
    $especialidade = mysqli_real_escape_string($conn, $_POST['especialidade']);
    $registro_prof = mysqli_real_escape_string($conn, $_POST['registro_prof']);
    $senha         = $_POST['senha'];
    $ativo         = isset($_POST['ativo']) ? 1 : 0;

    if ($nome == '' || $email == '' || $senha == '') {
        $erro = "Nome, e-mail e senha são obrigatórios.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter no mínimo 6 caracteres.";
    } else {
        // Verifica e-mail duplicado
        $sql_check = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
        $res_check = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (nome, email, telefone, senha_hash, perfil, especialidade, registro_prof, ativo)
                    VALUES ('$nome', '$email', '$telefone', '$hash', '$perfil', '$especialidade', '$registro_prof', $ativo)";

            $resultado = mysqli_query($conn, $sql);

            if (!$resultado) {
                $erro = "Erro ao cadastrar: " . mysqli_error($conn);
            } else {
                mysqli_close($conn);
                header("Location: /usuarios.php");
                exit;
            }
        }
    }
}

mysqli_close($conn);

include "includes/header.php";
?>

<?php if ($erro): ?>
  <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="section-header">
  <div>
    <div class="section-title">Novo Usuário</div>
    <div class="section-sub">Cadastre um administrador, profissional ou cliente</div>
  </div>
</div>

<div class="form-card">
  <form method="POST" action="/novo_usuario.php">

    <div class="form-grid-2">
      <div class="form-group">
        <label>Nome completo *</label>
        <input type="text" name="nome" required
               placeholder="Ex: Dra. Ana Lima"
               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Perfil *</label>
        <select name="perfil" required onchange="toggleProfFields(this.value)">
          <?php foreach (['cliente'=>'Cliente','profissional'=>'Profissional','administrador'=>'Administrador'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= ($_POST['perfil'] ?? 'cliente') === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>E-mail *</label>
        <input type="email" name="email" required
               placeholder="email@exemplo.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone"
               placeholder="(11) 9 9999-9999"
               value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
      </div>
    </div>

    <div id="prof-fields" style="display:none">
      <div class="form-grid-2">
        <div class="form-group">
          <label>Especialidade</label>
          <input type="text" name="especialidade"
                 placeholder="Ex: Dentista, Clínico Geral"
                 value="<?= htmlspecialchars($_POST['especialidade'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Registro profissional</label>
          <input type="text" name="registro_prof"
                 placeholder="Ex: CRO-SP 12345"
                 value="<?= htmlspecialchars($_POST['registro_prof'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Senha *</label>
        <input type="password" name="senha" placeholder="Mínimo 6 caracteres">
      </div>

      <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px">
        <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-size:0.85rem;cursor:pointer">
          <input type="checkbox" name="ativo" value="1"
                 checked style="width:auto">
          Usuário ativo
        </label>
      </div>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn-primary">✓ Salvar usuário</button>
      <a href="/usuarios.php" class="btn-secondary">Cancelar</a>
    </div>

  </form>
</div>

<script>
function toggleProfFields(perfil) {
  document.getElementById('prof-fields').style.display =
    perfil === 'profissional' ? 'block' : 'none';
}
toggleProfFields(document.querySelector('[name=perfil]').value);
</script>

<?php include "includes/footer.php"; ?>
