<?php
// visualizador_cfle.php
// Visualizador de resultados JSON con JSON Editor (vista de árbol)

$resultado = null;
$error = null;
$jsonRaw = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = $_POST['json_data'] ?? '';

    if (!empty($jsonInput)) {
        // Decodificar desde base64 (enviado desde carga_pdf.php)
        $jsonDecoded = base64_decode($jsonInput);
        
        // Guardar JSON raw para el visualizador
        $jsonRaw = $jsonDecoded;
        
        // Decodificar para validar
        $data = json_decode($jsonDecoded, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $resultado = [];

            // Detectar si tiene clave "Matriz"
            if (isset($data['Matriz']) && is_array($data['Matriz'])) {
                // Procesar cada elemento de Matriz
                foreach ($data['Matriz'] as $index => $elemento) {
                    if (isset($elemento['html_tabla'])) {
                        // Es una tabla HTML ya formateada
                        $titulo = $elemento['titulo_tabla'] ?? 'Tabla ' . ($index + 1);
                        $resultado[] = [
                            'titulo' => $titulo,
                            'html' => $elemento['html_tabla']
                        ];
                    } else {
                        // Es un objeto de producto - convertir a tabla HTML
                        $titulo = $elemento['nombre_del_producto'] ?? 'Producto ' . ($index + 1);
                        $tablaHtml = '<table>';
                        foreach ($elemento as $campo => $valor) {
                            $campoLegible = ucfirst(str_replace('_', ' ', $campo));
                            $valorFormateado = htmlspecialchars($valor);
                            // Convertir saltos de línea a <br>
                            $valorFormateado = nl2br($valorFormateado);
                            $tablaHtml .= "<tr><th>{$campoLegible}</th><td>{$valorFormateado}</td></tr>";
                        }
                        $tablaHtml .= '</table>';
                        $resultado[] = [
                            'titulo' => $titulo,
                            'html' => $tablaHtml
                        ];
                    }
                }
            }
            // Detectar si es un array de tablas directo
            elseif (isset($data[0]) && is_array($data[0])) {
                foreach ($data as $tabla) {
                    $titulo = $tabla['titulo_tabla'] ?? $tabla['titulo'] ?? 'Título no encontrado';
                    $tablaHtml = $tabla['html_tabla'] ?? $tabla['tabla_html'] ?? $tabla['tabla'] ?? $tabla['html'] ?? '<p>No se encontró contenido HTML de tabla.</p>';
                    $resultado[] = [
                        'titulo' => $titulo,
                        'html' => $tablaHtml
                    ];
                }
            }
            // Es una sola tabla
            else {
                $titulo = $data['titulo_tabla'] ?? $data['titulo'] ?? 'Título no encontrado';
                $tablaHtml = $data['html_tabla'] ?? $data['tabla_html'] ?? $data['tabla'] ?? $data['html'] ?? '<p>No se encontró contenido HTML de tabla.</p>';
                $resultado = [[
                    'titulo' => $titulo,
                    'html' => $tablaHtml
                ]];
            }
        } else {
            $error = "Error al leer el JSON: " . json_last_error_msg();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Resultados - EnDES</title>
    <!-- JSON Editor CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/9.10.0/jsoneditor.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/9.10.0/jsoneditor.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            margin: 0;
            padding: 2rem;
            line-height: 1.5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        h1, h2 { color: #2563eb; margin-top: 0; }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border: 1px solid #fecaca;
        }

        /* Estilos para la tabla renderizada */
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        tr:nth-child(even) { background-color: #f9fafb; }

        /* Estilos para JSON Editor */
        .json-editor-container {
            height: 600px;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .jsoneditor {
            border: none;
        }
        .jsoneditor-menu {
            background-color: #2563eb;
            border-bottom: 1px solid #1d4ed8;
        }
        .jsoneditor-contextmenu .jsoneditor-menu button {
            color: #1f2937;
        }
        .editor-info {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .editor-info strong {
            color: #2563eb;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>👁️ Visualizador de Resultados JSON</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">
            Visualiza los datos extraídos en formato de árbol interactivo. Puedes expandir/colapsar nodos, buscar y navegar por la estructura.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($jsonRaw): ?>
            <div class="editor-info">
                <strong>ℹ️ Información:</strong> 
                <span id="json-stats">Cargando...</span>
            </div>
            
            <div id="jsoneditor" class="json-editor-container"></div>
            
            <script>
                // Datos JSON (sin escapar - directamente desde POST)
                const jsonData = <?php echo $jsonRaw; ?>;
                
                // Contenedor del editor
                const container = document.getElementById("jsoneditor");
                
                // Opciones del editor
                const options = {
                    mode: 'tree',  // Vista de árbol por defecto
                    modes: ['tree', 'code', 'text'],  // Modos disponibles
                    onChangeText: function(json) {
                        // Solo lectura - no permitir cambios
                        return false;
                    },
                    onModeChange: function(newMode, oldMode) {
                        console.log('Modo cambiado de', oldMode, 'a', newMode);
                    },
                    onError: function(err) {
                        console.error('Error en JSON Editor:', err.toString());
                    }
                };
                
                // Crear el editor
                const editor = new JSONEditor(container, options);
                
                // Establecer los datos
                editor.set(jsonData);
                
                // Actualizar estadísticas
                const elementCount = jsonData?.Matriz?.length || 0;
                const jsonSize = new Blob([JSON.stringify(jsonData)]).size;
                document.getElementById('json-stats').innerHTML = 
                    '<strong>' + elementCount + '</strong> elementos en Matriz | ' +
                    'Tamaño: <strong>' + (jsonSize / 1024).toFixed(2) + ' KB</strong> | ' +
                    'Modo: <strong>Árbol interactivo</strong> (puedes cambiar a Código)';
            </script>
        <?php endif; ?>
    </div>

    <?php if ($resultado): ?>
        <div class="card">
            <h2>📊 Vista de Tablas HTML</h2>
            <p style="color: #6b7280; margin-bottom: 1rem;">
                Las siguientes tablas HTML fueron extraídas del JSON:
            </p>
            <?php foreach ($resultado as $index => $tabla): ?>
                <div style="margin-bottom: 2rem;">
                    <h3><?php echo htmlspecialchars($tabla['titulo']); ?></h3>
                    <div class="table-container">
                        <?php echo $tabla['html']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
