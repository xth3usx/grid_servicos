<?php
$conn = new mysqli("localhost", "user", "pass", "db");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$id = $_POST['id'] ?? '';
$nome = $_POST['nome'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$link = $_POST['link'] ?? '';

if ($id && $nome && $categoria && $link) {
  $stmt = $conn->prepare("UPDATE servicos SET nome = ?, categoria = ?, link = ? WHERE id = ?");
  $stmt->bind_param("sssi", $nome, $categoria, $link, $id);
  $stmt->execute();
  $stmt->close();
}

$conn->close();

header("Location: index.php");
exit;
