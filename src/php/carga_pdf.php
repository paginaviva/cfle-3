<?php
// src/php/carga_pdf.php
require_once __DIR__ . '/../Service/AuthService.php';

// Cargar configuración
$config = require __DIR__ . '/../../config/config.php';
$auth = new AuthService($config);

// Proteger ruta
$auth->requireLogin();

$uploadedFile = null;
$error = null;
$processingResult = null;

// Cargar configuración de prompts
$promptsConfig = require __DIR__ . '/../../config/prompts.php';
$promptsData = $promptsConfig['prompts'] ?? [];

// --- LÓGICA DE SUBIDA (Paso 1) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        
        $nombreTmp  = $_FILES['pdf']['tmp_name'];
        $nombreOrig = basename($_FILES['pdf']['name']);
        
        // Crear carpeta basada en el nombre del archivo (sin extensión)
        $nombreCarpeta = pathinfo($nombreOrig, PATHINFO_FILENAME);
        $nombreCarpeta = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreCarpeta);
        
        $targetDir = DOCS_PATH . '/' . $nombreCarpeta . '/';
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        
        $rutaDestino = $targetDir . $nombreOrig;

        if (move_uploaded_file($nombreTmp, $rutaDestino)) {
            $uploadedFile = [
                'path' => $rutaDestino,
                'name' => $nombreOrig,
                'initial_prompt' => $_POST['initial_prompt'] ?? null
            ];
        } else {
            $error = "Error al guardar el archivo.";
        }
    } else {
        $error = "Error en la subida: " . $_FILES['pdf']['error'];
    }
}

// --- LÓGICA DE PROCESAMIENTO (Paso 2 - Placeholder) ---
// --- LÓGICA DE PROCESAMIENTO (Paso 2 - Subida a OpenAI) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filepath']) && !isset($_FILES['pdf'])) {
    $rutaDestino = $_POST['filepath'];
    // Validación de seguridad
    $realUploadDir = realpath(DOCS_PATH);
    $realFilePath = realpath($rutaDestino);

    if ($realFilePath && strpos($realFilePath, $realUploadDir) === 0 && file_exists($realFilePath)) {
         $uploadedFile = [
            'path' => $realFilePath,
            'name' => basename($realFilePath),
            'initial_prompt' => $_POST['prompt_select'] ?? null
        ];
        
        // Configurar Logger
        $logFile = dirname($realFilePath) . '/process.log';
        $log = function($msg) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] $msg" . PHP_EOL, FILE_APPEND);
        };

        $log("Iniciando proceso para: " . $uploadedFile['name']);
        $log("Prompt seleccionado: " . ($uploadedFile['initial_prompt'] ?? 'None'));

        try {
            // Instanciar Cliente OpenAI
            require_once __DIR__ . '/../OpenAIClient.php';
            $client = new OpenAIClient($config['openai_api_key'] ?? OPENAI_API_KEY);

            // Subir archivo a OpenAI
            $log("Subiendo archivo a OpenAI...");
            $uploadResult = $client->uploadFile($realFilePath, 'user_data');
            
            if (!isset($uploadResult['id'])) {
                throw new \RuntimeException("La respuesta de OpenAI no contiene un ID de archivo.");
            }

            $fileId = $uploadResult['id'];
            $log("Archivo subido con éxito. ID: $fileId");

            // Guardar file_id
            file_put_contents($realFilePath . '.file_id', $fileId);
            $_SESSION['current_file_id'] = $fileId;

            // Obtener prompt y modelo
            $promptKey = $uploadedFile['initial_prompt'] ?? array_key_first($promptsData);
            $promptName = $promptsData[$promptKey]['name'] ?? $promptKey;
            $promptText = $promptsData[$promptKey]['prompt_text'] ?? "Analiza este documento.";
            $log("Prompt seleccionado: $promptName");

            // Modelo seleccionado
            $model = $_POST['parametro_2'] ?? OPENAI_MODEL_ID;
            
            // Validar modelo para Responses API
            $allowedModelsForResponses = ['gpt-5.1'];
            if (!in_array($model, $allowedModelsForResponses, true)) {
                $log("Modelo no válido para Responses: $model. Forzando a 'gpt-5.1'.");
                $model = 'gpt-5.1';
            }

            $log("Iniciando llamada a Responses con modelo: $model");

            // Construir payload para Responses API (Opción A: Lectura directa del PDF)
            $userContent = "Analiza el archivo PDF adjunto siguiendo exactamente las instrucciones anteriores "
                . "y devuelve un único objeto JSON con la clave Matriz.";

            $payload = [
                'model' => $model,
                'instructions' => $promptText,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $userContent
                            ],
                            [
                                'type' => 'input_file',
                                'file_id' => $fileId
                            ]
                        ]
                    ]
                ],
                'stream' => false
            ];

            // Llamar a Responses API
            $response = $client->callResponses($payload);
            $log("Llamada a Responses completada.");

            // Guardar respuesta completa para debugging
            $log("Respuesta completa: " . substr(json_encode($response), 0, 500) . "...");

            // Extraer texto de la respuesta
            $outputItems = $response['output'] ?? [];
            if (empty($outputItems)) {
                throw new \RuntimeException("La respuesta de OpenAI no contiene 'output'. Respuesta completa: " . json_encode($response));
            }

            $lastOutput = end($outputItems);
            $contentItems = $lastOutput['content'] ?? [];

            if (empty($contentItems) || !isset($contentItems[0]['text'])) {
                throw new \RuntimeException("La respuesta de OpenAI no contiene texto en 'output[..].content[..].text'. Respuesta completa: " . json_encode($response));
            }

            $lastMessage = $contentItems[0]['text'];
            $log("Texto recibido del modelo (primeros 200 chars): " . substr($lastMessage, 0, 200));

            // === EXTRACCIÓN MULTI-ESTRATEGIA ===
            $jsonOutput = null;

            // Estrategia 1: Buscar bloque markdown ```json ... ```
            if (preg_match('/```json\s*(\{.*?\})\s*```/s', $lastMessage, $matches)) {
                $jsonOutput = $matches[1];
                $log("JSON extraído vía estrategia 1 (markdown block)");
            }
            // Estrategia 2: Intentar parsear la respuesta completa como JSON
            elseif (json_decode($lastMessage, true) !== null) {
                $jsonOutput = $lastMessage;
                $log("JSON extraído vía estrategia 2 (respuesta completa es JSON)");
            }
            // Estrategia 3: Buscar patrón {"Matriz": [...]}
            elseif (preg_match('/\{\s*"Matriz"\s*:\s*\[.*?\]\s*\}/s', $lastMessage, $matches)) {
                $jsonOutput = $matches[0];
                $log("JSON extraído vía estrategia 3 (búsqueda de patrón Matriz)");
            }
            else {
                $log("ERROR: No se pudo extraer JSON válido de la respuesta");
                throw new \RuntimeException("No se pudo extraer JSON válido de la respuesta del modelo.");
            }

            // === VALIDACIÓN ===
            $matrizData = json_decode($jsonOutput, true);
            
            if ($matrizData === null) {
                $log("ERROR: JSON malformado - " . json_last_error_msg());
                throw new \RuntimeException("El JSON extraído está malformado: " . json_last_error_msg());
            }

            if (!isset($matrizData['Matriz'])) {
                $log("ERROR: Falta clave 'Matriz' en JSON");
                throw new \RuntimeException("El JSON no contiene la clave 'Matriz'.");
            }

            if (!is_array($matrizData['Matriz'])) {
                $log("ERROR: 'Matriz' no es un array");
                throw new \RuntimeException("La clave 'Matriz' no contiene un array.");
            }

            $elementCount = count($matrizData['Matriz']);
            $log("JSON validado correctamente - $elementCount elementos en Matriz");

            // === NORMALIZACIÓN ===
            // Re-codificar para limpiar y estandarizar
            $jsonOutput = json_encode($matrizData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            // Guardar resultado en archivo
            $resultFile = $realFilePath . '.result.json';
            file_put_contents($resultFile, $jsonOutput);
            $log("Resultado guardado en: " . basename($resultFile));

            $processingResult = $jsonOutput;
            $log("Proceso finalizado con éxito.");

        } catch (\Exception $e) {
            $error = "Error en el proceso: " . $e->getMessage();
            $log("ERROR: " . $e->getMessage());
        }

    } else {
        $error = "Archivo no válido o no encontrado.";
    }
}

include __DIR__ . '/layout_header.php';
?>

<div style="text-align: center; padding: 4rem 0;">
    <h2>Procesar Ficha Técnica</h2>
    <p style="color: #6b7280; margin-top: 1rem;">
        Sube un PDF para que el Agente IA lo analice.
    </p>
    
    <div style="margin-top: 2rem; padding: 2rem; border: 2px dashed #d1d5db; border-radius: 0.5rem; background-color: #f9fafb; max-width: 600px; margin-left: auto; margin-right: auto;">
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($processingResult): ?>
            <!-- Step 3: Result Display -->
            <div style="text-align: left; background: white; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0; color: #059669;">✅ Proceso Completado</h3>
                
                <p style="color: #374151; margin-bottom: 1rem;">
                    El análisis se ha completado correctamente. Se han extraído <strong><?php 
                        $data = json_decode($processingResult, true);
                        echo count($data['Matriz'] ?? []);
                    ?> elementos</strong>.
                </p>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Vista Previa del JSON:</label>
                    <pre style="background: #f3f4f6; padding: 1rem; border-radius: 0.375rem; overflow-x: auto; max-height: 300px; font-size: 0.875rem; border: 1px solid #d1d5db;"><?php echo htmlspecialchars(substr($processingResult, 0, 1000)); ?><?php if (strlen($processingResult) > 1000) echo "\n\n... (truncado)"; ?></pre>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                    <a href="data:application/json;charset=utf-8,<?php echo rawurlencode($processingResult); ?>" 
                       download="resultado_<?php echo date('YmdHis'); ?>.json"
                       class="btn btn-primary" 
                       style="flex: 1; padding: 0.75rem; text-decoration: none; text-align: center; min-width: 200px;">
                        📥 Descargar JSON Completo
                    </a>
                    
                    <?php 
                        $promptKey = $uploadedFile['initial_prompt'] ?? array_key_first($promptsData);
                        $visualizadorUrl = $promptsData[$promptKey]['visualizador'] ?? 'visualizador_cfle.php';
                    ?>
                    <form method="POST" action="<?php echo htmlspecialchars($visualizadorUrl); ?>" target="_blank" style="flex: 1; min-width: 200px; margin: 0;">
                        <input type="hidden" name="json_data" value="<?php echo htmlspecialchars($processingResult); ?>">
                        <button type="submit" class="btn" style="background: #3b82f6; color: white; padding: 0.75rem; border-radius: 0.375rem; text-align: center; width: 100%; border: none; cursor: pointer; font-size: 1rem;">
                            👁️ Visualizar JSON
                        </button>
                    </form>
                </div>
                
                <div style="margin-top: 1rem;">
                    <a href="carga_pdf.php" 
                       class="btn" 
                       style="background: #10b981; color: white; text-decoration: none; padding: 0.75rem; border-radius: 0.375rem; text-align: center; display: block; width: 100%;">
                        🔄 Procesar Otro Archivo
                    </a>
                </div>
            </div>

        <?php elseif ($uploadedFile): ?>
            <!-- Step 2: Confirmation and Process Button -->
            <div style="text-align: left; background: white; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                <h3 style="margin-top: 0; color: #059669;">¡Archivo Subido Correctamente!</h3>
                <p style="color: #374151; margin-bottom: 1.5rem;">
                    Has subido: <strong><?php echo htmlspecialchars($uploadedFile['name']); ?></strong>
                </p>

                <form action="carga_pdf.php" method="post" style="display: flex; flex-direction: column; gap: 1rem;" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerText = 'Procesando...';">
                    <input type="hidden" name="filepath" value="<?php echo htmlspecialchars($uploadedFile['path']); ?>">
                    
                    <!-- Prompt Seleccionado (Estático) -->
                    <?php 
                        $selectedPromptKey = $uploadedFile['initial_prompt'] ?? array_key_first($promptsData);
                        $selectedPromptName = $promptsData[$selectedPromptKey]['name'] ?? $selectedPromptKey;
                        $selectedPromptDesc = $promptsData[$selectedPromptKey]['description'] ?? '';
                    ?>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Prompt Seleccionado</label>
                        <div style="padding: 0.75rem; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 0.375rem; color: #111827;">
                            <strong><?php echo htmlspecialchars($selectedPromptName); ?></strong>
                            <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; margin-bottom: 0;"><?php echo htmlspecialchars($selectedPromptDesc); ?></p>
                        </div>
                        <input type="hidden" name="prompt_select" value="<?php echo htmlspecialchars($selectedPromptKey); ?>">
                    </div>

                    <!-- Contenedor de Parámetros Dinámicos -->
                    <div id="dynamic_parameters">
                        <!-- Se llena vía JS -->
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.75rem;">
                            Ejecutar Análisis IA
                        </button>
                        <a href="carga_pdf.php" class="btn" style="background: #9ca3af; color: white; text-decoration: none; padding: 0.75rem; border-radius: 0.375rem; text-align: center;">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <script>
                // Pasar configuración de PHP a JS
                const promptsConfig = <?php echo json_encode($promptsData); ?>;
                const selectedPromptKey = "<?php echo $selectedPromptKey; ?>";
                const postData = <?php echo json_encode($_POST); ?>; // Datos POST para preservar valores
                const paramsContainer = document.getElementById('dynamic_parameters');

                function renderParameters(promptKey) {
                    paramsContainer.innerHTML = ''; // Limpiar
                    const promptData = promptsConfig[promptKey];
                    
                    if (!promptData) return;

                    // Renderizar parámetros
                    if (promptData.pprompt_parametros) {
                        for (const [paramKey, paramConfig] of Object.entries(promptData.pprompt_parametros)) {
                            const wrapper = document.createElement('div');
                            wrapper.style.marginBottom = '1rem';

                            const label = document.createElement('label');
                            label.htmlFor = paramKey;
                            label.style.display = 'block';
                            label.style.fontWeight = '600';
                            label.style.marginBottom = '0.5rem';
                            label.style.color = '#374151';
                            label.textContent = (paramConfig.Etiqueta && paramConfig.Etiqueta[0]) ? paramConfig.Etiqueta[0] : paramKey;
                            wrapper.appendChild(label);

                            const type = (paramConfig.Tipo && paramConfig.Tipo[0]) ? paramConfig.Tipo[0] : 'text';
                            // Priorizar valor POST, luego valor por defecto
                            const defaultValue = postData[paramKey] !== undefined ? postData[paramKey] : (paramConfig.valor_defecto || '');

                            if (type === 'select' && paramConfig.opciones) {
                                const select = document.createElement('select');
                                select.name = paramKey;
                                select.id = paramKey;
                                select.style.width = '100%';
                                select.style.padding = '0.5rem';
                                select.style.border = '1px solid #d1d5db';
                                select.style.borderRadius = '0.375rem';
                                select.style.background = 'white';

                                paramConfig.opciones.forEach(opt => {
                                    const option = document.createElement('option');
                                    option.value = opt;
                                    option.textContent = opt;
                                    if (String(opt) === String(defaultValue)) {
                                        option.selected = true;
                                    }
                                    select.appendChild(option);
                                });
                                wrapper.appendChild(select);
                            } else {
                                const input = document.createElement('input');
                                input.type = 'text';
                                input.name = paramKey;
                                input.id = paramKey;
                                input.value = defaultValue;
                                input.style.width = '100%';
                                input.style.padding = '0.5rem';
                                input.style.border = '1px solid #d1d5db';
                                input.style.borderRadius = '0.375rem';
                                wrapper.appendChild(input);
                            }

                            paramsContainer.appendChild(wrapper);
                        }
                    }
                }

                // Render inicial
                if (selectedPromptKey) {
                    renderParameters(selectedPromptKey);
                }
            </script>

        <?php else: ?>
            <!-- Step 1: Upload Form -->
            <form action="carga_pdf.php" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div style="text-align: left;">
                    <label for="pdf" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Archivo PDF</label>
                    <input type="file" name="pdf" id="pdf" accept="application/pdf" required 
                           style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: white;">
                </div>

                <!-- Selector de Prompt en Paso 1 -->
                <div style="text-align: left;">
                    <label for="initial_prompt" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Seleccionar Prompt</label>
                    <select name="initial_prompt" id="initial_prompt" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: white;">
                        <?php foreach ($promptsData as $key => $data): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>">
                                <?php echo htmlspecialchars($data['name'] ?? $key); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.75rem; font-size: 1rem; margin-top: 1rem;">
                    Subir Archivo
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>
