<?php
// visualizador.php
// Herramienta temporal para visualizar tablas HTML desde un JSON.

$resultado = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = $_POST['json_data'] ?? '';
    
    // Limpiamos las barras invertidas que a veces añade PHP magic quotes (aunque obsoleto, por si acaso)
    if (get_magic_quotes_gpc()) {
        $jsonInput = stripslashes($jsonInput);
    }

    if (!empty($jsonInput)) {
        $data = json_decode($jsonInput, true);
        
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
    <title>Visualizador de Tablas JSON</title>
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
            max-width: 900px;
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
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        textarea {
            width: 100%;
            height: 200px;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-family: monospace;
            font-size: 0.9rem;
            box-sizing: border-box;
            margin-bottom: 1rem;
        }
        textarea:focus { outline: 2px solid #2563eb; border-color: transparent; }
        button {
            background-color: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover { background-color: #1d4ed8; }
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
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Visualizador de Tablas JSON</h1>
        <p>Pega tu JSON abajo. Formatos soportados:</p>
        <ul>
            <li><code>{"Matriz": [...]}</code> - Convierte todo a tablas</li>
            <li><code>[{"titulo_tabla": "...", "html_tabla": "..."}]</code> - Array de tablas</li>
            <li><code>{"titulo_tabla": "...", "html_tabla": "..."}</code> - Una tabla</li>
        </ul>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="json_data">Pegar JSON aquí:</label>
                <textarea name="json_data" id="json_data" placeholder='{
    "Matriz": [
        {
            "nombre_del_producto": "Producto X",
            "codigo_referencia": "REF-001",
            "descripcion_del_producto": "Descripción..."
        },
        {
            "titulo_tabla": "Especificaciones",
            "html_tabla": "<table>...</table>"
        }
    ]
}' required></textarea>
            </div>
            <button type="submit">Visualizar Tabla(s)</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($resultado): ?>
        <?php foreach ($resultado as $index => $tabla): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($tabla['titulo']); ?></h2>
                <div class="table-container">
                    <!-- Se imprime el HTML tal cual (raw) para que se renderice la tabla -->
                    <?php echo $tabla['html']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
