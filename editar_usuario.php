<?php
// ============================================================
//  editar_usuario.php — Gerenciamento de Status via Select
// ============================================================
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

// ── Busca o usuário 
$sql       = "SELECT * FROM usuarios WHERE id = $id LIMIT 1";
$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) == 0) {
    header("Location: /usuarios.php");
    exit;
}

$usuario = mysqli_fetch_assoc($resultado);

// ── Processar POST 
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $nome          = mysqli_real_escape_string($conn, $_POST['nome']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $telefone      = mysqli_real_escape_string($conn, $_POST['telefone']);
    $perfil        = $_POST['perfil'];
    $especialidade = mysqli_real_escape_string($conn, $_POST['especialidade']);
    $registro_prof = mysqli_real_escape_string($conn, $_POST['registro_prof']);
    $nova_senha    = $_POST['nova_senha'];
    
    // MODIFICAÇÃO: Captura o valor do select (1 ou 0)
    $ativo         = (int)$_POST['ativo'];

    if ($nome == '' || $email == '') {
        $erro = "Nome e e-mail são obrigatórios.";
    } elseif ($nova_senha != '' && strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter no mínimo 6 caracteres.";
    } else {
        // Verifica e-mail duplicado
        $sql_check = "SELECT id FROM usuarios WHERE email = '$email' AND id != $id LIMIT 1";
        $res_check = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            $erro = "Este e-mail já está em uso por outro usuário.";
        } else {
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

    // Mantém os dados no formulário em caso de erro
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
    </div>
  </div>
</div>

<div class="form-card">
  <form method="POST" action="/editar_usuario.php?id=<?= $id ?>">

    <div class="form-grid-2">
      <div class="form-group">
        <label>Nome completo *</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($usuario['nome']) ?>">
      </div>

      <div class="form-group">
        <label>Perfil *</label>
        <select name="perfil" required onchange="toggleProfFields(this.value)" <?= $id == $sessao_id ? 'disabled' : '' ?>>
          <?php foreach (['cliente'=>'Cliente','profissional'=>'Profissional','administrador'=>'Administrador'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= $usuario['perfil'] === $v ? 'selected' : '' ?>><?= $l ?></option>
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
        <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email']) ?>">
      </div>

      <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
      </div>
    </div>

    <div id="prof-fields">
      <div class="form-grid-2">
        <div class="form-group">
          <label>Especialidade</label>
          <input type="text" name="especialidade" value="<?= htmlspecialchars($usuario['especialidade'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Registro profissional</label>
          <input type="text" name="registro_prof" value="<?= htmlspecialchars($usuario['registro_prof'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Nova senha <span style="font-weight:400; font-size:0.75rem">(deixe em branco para manter)</span></label>
      <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres">
    </div>

    <div class="form-group">
      <label>Status da Conta *</label>
      <select name="ativo" required <?= $id == $sessao_id ? 'disabled' : '' ?>>
        <option value="1" <?= (int)$usuario['ativo'] === 1 ? 'selected' : '' ?>>Ativo</option>
        <option value="0" <?= (int)$usuario['ativo'] === 0 ? 'selected' : '' ?>>Inativo</option>
      </select>
      
      <?php if ($id == $sessao_id): ?>
        <span style="color:var(--muted); font-size:0.75rem; display:block; margin-top:4px">
          (Você não pode desativar a si mesmo)
        </span>
        <input type="hidden" name="ativo" value="1">
      <?php endif; ?>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn-primary">✓ Salvar alterações</button>
      <a href="/usuarios.php" class="btn-secondary">Cancelar</a>
      <?php if ($id != $sessao_id): ?>
        <a href="/excluir_usuario.php?id=<?= $id ?>" class="btn-danger" style="margin-left:auto" 
           onclick="return confirm('Excluir permanentemente?')">✕ Excluir</a>
      <?php endif; ?>
    </div>

  </form>
</div>

<script>
function toggleProfFields(perfil) {
  const fields = document.getElementById('prof-fields');
  fields.style.display = perfil === 'profissional' ? 'block' : 'none';
}
toggleProfFields(document.querySelector('[name=perfil]').value);
</script>

<?php include "includes/footer.php"; ?>