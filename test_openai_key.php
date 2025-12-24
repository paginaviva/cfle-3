<?php
/**
 * Test rápido para verificar si una API Key de OpenAI (APIO) es válida.
 * Usa el endpoint /v1/models, que es ligero y no consume tokens.
 */

$apiKey = getenv('OPENAI_API_KEY'); // <-- Usa una variable de entorno para la API Key
$endpoint = "https://api.openai.com/v1/models";

// Preparar solicitud
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "❌ Error cURL: " . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Analizar respuesta según código HTTP
if ($httpCode === 200) {
    echo "✅ API Key válida y funcionando.\n";
    echo "Respuesta del servidor:\n$response";
} elseif ($httpCode === 401) {
    echo "❌ API Key inválida o no autorizada (401 Unauthorized).\n";
} elseif ($httpCode === 429) {
    echo "⚠️ API Key válida pero la cuenta tiene rate-limit o problemas de crédito (429 Too Many Requests).\n";
} else {
    echo "⚠️ Algo ocurrió. Código HTTP: $httpCode\n";
    echo "Respuesta:\n$response\n";
}
