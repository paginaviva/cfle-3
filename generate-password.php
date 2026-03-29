<?php
/**
 * Script utilitario para generar hashes bcrypt de contraseñas
 * 
 * USO:
 * 1. Ejecutar desde línea de comandos:
 *    php generate-password.php
 * 
 * 2. O modificar la línea 12 con tu contraseña deseada y ejecutar:
 *    php generate-password.php
 * 
 * 3. Copiar el hash generado y pegarlo en config/config.php
 */

// Contraseña a hashear (puedes cambiarla aquí o pasarla por argumento)
$password = $argv[1] ?? 'admin123';

if (php_sapi_name() !== 'cli') {
    echo "Este script debe ejecutarse desde línea de comandos.\n";
    exit(1);
}

echo "==========================================\n";
echo "Generador de Hash Bcrypt para EnDES\n";
echo "==========================================\n\n";

echo "Contraseña: " . str_repeat('*', strlen($password)) . "\n\n";

$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Hash generado:\n";
echo "----------------------------------------\n";
echo $hash . "\n";
echo "----------------------------------------\n\n";

echo "Instrucciones:\n";
echo "1. Copia el hash anterior (incluyendo \$2y\$...)\n";
echo "2. Abre config/config.php\n";
echo "3. Reemplaza el hash del usuario 'admin' con este nuevo hash\n\n";

echo "Ejemplo en config/config.php:\n";
echo "\$users_config = [\n";
echo "    'admin' => '{$hash}',\n";
echo "];\n\n";

// Verificación
echo "Verificación:\n";
if (password_verify($password, $hash)) {
    echo "✓ El hash es válido. La contraseña puede verificarse correctamente.\n";
} else {
    echo "✗ ERROR: El hash no es válido.\n";
}
