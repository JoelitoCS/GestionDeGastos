<?php
session_start();

$json = file_get_contents("../bd/bd.json");
$datos = json_decode($json, true);

// Acceder al array de usuarios dentro del JSON
$usuarios = $datos['usuarios'];

$error = "";

if (isset($_POST['nombre'], $_POST['contrasenya'])) {
    $nombre = $_POST['nombre'];
    $contrasenya = $_POST['contrasenya'];

    foreach ($usuarios as $u) {
        if ($u['nombre'] === $nombre && $u['password'] === $contrasenya) {
            $_SESSION['usuario.nombre'] = $nombre;
            $_SESSION['usuario.password'] = $contrasenya;
            header("Location: ../index.php");
            exit();
        }
    }
    $error = "Credencials incorrectes. Torna-ho a intentar.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'badalona-blue': '#2C3E73',
                        'badalona-light': '#E8EBF0',
                        'badalona-dark': '#1A2847',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-badalona-light via-white to-blue-50 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
       
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-badalona-blue mb-2">Gestión de Gastos e Ingresos</h1>
            <img src="../imagenes/logo.png" alt="Reactivació Badalona" class="mx-auto mb-4 h-24 w-[270px] object-contain">
        </div>

     
        <div class="bg-white rounded-2xl shadow-2xl p-8 border-t-4 border-badalona-blue">
            
            <form method="POST" class="space-y-6">
                
                <
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                
                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-badalona-blue focus:border-transparent transition duration-200 outline-none"
                            placeholder="Introdueix el teu usuari"
                        >
                    </div>
                </div>

                
                <div>
                    <label for="contrasenya" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="contrasenya" 
                            name="contrasenya" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-badalona-blue focus:border-transparent transition duration-200 outline-none"
                            placeholder="Introdueix la teva contrasenya"
                        >
                    </div>
                </div>

                
                <button 
                    type="submit"
                    class="w-full bg-badalona-blue hover:bg-badalona-dark text-white font-semibold py-3 px-4 rounded-lg transition duration-300 transform shadow-lg hover:shadow-xl"
                >
                    Iniciar Sesión
                </button>

            </form>

           
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    © 2026 Gestion de Gastos. Todos los derechos reservados.
                </p>
            </div>

        </div>

    </div>

</body>
</html>