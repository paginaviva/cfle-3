<?php
// config/config.php

// Cargar configuración de API Key (archivo camuflado)
require_once __DIR__ . '/api-key.php';

// Definir rutas base
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
if (!defined('DOCS_PATH')) define('DOCS_PATH', BASE_PATH . '/docs');
if (!defined('LOGS_PATH')) define('LOGS_PATH', BASE_PATH . '/logs');

// Configuracion de la App
if (!defined('APP_TITLE')) define('APP_TITLE', 'Gestor de Fichas Tecnicas');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');

// OpenAI Configuration
// La API Key se carga desde api-key.php, .env o variable de entorno
if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
    // Intentar cargar desde archivo .env si existe
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $envContents = file_get_contents($envFile);
        if (preg_match('/OPENAI_API_KEY=(.*)/', $envContents, $matches)) {
            $apiKey = trim($matches[1]);
            if ($apiKey && $apiKey !== 'TU_API_KEY_AQUI') {
                define('OPENAI_API_KEY', $apiKey);
            } else {
                define('OPENAI_API_KEY', 'TU_API_KEY_AQUI');
            }
        } else {
            define('OPENAI_API_KEY', 'TU_API_KEY_AQUI');
        }
    } else {
        // Fallback a variable de entorno (Railway)
        $envApiKey = getenv('OPENAI_API_KEY');
        if ($envApiKey) {
            define('OPENAI_API_KEY', $envApiKey);
        } else {
            define('OPENAI_API_KEY', 'TU_API_KEY_AQUI');
        }
    }
}
if (!defined('OPENAI_API_KEY_DEFAULT')) define('OPENAI_API_KEY_DEFAULT', OPENAI_API_KEY);
if (!defined('OPENAI_ASSISTANT_ID')) define('OPENAI_ASSISTANT_ID', 'asst_...'); // Legacy - no usado actualmente
if (!defined('OPENAI_MODEL_ID')) define('OPENAI_MODEL_ID', 'gpt-5.1');
if (!defined('OPENAI_RESPONSES_URL')) define('OPENAI_RESPONSES_URL', 'https://api.openai.com/v1/responses');
if (!defined('OPENAI_FILES_URL')) define('OPENAI_FILES_URL', 'https://api.openai.com/v1/files');
if (!defined('OPENAI_THREADS_URL')) define('OPENAI_THREADS_URL', 'https://api.openai.com/v1/threads');

// Modo Demo/Pruebas
// true = Ejecutar OpenAI normalmente (producción)
// false = Saltar OpenAI, mostrar resultados mock (ahorro de tokens/dinero)
if (!defined('OPENAI_ENABLED')) define('OPENAI_ENABLED', true);

// Upload Configuration
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', DOCS_PATH . '/');

// Configuracion de Usuarios
// Para generar un hash: php -r "echo password_hash('tu-password', PASSWORD_BCRYPT);"
// Password por defecto: "admin123" - CAMBIAR en producción
$users_config = [
    'admin' => '$2y$10$fJfIuvIY35xcuvbCgaCMWe1mZg./gDeTRqCNiU8QFTdt/1Cd5v2sW', // admin123
];


return [
    'users' => $users_config,
    'paths' => [
        'docs' => DOCS_PATH,
        'logs' => LOGS_PATH
    ],
    'openai' => [
        'api_key' => OPENAI_API_KEY,
        'assistant_id' => OPENAI_ASSISTANT_ID,
        'model_id' => OPENAI_MODEL_ID
    ]
];
