<?php
session_start();
require '../config.php';

$filtroCategoria = $_GET['categoria_id'] ?? '';
$filtroFecha     = $_GET['fecha'] ?? '';

$categorias = [];
$resCat = $mysqli->query("SELECT id, nombre FROM categorias WHERE tipo = 'ingreso' ORDER BY nombre");
if ($resCat) {
    while ($row = $resCat->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$where = [];
$params = [];
$types  = '';

if ($filtroCategoria !== '') {
    $where[]  = 'i.categoria_id = ?';
    $params[] = $filtroCategoria;
    $types   .= 'i';
}
if ($filtroFecha !== '') {
    $where[]  = 'DATE(i.fecha) = ?';
    $params[] = $filtroFecha;
    $types   .= 's';
}

$sql = "SELECT i.*, c.nombre AS categoria_nombre
        FROM ingresos i
        LEFT JOIN categorias c ON c.id = i.categoria_id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY i.fecha DESC';

$ingresos = [];
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $ingresos[] = $row;
}

$total = array_sum(array_column($ingresos, 'monto'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresos — Gestor de Gastos</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @keyframes modalIn {
            from { opacity: 0; transform: scale(.96) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-open { animation: modalIn .2s ease; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen">

<nav class="bg-gray-900 border-b border-gray-800 px-6 flex items-center gap-6 h-14">
    <span class="text-emerald-400 font-bold text-lg tracking-tight">💰 GestorGastos</span>
    <a href="#" class="text-gray-500 hover:text-white text-sm font-semibold transition-colors no-underline">Dashboard</a>
    <a href="#" class="text-white text-sm font-semibold no-underline">Ingresos</a>
    <a href="#" class="text-gray-500 hover:text-white text-sm font-semibold transition-colors no-underline">Gastos</a>
    <a href="#" class="text-gray-500 hover:text-white text-sm font-semibold transition-colors no-underline">Categorías</a>
</nav>

<main class="max-w-5xl mx-auto px-6 py-10">

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-white">Mis <span class="text-emerald-400">Ingresos</span></h1>
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-gray-800 border border-gray-700 rounded-xl px-5 py-3 text-right">
                <div class="text-xs text-gray-500 uppercase tracking-widest mb-1">Total filtrado</div>
                <div class="text-2xl font-extrabold text-emerald-400 font-mono">€<?= number_format($total, 2, ',', '.') ?></div>
            </div>
            <button onclick="abrirModal()" class="bg-emerald-400 hover:bg-emerald-500 text-gray-900 font-bold px-5 py-3 rounded-xl text-sm transition-colors cursor-pointer border-0">
                + Nuevo ingreso
            </button>
        </div>
    </div>

    <form method="GET" class="bg-gray-900 border border-gray-800 rounded-2xl px-6 py-5 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Categoría</label>
            <select name="categoria_id" class="bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2 outline-none focus:border-emerald-400 transition-colors">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filtroCategoria == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Fecha</label>
            <input type="date" name="fecha" value="<?= htmlspecialchars($filtroFecha) ?>"
                   class="bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2 outline-none focus:border-emerald-400 transition-colors">
        </div>
        <button type="submit" class="bg-emerald-400 hover:bg-emerald-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm transition-colors cursor-pointer border-0">
            Filtrar
        </button>
        <?php if ($filtroCategoria || $filtroFecha): ?>
            <a href="index.php" class="text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-4 py-2 rounded-lg text-sm font-semibold transition-colors no-underline">
                Limpiar
            </a>
        <?php endif; ?>
    </form>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-800 border-b border-gray-700">
                    <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase tracking-widest font-semibold">Fecha</th>
                    <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase tracking-widest font-semibold">Categoría</th>
                    <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase tracking-widest font-semibold">Descripción</th>
                    <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase tracking-widest font-semibold">Monto</th>
                    <th class="text-left px-5 py-3 text-xs text-gray-500 uppercase tracking-widest font-semibold">Recurrente</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ingresos)): ?>
                    <tr><td colspan="6" class="text-center py-12 text-gray-500 text-sm">No hay ingresos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($ingresos as $ing): ?>
                        <tr class="border-b border-gray-800 last:border-b-0 hover:bg-gray-800 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-400">
                                <?= date('d/m/Y', strtotime($ing['fecha'])) ?>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="bg-gray-800 border border-gray-700 rounded-full text-xs px-3 py-0.5 text-gray-400">
                                    <?= htmlspecialchars($ing['categoria_nombre'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-sm">
                                <?= htmlspecialchars($ing['descripcion'] ?? '—') ?>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-medium text-emerald-400">
                                €<?= number_format($ing['monto'], 2, ',', '.') ?>
                            </td>
                            <td class="px-5 py-3.5">
                                <?php if ($ing['es_recurrente']): ?>
                                    <span class="bg-emerald-950 text-emerald-400 border border-emerald-800 rounded-full text-xs px-3 py-0.5 font-semibold">
                                        ↻ Recurrente
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-600 text-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5">
                                <form method="POST" action="deleteIngreso.php" onsubmit="return confirm('¿Eliminar este ingreso?')">
                                    <input type="hidden" name="id" value="<?= $ing['id'] ?>">
                                    <button type="submit" class="text-red-400 border border-red-900 hover:bg-red-950 px-3 py-1 rounded-lg text-xs font-semibold transition-colors cursor-pointer bg-transparent">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="modalBackdrop" onclick="cerrarModalFuera(event)"
     class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 items-center justify-center">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 w-full max-w-md mx-4 modal-open">
        <h2 class="text-xl font-extrabold text-white mb-6">Nuevo ingreso</h2>
        <form method="POST" action="addIngreso.php">
            <div class="mb-4">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Monto (€)</label>
                <input type="number" name="monto" step="0.01" min="0" placeholder="0.00" required
                       class="w-full bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-emerald-400 transition-colors">
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Categoría</label>
                <select name="categoria_id" required
                        class="w-full bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-emerald-400 transition-colors">
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Descripción</label>
                <textarea name="descripcion" placeholder="Opcional..."
                          class="w-full bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-emerald-400 transition-colors resize-y min-h-16"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 uppercase tracking-widest mb-1.5">Fecha</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required
                       class="w-full bg-gray-950 border border-gray-700 rounded-lg text-white text-sm px-3 py-2.5 outline-none focus:border-emerald-400 transition-colors">
            </div>
            <div class="mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                    <input type="checkbox" name="es_recurrente" class="accent-emerald-400 w-4 h-4">
                    Es un ingreso recurrente
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="cerrarModal()"
                        class="text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-4 py-2 rounded-lg text-sm font-semibold transition-colors cursor-pointer bg-transparent">
                    Cancelar
                </button>
                <button type="submit"
                        class="bg-emerald-400 hover:bg-emerald-500 text-gray-900 font-bold px-5 py-2 rounded-lg text-sm transition-colors cursor-pointer border-0">
                    Guardar ingreso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal() {
        const m = document.getElementById('modalBackdrop');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function cerrarModal() {
        const m = document.getElementById('modalBackdrop');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    function cerrarModalFuera(e) {
        if (e.target === document.getElementById('modalBackdrop')) cerrarModal();
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
</script>

</body>
</html>