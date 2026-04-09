<?php

//  editar_agendamento.php

include "includes/auth.php";

if ($sessao_perfil === 'cliente') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$pagina_atual = 'agendamentos';
$erro = "";

$id = (int) $_GET['id'];

if ($id == 0) {
    header("Location: /agendamentos.php");
    exit;
}

// ── Busca o agendamento ───────────────────────────────────
$sql       = "SELECT *, DATE_FORMAT(data_hora, '%Y-%m-%d') AS data, DATE_FORMAT(data_hora, '%H:%i') AS hora
              FROM agendamentos WHERE id = $id LIMIT 1";
$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) == 0) {
    header("Location: /agendamentos.php");
    exit;
}

$agendamento = mysqli_fetch_assoc($resultado);

// Profissional só edita os próprios
if ($sessao_perfil === 'profissional' && $agendamento['profissional_id'] != $sessao_id) {
    header("Location: /agendamentos.php");
    exit;
}

// ── Listas para os selects ────────────────────────────────
$res_profs    = mysqli_query($conn, "SELECT id, nome, especialidade FROM usuarios WHERE perfil = 'profissional' AND ativo = 1 ORDER BY nome");
$res_clientes = mysqli_query($conn, "SELECT id, nome FROM usuarios WHERE perfil = 'cliente' AND ativo = 1 ORDER BY nome");

// ── Processar POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $profissional_id = (int) $_POST['profissional_id'];
    $cliente_id      = (int) $_POST['cliente_id'];
    $tipo_servico    = $_POST['tipo_servico'];
    $sala            = mysqli_real_escape_string($conn, $_POST['sala']);
    $data            = $_POST['data'];
    $hora            = $_POST['hora'];
    $duracao_min     = (int) $_POST['duracao_min'];
    $status          = $_POST['status'];
    $observacoes     = mysqli_real_escape_string($conn, $_POST['observacoes']);

    if ($profissional_id == 0 || $cliente_id == 0 || $data == '' || $hora == '') {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        $data_hora = $data . " " . $hora . ":00";

        $sql = "UPDATE agendamentos SET
                    profissional_id = $profissional_id,
                    cliente_id      = $cliente_id,
                    tipo_servico    = '$tipo_servico',
                    sala            = '$sala',
                    data_hora       = '$data_hora',
                    duracao_min     = $duracao_min,
                    status          = '$status',
                    observacoes     = '$observacoes'
                WHERE id = $id";

        $resultado = mysqli_query($conn, $sql);

        if (!$resultado) {
            $erro = "Erro ao atualizar: " . mysqli_error($conn);
        } else {
            mysqli_close($conn);
            header("Location: /agendamentos.php");
            exit;
        }
    }

    // Atualiza os dados exibidos no form com o que foi postado
    $agendamento['profissional_id'] = $_POST['profissional_id'];
    $agendamento['cliente_id']      = $_POST['cliente_id'];
    $agendamento['tipo_servico']    = $_POST['tipo_servico'];
    $agendamento['sala']            = $_POST['sala'];
    $agendamento['data']            = $_POST['data'];
    $agendamento['hora']            = $_POST['hora'];
    $agendamento['duracao_min']     = $_POST['duracao_min'];
    $agendamento['status']          = $_POST['status'];
    $agendamento['observacoes']     = $_POST['observacoes'];
}

mysqli_close($conn);

include "includes/header.php";
?>

<?php if ($erro): ?>
  <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="section-header">
  <div>
    <div class="section-title">Editar Agendamento</div>
    <div class="section-sub">
      Agendamento #<?= $id ?> —
      <?= htmlspecialchars($agendamento['data']) ?> às <?= htmlspecialchars($agendamento['hora']) ?>
    </div>
  </div>
</div>

<div class="form-card">
  <form method="POST" action="/editar_agendamento.php?id=<?= $id ?>">

    <div class="form-grid-2">
      <div class="form-group">
        <label>Profissional *</label>
        <select name="profissional_id" required
          <?= $sessao_perfil === 'profissional' ? 'disabled' : '' ?>>
          <option value="0">Selecionar...</option>
          <?php while ($p = mysqli_fetch_assoc($res_profs)): ?>
            <option value="<?= $p['id'] ?>"
              <?= $agendamento['profissional_id'] == $p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nome']) ?>
              <?= $p['especialidade'] ? '— ' . htmlspecialchars($p['especialidade']) : '' ?>
            </option>
          <?php endwhile; ?>
        </select>
        <?php if ($sessao_perfil === 'profissional'): ?>
          <input type="hidden" name="profissional_id" value="<?= $agendamento['profissional_id'] ?>">
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>Cliente / Paciente *</label>
        <select name="cliente_id" required>
          <option value="0">Selecionar...</option>
          <?php while ($c = mysqli_fetch_assoc($res_clientes)): ?>
            <option value="<?= $c['id'] ?>"
              <?= $agendamento['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nome']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Tipo de Serviço *</label>
        <select name="tipo_servico" required>
          <?php foreach (['consulta'=>'Consulta','retorno'=>'Retorno','procedimento'=>'Procedimento','avaliacao'=>'Avaliação'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= $agendamento['tipo_servico'] === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Sala / Recurso</label>
        <input type="text" name="sala"
               placeholder="Ex: Sala 01 - Consultório A"
               value="<?= htmlspecialchars($agendamento['sala'] ?? '') ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Data *</label>
        <input type="date" name="data" required
               value="<?= htmlspecialchars($agendamento['data']) ?>">
      </div>

      <div class="form-group">
        <label>Horário *</label>
        <input type="time" name="hora" required
               value="<?= htmlspecialchars($agendamento['hora']) ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Duração (minutos)</label>
        <select name="duracao_min">
          <?php foreach ([30, 45, 60, 90, 120] as $d): ?>
            <option value="<?= $d ?>"
              <?= $agendamento['duracao_min'] == $d ? 'selected' : '' ?>>
              <?= $d ?> min
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <?php foreach (['pendente'=>'Pendente','confirmado'=>'Confirmado','cancelado'=>'Cancelado','concluido'=>'Concluído'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= $agendamento['status'] === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Observações</label>
      <textarea name="observacoes"
                placeholder="Informações adicionais..."
      ><?= htmlspecialchars($agendamento['observacoes'] ?? '') ?></textarea>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn-primary">✓ Salvar alterações</button>
      <a href="/agendamentos.php" class="btn-secondary">Cancelar</a>
      <?php if ($sessao_perfil === 'administrador'): ?>
        <a href="/excluir_agendamento.php?id=<?= $id ?>"
           class="btn-danger" style="margin-left:auto"
           onclick="return confirm('Excluir este agendamento permanentemente?')">
          ✕ Excluir
        </a>
      <?php endif; ?>
    </div>

  </form>
</div>

<?php include "includes/footer.php"; ?>
