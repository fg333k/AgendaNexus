<?php
//  dashboard
include "includes/auth.php";
include "includes/conexao.php";
$pagina_atual = 'dashboard';    // Define a página para o menu active

$hoje = date('Y-m-d');

// ── Restrição por perfil (reutilizada em todas as queries) 
$where_perfil_agend = "";

if ($sessao_perfil === 'cliente') {
    $where_perfil_agend = " AND cliente_id = $sessao_id";
}

if ($sessao_perfil === 'profissional') {
    $where_perfil_agend = " AND profissional_id = $sessao_id";
}

// ── Total de agendamentos hoje (filtrado por perfil) 
$sql       = "SELECT COUNT(*) AS total FROM agendamentos 
              WHERE DATE(data_hora) = '$hoje' $where_perfil_agend";
$resultado = mysqli_query($conn, $sql);
$row       = mysqli_fetch_assoc($resultado);
$total_hoje = $row['total'];

// ── Total pendentes (filtrado por perfil) 
$sql       = "SELECT COUNT(*) AS total FROM agendamentos 
              WHERE status = 'pendente' $where_perfil_agend";
$resultado = mysqli_query($conn, $sql);
$row       = mysqli_fetch_assoc($resultado);
$total_pendentes = $row['total'];

// ── Total clientes e profissionais (somente admin) 
$total_clientes      = 0;
$total_profissionais = 0;

if ($sessao_perfil === 'administrador') {
    $sql       = "SELECT COUNT(*) AS total FROM usuarios WHERE perfil = 'cliente' AND ativo = 1";
    $resultado = mysqli_query($conn, $sql);
    $row       = mysqli_fetch_assoc($resultado);
    $total_clientes = $row['total'];

    $sql       = "SELECT COUNT(*) AS total FROM usuarios WHERE perfil = 'profissional' AND ativo = 1";
    $resultado = mysqli_query($conn, $sql);
    $row       = mysqli_fetch_assoc($resultado);
    $total_profissionais = $row['total'];
}

// ── Próximos agendamentos (filtrado por perfil) ───────────
$where_tabela = "WHERE DATE(a.data_hora) >= '$hoje'";

if ($sessao_perfil === 'cliente') {
    $where_tabela .= " AND a.cliente_id = $sessao_id";
}

if ($sessao_perfil === 'profissional') {
    $where_tabela .= " AND a.profissional_id = $sessao_id";
}

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
        $where_tabela
        ORDER BY a.data_hora ASC
        LIMIT 8";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro na consulta: " . mysqli_error($conn));
}

mysqli_close($conn);

include "includes/header.php";
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--primary-light)">📅</div>
    <div class="stat-label">Hoje</div>
    <div class="stat-value"><?= $total_hoje ?></div>
    <div class="stat-sub">
      <?= $sessao_perfil === 'administrador' ? 'Total do dia' : 'Seus agendamentos hoje' ?>
    </div>
  </div>

  <?php if ($sessao_perfil === 'administrador'): ?>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--blue-light)">👥</div>
    <div class="stat-label">Clientes</div>
    <div class="stat-value"><?= $total_clientes ?></div>
    <div class="stat-sub">Cadastrados e ativos</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gold-light)">🩺</div>
    <div class="stat-label">Profissionais</div>
    <div class="stat-value"><?= $total_profissionais ?></div>
    <div class="stat-sub">Ativos no sistema</div>
  </div>
  <?php endif; ?>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--accent-light)">⏳</div>
    <div class="stat-label">Pendentes</div>
    <div class="stat-value"><?= $total_pendentes ?></div>
    <div class="stat-sub">
      <?= $sessao_perfil === 'administrador' ? 'Aguardando confirmação' : 'Seus pendentes' ?>
    </div>
  </div>
</div>

<div class="section-header">
  <div>
    <div class="section-title">Próximos agendamentos</div>
    <div class="section-sub">A partir de hoje — <?= date('d/m/Y') ?></div>
  </div>
  
  <?php if ($sessao_perfil === 'administrador'): ?>
    <a href="/novo_agendamento.php" class="btn-primary">+ Novo agendamento</a>
  <?php endif; ?>
</div>

<div class="table-wrap">
  <?php if (mysqli_num_rows($resultado) == 0): ?>
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
        <?php if ($sessao_perfil === 'administrador'): ?>
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
            <div class="mini-avatar av-green">
              <?= iniciais($a['profissional']) ?>
            </div>
            <div>
              <div class="person-name"><?= htmlspecialchars($a['profissional']) ?></div>
              <div class="person-spec"><?= htmlspecialchars($a['especialidade'] ?? '') ?></div>
            </div>
          </div>
        </td>
        <td>
          <div class="person-cell">
            <div class="mini-avatar av-blue">
              <?= iniciais($a['cliente']) ?>
            </div>
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
        
        <?php if ($sessao_perfil === 'administrador'): ?>
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