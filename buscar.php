$conn = new mysqli("localhost", "user", "pass", "db");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT * FROM servicos WHERE ativo = 1";

if (!empty($categoria) && $categoria !== 'Todos') {
  $sql .= " AND categoria = '" . $conn->real_escape_string($categoria) . "'";
}

if (!empty($busca)) {
  $sql .= " AND nome LIKE '%" . $conn->real_escape_string($busca) . "%'";
}

$sql .= " ORDER BY categoria, nome";
$result = $conn->query($sql);

if ($result->num_rows > 0):
  while($row = $result->fetch_assoc()):
    $classe = strtolower($row['categoria']) === 'infraestrutura' ? 'infra' :
              (strtolower($row['categoria']) === 'rede' ? 'rede' : 'seginfo');
    echo '<a href="' . htmlspecialchars($row['link']) . '" class="box ' . $classe . '" target="_blank">';
    echo '<div class="titulo">' . htmlspecialchars($row['nome']) . '</div>';
    echo '<div class="categoria">' . htmlspecialchars($row['categoria']) . '</div>';
    echo '</a>';
  endwhile;
else:
  echo "<p style='grid-column: span 5; text-align:center;'>Nenhum serviço encontrado.</p>";
endif;

$conn->close();
