<?php
session_start();
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto       = $_POST['monto'] ?? null;
    $categoriaId = $_POST['categoria_id'] ?? null;
    $desc        = $_POST['descripcion'] ?? null;
    $fecha       = $_POST['fecha'] ?? null;
    $esRecu      = isset($_POST['es_recurrente']) ? 1 : 0;

    if ($monto && $categoriaId && $fecha) {
        $stmt = $mysqli->prepare("INSERT INTO ingresos (monto, categoria_id, descripcion, fecha, es_recurrente) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("dissi", $monto, $categoriaId, $desc, $fecha, $esRecu);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}
?>