<?php
if (!isset($_POST['nome'], $_POST['categoria'], $_POST['link'])) {
  die("Dados incompletos.");
}

$conn = new mysqli("localhost", "user", "pass", "db");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$nome = $conn->real_escape_string($_POST['nome']);
$categoria = $conn->real_escape_string($_POST['categoria']);
$link = $conn->real_escape_string($_POST['link']);

$sql = "INSERT INTO servicos (nome, categoria, link) VALUES ('$nome', '$categoria', '$link')";

if ($conn->query($sql) === TRUE) {
  header("Location: index.php");
  exit;
} else {
  echo "Erro ao adicionar: " . $conn->error;
}

$conn->close();
