<?php
if (!isset($_POST['id'])) {
  http_response_code(400);
  exit;
}

$conn = new mysqli("localhost", "user", "pass", "db");
if ($conn->connect_error) {
  http_response_code(500);
  exit;
}

$id = (int) $_POST['id'];
$conn->query("UPDATE servicos SET ativo = 0 WHERE id = $id");
$conn->close();
