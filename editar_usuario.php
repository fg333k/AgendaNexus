<?php

//  editar_usuario.php — Somente administrador

include "includes/auth.php";

if ($sessao_perfil !== 'administrador') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$pagina_atual = 'usuarios';
$erro = "";

$id = (int) $_GET['id'];

if ($id == 0) {
    header("Location: /usuarios.php");
    exit;
}

// ── Busca o usuário ───────────────────────────────────────
$sql       = "SELECT * FROM usuarios WHERE id = $id LIMIT 1";
$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) == 0) {
    header("Location: /usuarios.php");
    exit;
}

$usuario = mysqli_fetch_assoc($resultado);

// ── Processar POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nome          = mysqli_real_escape_string($conn, $_POST['nome']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $telefone      = mysqli_real_escape_string($conn, $_POST['telefone']);
    $perfil        = $_POST['perfil'];
    $especialidade = mysqli_real_escape_string($conn, $_POST['especialidade']);
    $registro_prof = mysqli_real_escape_string($conn, $_POST['registro_prof']);
    $nova_senha    = $_POST['nova_senha'];
    $ativo         = isset($_POST['ativo']) ? 1 : 0;

    if ($nome == '' || $email == '') {
        $erro = "Nome e e-mail são obrigatórios.";
    } elseif ($nova_senha != '' && strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter no mínimo 6 caracteres.";
    } else {
        // Verifica e-mail duplicado em outro usuário
        $sql_check = "SELECT id FROM usuarios WHERE email = '$email' AND id != $id LIMIT 1";
        $res_check = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            $erro = "Este e-mail já está em uso por outro usuário.";
        } else {

            // Atualiza com ou sem nova senha
            if ($nova_senha != '') {
                $hash = password_hash($nova_senha, PASSWORD_BCRYPT);

                $sql = "UPDATE usuarios SET
                            nome          = '$nome',
                            email         = '$email',
                            telefone      = '$telefone',
                            perfil        = '$perfil',
                            especialidade = '$especialidade',
                            registro_prof = '$registro_prof',
                            senha_hash    = '$hash',
                            ativo         = $ativo
                        WHERE id = $id";
            } else {
                $sql = "UPDATE usuarios SET
                            nome          = '$nome',
                            email         = '$email',
                            telefone      = '$telefone',
                            perfil        = '$perfil',
                            especialidade = '$especialidade',
                            registro_prof = '$registro_prof',
                            ativo         = $ativo
                        WHERE id = $id";
            }

            $resultado = mysqli_query($conn, $sql);

            if (!$resultado) {
                $erro = "Erro ao atualizar: " . mysqli_error($conn);
            } else {
                mysqli_close($conn);
                header("Location: /usuarios.php");
                exit;
            }
        }
    }

    // Atualiza os dados exibidos no form com o que foi postado
    $usuario['nome']          = $_POST['nome'];
    $usuario['email']         = $_POST['email'];
    $usuario['telefone']      = $_POST['telefone'];
    $usuario['perfil']        = $_POST['perfil'];
    $usuario['especialidade'] = $_POST['especialidade'];
    $usuario['registro_prof'] = $_POST['registro_prof'];
    $usuario['ativo']         = $ativo;
}

mysqli_close($conn);

include "includes/header.php";
?>

<?php if ($erro): ?>
  <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="section-header">
  <div>
    <div class="section-title">Editar Usuário</div>
    <div class="section-sub">
      Editando: <?= htmlspecialchars($usuario['nome']) ?>
      — <?= ucfirst($usuario['perfil']) ?>
    </div>
  </div>
</div>

<div class="form-card">
  <form method="POST" action="/editar_usuario.php?id=<?= $id ?>">

    <div class="form-grid-2">
      <div class="form-group">
        <label>Nome completo *</label>
        <input type="text" name="nome" required
               placeholder="Ex: Dr. Carlos Mendes"
               value="<?= htmlspecialchars($usuario['nome']) ?>">
      </div>

      <div class="form-group">
        <label>Perfil *</label>
        <select name="perfil" required
                onchange="toggleProfFields(this.value)"
                <?= $id == $sessao_id ? 'disabled' : '' ?>>
          <?php foreach (['cliente'=>'Cliente','profissional'=>'Profissional','administrador'=>'Administrador'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= $usuario['perfil'] === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($id == $sessao_id): ?>
          <input type="hidden" name="perfil" value="<?= htmlspecialchars($usuario['perfil']) ?>">
        <?php endif; ?>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>E-mail *</label>
        <input type="email" name="email" required
               placeholder="email@exemplo.com"
               value="<?= htmlspecialchars($usuario['email']) ?>">
      </div>

      <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone"
               placeholder="(11) 9 9999-9999"
               value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
      </div>
    </div>

    <div id="prof-fields">
      <div class="form-grid-2">
        <div class="form-group">
          <label>Especialidade</label>
          <input type="text" name="especialidade"
                 placeholder="Ex: Dentista, Clínico Geral"
                 value="<?= htmlspecialchars($usuario['especialidade'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Registro profissional</label>
          <input type="text" name="registro_prof"
                 placeholder="Ex: CRO-SP 12345"
                 value="<?= htmlspecialchars($usuario['registro_prof'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Nova senha
        <span style="font-weight:400;text-transform:none;font-size:0.75rem">
          (deixe em branco para manter a atual)
        </span>
      </label>
      <input type="password" name="nova_senha"
             placeholder="Mínimo 6 caracteres">
    </div>

    <div class="form-group" style="margin-bottom:0">
      <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-size:0.85rem;cursor:pointer">
        <input type="checkbox" name="ativo" value="1"
               <?= $usuario['ativo'] ? 'checked' : '' ?>
               <?= $id == $sessao_id ? 'disabled' : '' ?>
               style="width:auto">
        Usuário ativo
        <?php if ($id == $sessao_id): ?>
          <span style="color:var(--muted);font-size:0.75rem">(não é possível desativar a si mesmo)</span>
          <input type="hidden" name="ativo" value="1">
        <?php endif; ?>
      </label>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn-primary">✓ Salvar alterações</button>
      <a href="/usuarios.php" class="btn-secondary">Cancelar</a>
      <?php if ($id != $sessao_id): ?>
        <a href="/excluir_usuario.php?id=<?= $id ?>"
           class="btn-danger" style="margin-left:auto"
           onclick="return confirm('Excluir o usuário <?= htmlspecialchars(addslashes($usuario['nome'])) ?> permanentemente?')">
          ✕ Excluir usuário
        </a>
      <?php endif; ?>
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
