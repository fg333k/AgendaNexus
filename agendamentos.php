<?php

//  agendamentos.php — Listagem com filtros

include "includes/auth.php";
include "includes/conexao.php";

$pagina_atual = 'agendamentos';

// ── Filtros via GET ───────────────────────────────────────
$filtro_data    = $_GET['data']    ?? '';
$filtro_prof    = $_GET['prof']    ?? '';
$filtro_status  = $_GET['status']  ?? '';
$filtro_servico = $_GET['servico'] ?? '';
$filtro_busca   = $_GET['busca']   ?? '';

// ── Montagem WHERE dinamicamente ─────────────────────────────
$where = "WHERE 1=1";

if ($filtro_data != '') {
    $where .= " AND DATE(a.data_hora) = '$filtro_data'";
}

if ($filtro_prof != '') {
    $filtro_prof_int = (int) $filtro_prof;
    $where .= " AND a.profissional_id = $filtro_prof_int";
}

if ($filtro_status != '') {
    $where .= " AND a.status = '$filtro_status'";
}

if ($filtro_servico != '') {
    $where .= " AND a.tipo_servico = '$filtro_servico'";
}

if ($filtro_busca != '') {
    $busca = mysqli_real_escape_string($conn, $filtro_busca);
    $where .= " AND (p.nome LIKE '%$busca%' OR c.nome LIKE '%$busca%')";
}

// Restrição por perfil
if ($sessao_perfil === 'cliente') {
    $where .= " AND a.cliente_id = $sessao_id";
}

if ($sessao_perfil === 'profissional') {
    $where .= " AND a.profissional_id = $sessao_id";
}

// ── Consulta principal ────────────────────────────────────
$sql = "SELECT
            a.id,
            DATE_FORMAT(a.data_hora, '%d/%m/%Y') AS data,
            DATE_FORMAT(a.data_hora, '%H:%i')    AS hora,
            p.nome        AS profissional,
            p.especialidade,
            c.nome        AS cliente,
            a.tipo_servico,
            a.sala,
            a.status
        FROM agendamentos a
        INNER JOIN usuarios p ON p.id = a.profissional_id
        INNER JOIN usuarios c ON c.id = a.cliente_id
        $where
        ORDER BY a.data_hora DESC";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro na consulta: " . mysqli_error($conn));
}

$total = mysqli_num_rows($resultado);

// ── Lista de profissionais para o filtro ──────────────────
$sql_profs     = "SELECT id, nome FROM usuarios WHERE perfil = 'profissional' AND ativo = 1 ORDER BY nome";
$res_profs     = mysqli_query($conn, $sql_profs);

mysqli_close($conn);

include "includes/header.php";
?>

<div class="section-header">
  <div>
    <div class="section-title">Todos os Agendamentos</div>
    <div class="section-sub"><?= $total ?> registro(s) encontrado(s)</div>
  </div>
  <?php if ($sessao_perfil !== 'cliente'): ?>
    <a href="/novo_agendamento.php" class="btn-primary">＋ Novo agendamento</a>
  <?php endif; ?>
</div>

<!-- Filtros -->
<form method="GET" action="/agendamentos.php">
  <div class="filters-row">

    <input type="date" name="data" class="filter-select"
           value="<?= htmlspecialchars($filtro_data) ?>">

    <?php if ($sessao_perfil !== 'profissional'): ?>
    <select name="prof" class="filter-select">
      <option value="">Todos os profissionais</option>
      <?php while ($p = mysqli_fetch_assoc($res_profs)): ?>
        <option value="<?= $p['id'] ?>"
          <?= $filtro_prof == $p['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['nome']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <?php endif; ?>

    <select name="status" class="filter-select">
      <option value="">Todos os status</option>
      <?php foreach (['pendente','confirmado','cancelado','concluido'] as $s): ?>
        <option <?= $filtro_status === $s ? 'selected' : '' ?>>
          <?= $s ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="servico" class="filter-select">
      <option value="">Todos os serviços</option>
      <?php foreach (['consulta','retorno','procedimento','avaliacao'] as $sv): ?>
        <option <?= $filtro_servico === $sv ? 'selected' : '' ?>>
          <?= $sv ?>
        </option>
      <?php endforeach; ?>
    </select>

    <input type="text" name="busca" class="filter-input"
           placeholder="🔍  Buscar cliente ou profissional..."
           value="<?= htmlspecialchars($filtro_busca) ?>">

    <button type="submit" class="btn-primary">Filtrar</button>

    <?php if ($filtro_data || $filtro_prof || $filtro_status || $filtro_servico || $filtro_busca): ?>
      <a href="/agendamentos.php" class="btn-secondary">✕ Limpar</a>
    <?php endif; ?>

  </div>
</form>

<!-- Tabela -->
<div class="table-wrap">
  <?php if ($total == 0): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <p>Nenhum agendamento encontrado.</p>
    </div>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Data / Hora</th>
        <th>Profissional</th>
        <th>Cliente</th>
        <th>Serviço</th>
        <th>Sala</th>
        <th>Status</th>
        <?php if ($sessao_perfil !== 'cliente'): ?>
        <th style="text-align:right">Ações</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($a = mysqli_fetch_assoc($resultado)): ?>
      <tr>
        <td>
          <div class="td-date"><?= $a['data'] ?></div>
          <div class="td-time"><?= $a['hora'] ?></div>
        </td>
        <td>
          <div class="person-cell">
            <div class="mini-avatar av-green"><?= iniciais($a['profissional']) ?></div>
            <div>
              <div class="person-name"><?= htmlspecialchars($a['profissional']) ?></div>
              <div class="person-spec"><?= htmlspecialchars($a['especialidade'] ?? '') ?></div>
            </div>
          </div>
        </td>
        <td>
          <div class="person-cell">
            <div class="mini-avatar av-blue"><?= iniciais($a['cliente']) ?></div>
            <div class="person-name"><?= htmlspecialchars($a['cliente']) ?></div>
          </div>
        </td>
        <td>
          <span class="servico-tag tag-<?= $a['tipo_servico'] ?>">
            <?= $a['tipo_servico'] ?>
          </span>
        </td>
        <td style="font-size:0.8rem;color:var(--muted)">
          🚪 <?= htmlspecialchars($a['sala'] ?? '—') ?>
        </td>
        <td>
          <span class="status-badge status-<?= $a['status'] ?>">
            <span class="dot"></span> <?= $a['status'] ?>
          </span>
        </td>
        <?php if ($sessao_perfil !== 'cliente'): ?>
        <td>
          <div class="td-actions">
            <a href="/editar_agendamento.php?id=<?= $a['id'] ?>" class="btn-icon" title="Editar">✎</a>
            <a href="/excluir_agendamento.php?id=<?= $a['id'] ?>"
               class="btn-icon danger" title="Excluir"
               onclick="return confirm('Excluir este agendamento?')">✕</a>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
