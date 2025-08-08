<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexão com o banco de dados
$conn = new mysqli("localhost", "user", "pass", "db");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

// Filtros de categoria e busca
$categoriaFiltro = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';

// Monta a consulta principal
$sql = "SELECT * FROM servicos WHERE ativo = 1";
if (!empty($categoriaFiltro) && $categoriaFiltro !== 'Todos') {
  $sql .= " AND categoria = '" . $conn->real_escape_string($categoriaFiltro) . "'";
}
if (!empty($busca)) {
  $sql .= " AND nome LIKE '%" . $conn->real_escape_string($busca) . "%'";
}
$sql .= " ORDER BY categoria, nome";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Serviços STI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
/* === Configurações gerais === */
body {
  font-family: "Segoe UI", sans-serif;
  padding: 0.5rem 1rem 1rem 1rem;
  background: #ffffff;
}
h1 {
  text-align: center;
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 1rem 0;
}

/* === Barra de ações === */
.barra-acoes {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 0.5rem; 
}
/* Estilo dos botões circulares */
.botao-circular {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: none;
  background-color: #2d98f0;
  color: white;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  transition: background 0.3s ease;
}
.botao-circular:hover {
  background-color: #1c7cd6;
}

/* Container da busca (inicialmente oculto) */
.busca-container {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease, opacity 0.4s ease;
  opacity: 0;
  pointer-events: none;
  margin-bottom: 0.5rem; /* reduzido para aproximar mais da grid */
  /* Centraliza o conteúdo horizontalmente */
  display: flex;
  justify-content: center;
}
.busca-container.show {
  max-height: 150px;
  opacity: 1;
  pointer-events: auto;
}

  /* Estilização do formulário de busca */
  /* Centraliza o formulário dentro do container de busca */
  .busca-container form {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
  }
  /* Aplicar estilo aos campos de texto e select da busca */
  .busca-container input[type="text"],
  .busca-container select {
    padding: 0.55rem 0.9rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    background-color: #fff;
    min-width: 180px;
    max-width: 240px;
  }

/* === Formulário de adição === */
/* Estilização do formulário de adição (similar ao da busca) */
#formAdicionar {
  max-width: 500px;
  margin: 0 auto 0.5rem auto; /* mantém margem reduzida */
  background: #ffffff; /* alinhado ao estilo do campo de busca */
  border: 1px solid #eee; /* borda sutil para delimitar */
  padding: 1rem 1.5rem;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  overflow: hidden;
  transition: max-height 0.4s ease, opacity 0.4s ease;
  max-height: 0;
  opacity: 0;
  pointer-events: none;
  font-size: 0.95rem;
}
#formAdicionar.show {
  max-height: 600px;
  opacity: 1;
  pointer-events: auto;
}
#formAdicionar .form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}
#formAdicionar .form-row > div {
  flex: 1 1 48%;
  min-width: 45%;
}
#formAdicionar label {
  display: block;
  margin-bottom: 0.3rem;
  font-weight: 500;
  color: #333;
}
#formAdicionar input,
#formAdicionar select {
  width: 100%;
  padding: 0.55rem 0.9rem; /* mesmo espaçamento do campo de busca */
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  background-color: #fff;
  /* Garante que padding e borda sejam considerados dentro da largura total, evitando transbordar na borda */
  box-sizing: border-box;
}
#formAdicionar button {
  display: block;
  margin: 0.5rem auto 0 auto;
  background: #27ae60;
  color: #fff;
  padding: 0.5rem 1.2rem;
  border: none;
  border-radius: 5px;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background 0.3s ease;
}
#formAdicionar button:hover {
  background: #1e8449;
}

/* === Grid de serviços === */
.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}
@media (max-width: 1000px) {
  .grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 700px) {
  .grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .grid { grid-template-columns: 1fr; }
}

/* Cartão de serviço */
.card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: linear-gradient(145deg, #f9f9f9, #ffffff);
  border-radius: 14px;
  padding: 1.2rem;
  box-shadow: 0 6px 18px rgba(0,0,0,0.05);
  text-decoration: none;
  color: #2c3e50;
  transition: all 0.3s ease;
  border: 1px solid #eee;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.08);
  border-color: #ddd;
}
.card-body {
  margin-bottom: 1rem;
}
.card-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}
.card-subtitle {
  font-size: 0.85rem;
  color: #999;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.card-action {
  margin-top: auto;
  font-size: 0.9rem;
  font-weight: 500;
  color: #2980b9;
  text-align: right;
}
.card.infra  { border-left: 4px solid #3498db; }
.card.rede   { border-left: 4px solid #2ecc71; }
.card.seginfo{ border-left: 4px solid #e74c3c; }

/* Menu de contexto e modal (mantidos) */
.context-menu {
  position: absolute;
  display: none;
  background-color: #ffffff;
  border: 1px solid #ccc;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
  border-radius: 6px;
  z-index: 1000;
  min-width: 160px;
  font-size: 14px;
}
.context-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.context-menu li {
  padding: 10px 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.context-menu li:hover {
  background-color: #f2f2f2;
}

.modal {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
}
.modal-content {
  background: #fff;
  margin: 10% auto;
  padding: 1rem;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
}
  </style>
</head>
<body>

<!-- Título removido conforme solicitado -->

<!-- Barra de ações com botões circulares -->
<div class="barra-acoes">
  <button class="botao-circular" onclick="toggleBusca()" title="Buscar">🔍</button>
  <button class="botao-circular" onclick="toggleForm()" title="Adicionar">+</button>
</div>

<!-- Container da busca -->
<div id="buscaContainer" class="busca-container">
  <form id="filtroForm" method="GET" class="filtro-form">
    <input type="text" name="busca" id="busca" placeholder="🔍 Buscar..." value="<?= htmlspecialchars($busca) ?>">
    <select name="categoria" id="categoria">
      <option value="Todos">Todas</option>
      <option value="Infraestrutura" <?= $categoriaFiltro == "Infraestrutura" ? 'selected' : '' ?>>Infraestrutura</option>
      <option value="Rede" <?= $categoriaFiltro == "Rede" ? 'selected' : '' ?>>Rede</option>
      <option value="Seginfo" <?= $categoriaFiltro == "Seginfo" ? 'selected' : '' ?>>Seginfo</option>
    </select>
  </form>
</div>

<!-- Formulário de adição -->
<form id="formAdicionar" action="adicionar.php" method="POST">
  <div class="form-row">
    <div>
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" required>
    </div>
    <div>
      <label for="categoria">Categoria</label>
      <select id="categoria" name="categoria" required>
        <option value="">Selecione</option>
        <option value="Infraestrutura">Infraestrutura</option>
        <option value="Rede">Rede</option>
        <option value="Seginfo">Seginfo</option>
      </select>
    </div>
  </div>
  <div class="form-row">
    <div style="flex: 1 1 100%;">
      <label for="link">Link</label>
      <input type="url" id="link" name="link" required>
    </div>
  </div>
  <button type="submit">Salvar</button>
</form>

<!-- Grid de serviços -->
<div class="grid">
<?php
if ($result && $result->num_rows > 0):
  while($row = $result->fetch_assoc()):
    $categoria = strtolower($row['categoria']);
    $classe = $categoria === 'infraestrutura' ? 'infra' :
              ($categoria === 'rede' ? 'rede' : 'seginfo');
?>
  <a href="<?= htmlspecialchars($row['link']) ?>"
     class="card <?= $classe ?>"
     target="_top"
     data-id="<?= $row['id'] ?>"
     data-nome="<?= htmlspecialchars($row['nome']) ?>"
     data-categoria="<?= htmlspecialchars($row['categoria']) ?>"
     data-link="<?= htmlspecialchars($row['link']) ?>">
    <div class="card-body">
      <h2 class="card-title"><?= htmlspecialchars($row['nome']) ?></h2>
      <p class="card-subtitle"><?= htmlspecialchars($row['categoria']) ?></p>
    </div>
    <!-- Removido o texto 'Abrir Serviço' conforme solicitado -->
    <div class="card-action"></div>
  </a>
<?php endwhile; else: ?>
  <p style="text-align:center;">Nenhum serviço encontrado.</p>
<?php endif; ?>
</div>

<?php
// Consulta para contar serviços por categoria (como no original)
$sqlTotais = "
  SELECT categoria, COUNT(*) as total
  FROM servicos
  WHERE ativo = 1
  GROUP BY categoria
";
$resultTotais = $conn->query($sqlTotais);
$totais = [
  'Infraestrutura' => 0,
  'Rede' => 0,
  'Seginfo' => 0
];
while ($rowTotal = $resultTotais->fetch_assoc()) {
  $totais[$rowTotal['categoria']] = (int)$rowTotal['total'];
}
?>

<!-- Menu de contexto -->
<div class="context-menu" id="contextMenu">
  <ul>
    <li onclick="removerServico()">🗑️ Remover</li>
    <li onclick="abrirModalEditar()">✏️ Editar</li>
  </ul>
</div>

<!-- Modal de edição -->
<div class="modal" id="modalEditar">
  <div class="modal-content" style="
      font-family: 'Segoe UI', sans-serif;
      padding: 1.5rem 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
      max-width: 600px;
      width: 95%;
      margin: 5% auto;
      box-sizing: border-box;
    ">
    <form method="POST" action="editar.php" style="display: flex; flex-direction: column; gap: 1.2rem;">
      <input type="hidden" name="id" id="edit-id">
      <div>
        <label for="edit-nome" style="font-weight: 500; margin-bottom: 0.3rem; display: block;">Nome</label>
        <input type="text" name="nome" id="edit-nome" required
               style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #ccc; border-radius: 6px;
                      font-size: 0.95rem; box-sizing: border-box;">
      </div>
      <div>
        <label for="edit-categoria" style="font-weight: 500; margin-bottom: 0.3rem; display: block;">Categoria</label>
        <select name="categoria" id="edit-categoria" required
                style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #ccc; border-radius: 6px;
                       font-size: 0.95rem; box-sizing: border-box;">
          <option value="Infraestrutura">Infraestrutura</option>
          <option value="Rede">Rede</option>
          <option value="Seginfo">Seginfo</option>
        </select>
      </div>
      <div>
        <label for="edit-link" style="font-weight: 500; margin-bottom: 0.3rem; display: block;">Link</label>
        <input type="url" name="link" id="edit-link" required
               style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #ccc; border-radius: 6px;
                      font-size: 0.95rem; box-sizing: border-box;">
      </div>
      <button type="submit"
              style="width: 100%; background-color: #2d98f0; color: white; padding: 0.65rem 1.2rem;
                     border: none; border-radius: 6px; font-size: 0.95rem; font-weight: 500; cursor: pointer;
                     transition: background 0.3s ease; box-sizing: border-box;">
        Salvar Alterações
      </button>
    </form>
  </div>
</div>

<!-- Script -->
<script>
let selectedBox = null;
// Exibe menu de contexto ao clicar com o botão direito
document.querySelectorAll('.card').forEach(box => {
  box.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    selectedBox = this;
    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';
    menu.style.left = `${e.pageX}px`;
    menu.style.top = `${e.pageY}px`;
  });
});
// Oculta menu de contexto ao clicar fora
document.addEventListener('click', () => {
  document.getElementById('contextMenu').style.display = 'none';
});

// Remove serviço com confirmação
function removerServico() {
  const id = selectedBox.dataset.id;
  if (confirm("Remover esta caixa permanentemente?")) {
    fetch('remover.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(id)
    }).then(res => res.ok ? selectedBox.remove() : alert("Erro ao remover."));
  }
}

// Abre modal de edição e preenche dados
function abrirModalEditar() {
  document.getElementById("modalEditar").style.display = "block";
  document.getElementById("edit-id").value = selectedBox.dataset.id;
  document.getElementById("edit-nome").value = selectedBox.dataset.nome;
  document.getElementById("edit-categoria").value = selectedBox.dataset.categoria;
  document.getElementById("edit-link").value = selectedBox.dataset.link;
}
// Fecha modal ao clicar fora
document.getElementById("modalEditar").addEventListener("click", function(e) {
  if (e.target === this) this.style.display = "none";
});

// Toggle do formulário de adição
function toggleForm() {
  const form = document.getElementById("formAdicionar");
  const btn = document.querySelector(".botao-circular[title='Adicionar']");
  form.classList.toggle("show");
  btn.style.backgroundColor = form.classList.contains("show") ? "#e74c3c" : "#2d98f0";
  btn.innerText = form.classList.contains("show") ? "✖" : "+";
}
// Toggle da área de busca
function toggleBusca() {
  const buscaContainer = document.getElementById("buscaContainer");
  buscaContainer.classList.toggle("show");
}

// Debounce para campo de busca
let debounceTimer;
const buscaInput = document.getElementById("busca");
buscaInput.addEventListener("input", () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    document.getElementById("filtroForm").submit();
  }, 400);
});
// Submit ao selecionar categoria
document.getElementById("categoria").addEventListener("change", () => {
  document.getElementById("filtroForm").submit();
});
// Focar no campo de busca ao carregar
window.addEventListener("DOMContentLoaded", () => {
  const busca = document.getElementById("busca");
  busca.focus();
  if (busca.value.length > 0) {
    const val = busca.value;
    busca.value = "";
    busca.value = val;
  }

  // Exibe a área de busca automaticamente se já houver um termo de busca ou filtro selecionado
  const buscaContainer = document.getElementById("buscaContainer");
  const categoriaSelect = document.querySelector("#buscaContainer select");
  if (busca.value.trim().length > 0 || (categoriaSelect && categoriaSelect.value && categoriaSelect.value !== 'Todos')) {
    buscaContainer.classList.add('show');
  }
});
</script>

<!-- Exibe contagem de serviços por categoria -->
<hr style="margin: 2rem 0; border: none; border-top: 1px solid #eee;">
<div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; font-family: 'Segoe UI', sans-serif;">
  <div style="background: #f4faff; border-left: 4px solid #3498db; padding: 1rem 1.5rem; border-radius: 6px;">
    <strong>Infraestrutura:</strong> <?= $totais['Infraestrutura'] ?> serviço(s)
  </div>
  <div style="background: #f5fdf7; border-left: 4px solid #2ecc71; padding: 1rem 1.5rem; border-radius: 6px;">
    <strong>Rede:</strong> <?= $totais['Rede'] ?> serviço(s)
  </div>
  <div style="background: #fff3f2; border-left: 4px solid #e74c3c; padding: 1rem 1.5rem; border-radius: 6px;">
    <strong>Seginfo:</strong> <?= $totais['Seginfo'] ?> serviço(s)
  </div>
</div>

</body>
</html>
