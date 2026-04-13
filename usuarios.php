<?php

//  usuarios 
include "includes/auth.php";
include "includes/conexao.php";

// Proteção de perfil
if ($sessao_perfil !== 'administrador') {
    header("Location: /dashboard.php");
    exit;
}

$pagina_atual = 'usuarios';

$sql       = "SELECT id, nome, email, telefone, perfil, especialidade, ativo FROM usuarios ORDER BY perfil, nome";
$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro na consulta: " . mysqli_error($conn));
}

$total = mysqli_num_rows($resultado);

mysqli_close($conn);

$av_cores = [
    'administrador' => 'av-red',
    'profissional'  => 'av-green',
    'cliente'       => 'av-blue',
];

include "includes/header.php";
?>

<div class="section-header">
  <div>
    <div class="section-title">Usuários do Sistema</div>
    <div class="section-sub"><?= $total ?> usuário(s) cadastrado(s)</div>
  </div>
  <a href="/novo_usuario.php" class="btn-primary">＋ Novo usuário</a>
</div>

<div class="table-wrap">
  <?php if ($total == 0): ?>
    <div class="empty-state">
      <div class="empty-icon">👥</div>
      <p>Nenhum usuário cadastrado.</p>
    </div>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Usuário</th>
        <th>E-mail</th>
        <th>Telefone</th>
        <th>Perfil</th>
        <th>Especialidade</th>
        <th>Status</th>
        <th style="text-align:right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = mysqli_fetch_assoc($resultado)): ?>
      <tr>
        <td>
          <div class="person-cell">
            <div class="mini-avatar <?= $av_cores[$u['perfil']] ?? 'av-blue' ?>">
              <?= iniciais($u['nome']) ?>
            </div>
            <div class="person-name"><?= htmlspecialchars($u['nome']) ?></div>
          </div>
        </td>
        
        <td style="font-size:0.82rem">
          <?= htmlspecialchars($u['email']) ?>
        </td>
        
        <td style="font-size:0.82rem; color:var(--muted)">
          <?= htmlspecialchars($u['telefone'] ?? '—') ?>
        </td>
        
        <td>
          <span class="badge-perfil badge-<?= $u['perfil'] ?>">
            <?= $u['perfil'] ?>
          </span>
        </td>
        
        <td style="font-size:0.82rem; color:var(--muted)">
          <?= htmlspecialchars($u['especialidade'] ?? '—') ?>
        </td>
        
        <td>
          <span class="entity-tag <?= $u['ativo'] ? 'tag-ativo' : 'tag-inativo' ?>">
            <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
          </span>
        </td>

        <td>
          <div class="td-actions" style="justify-content: flex-end">
            <a href="/editar_usuario.php?id=<?= $u['id'] ?>" class="btn-icon" title="Editar">✎</a>
            
            <?php if ($u['id'] != $sessao_id): ?>
              <a href="/excluir_usuario.php?id=<?= $u['id'] ?>"
                 class="btn-icon danger" 
                 title="Excluir"
                 onclick="return confirm('Excluir o usuário <?= htmlspecialchars(addslashes($u['nome'])) ?>?')">✕</a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>