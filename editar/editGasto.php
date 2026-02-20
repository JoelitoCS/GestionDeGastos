<?php
session_start();
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? null;
    $monto       = $_POST['monto'] ?? null;
    $categoriaId = $_POST['categoria_id'] ?? null;
    $desc        = $_POST['descripcion'] ?? null;
    $fecha       = $_POST['fecha'] ?? null;
    $esRecu      = isset($_POST['es_recurrente']) ? 1 : 0;

    if ($id && $monto && $categoriaId && $fecha) {
        $stmt = $mysqli->prepare("UPDATE gastos SET monto = ?, categoria_id = ?, descripcion = ?, fecha = ?, es_recurrente = ? WHERE id = ?");
        $stmt->bind_param("dissii", $monto, $categoriaId, $desc, $fecha, $esRecu, $id);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}
?>