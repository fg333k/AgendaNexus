<?php

//  novo_agendamento.php

include "includes/auth.php";

if ($sessao_perfil === 'cliente') {
    header("Location: /dashboard.php");
    exit;
}

include "includes/conexao.php";

$pagina_atual = 'agendamentos';
$erro = "";

// ── Listas para os selects ────────────────────────────────
$sql_profs  = "SELECT id, nome, especialidade FROM usuarios WHERE perfil = 'profissional' AND ativo = 1 ORDER BY nome";
$res_profs  = mysqli_query($conn, $sql_profs);

$sql_clientes = "SELECT id, nome FROM usuarios WHERE perfil = 'cliente' AND ativo = 1 ORDER BY nome";
$res_clientes = mysqli_query($conn, $sql_clientes);

// ── Processar POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $profissional_id = (int) $_POST['profissional_id'];
    $cliente_id      = (int) $_POST['cliente_id'];
    $tipo_servico    = $_POST['tipo_servico'];
    $sala            = $_POST['sala'];
    $data            = $_POST['data'];
    $hora            = $_POST['hora'];
    $duracao_min     = (int) $_POST['duracao_min'];
    $status          = $_POST['status'];
    $observacoes     = mysqli_real_escape_string($conn, $_POST['observacoes']);
    $sala_escaped    = mysqli_real_escape_string($conn, $sala);

    if ($profissional_id == 0 || $cliente_id == 0 || $data == '' || $hora == '') {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        $data_hora = $data . " " . $hora . ":00";

        $sql = "INSERT INTO agendamentos
                    (profissional_id, cliente_id, tipo_servico, sala, data_hora, duracao_min, status, observacoes, criado_por)
                VALUES
                    ($profissional_id, $cliente_id, '$tipo_servico', '$sala_escaped', '$data_hora', $duracao_min, '$status', '$observacoes', $sessao_id)";

        $resultado = mysqli_query($conn, $sql);

        if (!$resultado) {
            $erro = "Erro ao salvar: " . mysqli_error($conn);
        } else {
            mysqli_close($conn);
            header("Location: /agendamentos.php");
            exit;
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
    <div class="section-title">Novo Agendamento</div>
    <div class="section-sub">Preencha os dados para registrar o compromisso</div>
  </div>
</div>

<div class="form-card">
  <form method="POST" action="/novo_agendamento.php">

    <div class="form-grid-2">
      <div class="form-group">
        <label>Profissional *</label>
        <select name="profissional_id" required>
          <option value="0">Selecionar...</option>
          <?php
          // Rebobina o resultado para reutilizar caso haja erro de validação
          mysqli_data_seek($res_profs, 0);
          while ($p = mysqli_fetch_assoc($res_profs)):
          ?>
            <option value="<?= $p['id'] ?>"
              <?= ($_POST['profissional_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nome']) ?>
              <?= $p['especialidade'] ? '— ' . htmlspecialchars($p['especialidade']) : '' ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Cliente / Paciente *</label>
        <select name="cliente_id" required>
          <option value="0">Selecionar...</option>
          <?php
          mysqli_data_seek($res_clientes, 0);
          while ($c = mysqli_fetch_assoc($res_clientes)):
          ?>
            <option value="<?= $c['id'] ?>"
              <?= ($_POST['cliente_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
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
          <option value="">Selecionar...</option>
          <?php foreach (['consulta'=>'Consulta','retorno'=>'Retorno','procedimento'=>'Procedimento','avaliacao'=>'Avaliação'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= ($_POST['tipo_servico'] ?? '') === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Sala / Recurso</label>
        <input type="text" name="sala"
               placeholder="Ex: Sala 01 - Consultório A"
               value="<?= htmlspecialchars($_POST['sala'] ?? '') ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Data *</label>
        <input type="date" name="data" required
               value="<?= htmlspecialchars($_POST['data'] ?? date('Y-m-d')) ?>">
      </div>

      <div class="form-group">
        <label>Horário *</label>
        <input type="time" name="hora" required
               value="<?= htmlspecialchars($_POST['hora'] ?? '09:00') ?>">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Duração (minutos)</label>
        <select name="duracao_min">
          <?php foreach ([30, 45, 60, 90, 120] as $d): ?>
            <option value="<?= $d ?>"
              <?= ($_POST['duracao_min'] ?? 60) == $d ? 'selected' : '' ?>>
              <?= $d ?> min
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <?php foreach (['pendente'=>'Pendente','confirmado'=>'Confirmado'] as $v => $l): ?>
            <option value="<?= $v ?>"
              <?= ($_POST['status'] ?? 'pendente') === $v ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Observações</label>
      <textarea name="observacoes"
                placeholder="Informações adicionais sobre o agendamento..."
      ><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
    </div>

    <div class="form-footer">
      <button type="submit" class="btn-primary">✓ Salvar agendamento</button>
      <a href="/agendamentos.php" class="btn-secondary">Cancelar</a>
    </div>

  </form>
</div>

<?php include "includes/footer.php"; ?>
