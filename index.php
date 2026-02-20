<?php
session_start();
require 'config.php';

if (!isset($_SESSION['usuario.nombre']) || $_SESSION['usuario.nombre'] !== 'albert') {
    // Si no está logueado o el nombre no es 'albert', redirige al login
    header("Location: sesiones/login.php");
    exit();
}

// Mes y año actual
$mes = date('m');
$año = date('Y');

// Total ingresos del mes
$stmtIngresos = $mysqli->prepare("SELECT COALESCE(SUM(monto), 0) AS total FROM ingresos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
$stmtIngresos->bind_param("ii", $mes, $año);
$stmtIngresos->execute();
$totalIngresos = $stmtIngresos->get_result()->fetch_assoc()['total'];

// Total gastos del mes
$stmtGastos = $mysqli->prepare("SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
$stmtGastos->bind_param("ii", $mes, $año);
$stmtGastos->execute();
$totalGastos = $stmtGastos->get_result()->fetch_assoc()['total'];

// Diferencia
$diferencia = $totalIngresos - $totalGastos;

// Últimos ingresos
$ultimosIngresos = $mysqli->query("
    SELECT i.monto, i.descripcion, i.fecha, c.nombre AS categoria
    FROM ingresos i
    JOIN categorias c ON i.categoria_id = c.id
    ORDER BY i.fecha DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Últimos gastos
$ultimosGastos = $mysqli->query("
    SELECT g.monto, g.descripcion, g.fecha, c.nombre AS categoria
    FROM gastos g
    JOIN categorias c ON g.categoria_id = c.id
    ORDER BY g.fecha DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Financiero</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Mono', monospace; }
        h1, h2, h3, .display { font-family: 'Syne', sans-serif; }

        body {
            background-color: #0a0a0a;
            background-image:
                radial-gradient(ellipse 80% 50% at 20% 10%, rgba(34,197,94,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(239,68,68,0.06) 0%, transparent 60%);
        }

        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(10px);
        }

        .card-ingreso { border-left: 3px solid #22c55e; }
        .card-gasto   { border-left: 3px solid #ef4444; }
        .card-diff-pos { border-left: 3px solid #3b82f6; }
        .card-diff-neg { border-left: 3px solid #f97316; }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .row-hover:hover {
            background: rgba(255,255,255,0.04);
            transition: background 0.15s ease;
        }

        .badge {
            font-size: 0.65rem;
            letter-spacing: 0.08em;
            padding: 2px 8px;
            border-radius: 99px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease forwards; }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }
        .delay-4 { animation-delay: 0.4s; opacity: 0; }
        .delay-5 { animation-delay: 0.5s; opacity: 0; }
    </style>
</head>
<body class="min-h-screen text-white px-6 py-10">

    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-10 fade-up">
        <p class="text-xs tracking-widest text-white/30 uppercase mb-1"><?= date('F Y') ?></p>
        <h1 class="text-4xl font-extrabold tracking-tight">Panel Financiero</h1>
        <p class="text-white/40 text-sm mt-1">Resumen del mes en curso</p>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">

        <!-- Ingresos -->
        <div class="card card-ingreso rounded-2xl p-6 fade-up delay-1">
            <p class="text-xs tracking-widest uppercase text-white/30 mb-3">Ingresos totales</p>
            <p class="stat-value text-4xl text-green-400">
                +<?= number_format($totalIngresos, 2, ',', '.') ?> €
            </p>
            <p class="text-white/20 text-xs mt-3">entradas este mes</p>
        </div>

        <!-- Gastos -->
        <div class="card card-gasto rounded-2xl p-6 fade-up delay-2">
            <p class="text-xs tracking-widest uppercase text-white/30 mb-3">Gastos totales</p>
            <p class="stat-value text-4xl text-red-400">
                -<?= number_format($totalGastos, 2, ',', '.') ?> €
            </p>
            <p class="text-white/20 text-xs mt-3">salidas este mes</p>
        </div>

        <!-- Diferencia -->
        <div class="card <?= $diferencia >= 0 ? 'card-diff-pos' : 'card-diff-neg' ?> rounded-2xl p-6 fade-up delay-3">
            <p class="text-xs tracking-widest uppercase text-white/30 mb-3">Balance neto</p>
            <p class="stat-value text-4xl <?= $diferencia >= 0 ? 'text-blue-400' : 'text-orange-400' ?>">
                <?= ($diferencia >= 0 ? '+' : '') . number_format($diferencia, 2, ',', '.') ?> €
            </p>
            <p class="text-white/20 text-xs mt-3"><?= $diferencia >= 0 ? 'superávit' : 'déficit' ?> del mes</p>
        </div>

    </div>

    <!-- Tablas -->
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Últimos ingresos -->
        <div class="card rounded-2xl p-6 fade-up delay-4">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold tracking-widest uppercase text-white/60">Últimos ingresos</h2>
                <a href="agregar_ingreso.php" class="text-xs text-green-400 hover:text-green-300 transition">+ Añadir</a>
            </div>
            <div class="space-y-1">
                <?php if (empty($ultimosIngresos)): ?>
                    <p class="text-white/20 text-xs py-4 text-center">Sin ingresos este mes</p>
                <?php else: ?>
                    <?php foreach ($ultimosIngresos as $ingreso): ?>
                    <div class="row-hover flex items-center justify-between rounded-lg px-3 py-3">
                        <div class="flex flex-col">
                            <span class="text-sm text-white/80"><?= htmlspecialchars($ingreso['descripcion'] ?? '—') ?></span>
                            <span class="text-xs text-white/30 mt-0.5"><?= htmlspecialchars($ingreso['categoria']) ?> · <?= date('d/m', strtotime($ingreso['fecha'])) ?></span>
                        </div>
                        <span class="text-green-400 font-medium text-sm">+<?= number_format($ingreso['monto'], 2, ',', '.') ?> €</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimos gastos -->
        <div class="card rounded-2xl p-6 fade-up delay-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold tracking-widest uppercase text-white/60">Últimos gastos</h2>
                <a href="agregar_gasto.php" class="text-xs text-red-400 hover:text-red-300 transition">+ Añadir</a>
            </div>
            <div class="space-y-1">
                <?php if (empty($ultimosGastos)): ?>
                    <p class="text-white/20 text-xs py-4 text-center">Sin gastos este mes</p>
                <?php else: ?>
                    <?php foreach ($ultimosGastos as $gasto): ?>
                    <div class="row-hover flex items-center justify-between rounded-lg px-3 py-3">
                        <div class="flex flex-col">
                            <span class="text-sm text-white/80"><?= htmlspecialchars($gasto['descripcion'] ?? '—') ?></span>
                            <span class="text-xs text-white/30 mt-0.5"><?= htmlspecialchars($gasto['categoria']) ?> · <?= date('d/m', strtotime($gasto['fecha'])) ?></span>
                        </div>
                        <span class="text-red-400 font-medium text-sm">-<?= number_format($gasto['monto'], 2, ',', '.') ?> €</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>