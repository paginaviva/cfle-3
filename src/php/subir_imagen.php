<?php
/**
 * Subir Imagen - Formulario F2
 * 
 * Página para subir imagen asociada al PDF procesado.
 * La imagen se guarda en la misma carpeta que el PDF con el mismo nombre.
 */

require_once __DIR__ . '/../Service/AuthService.php';

// Cargar configuración
$config = require __DIR__ . '/../../config/config.php';
$auth = new AuthService($config);

// Proteger ruta
$auth->requireLogin();

// Variables para manejo de imagen
$imageUploaded = false;
$imageName = null;
$error = null;
$successMessage = null;

// Procesar subida de imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $nombreTmp = $_FILES['image']['tmp_name'];
        $nombreOrig = basename($_FILES['image']['name']);
        
        // Obtener nombre base del PDF desde sesión
        $pdfNombreBase = $_SESSION['current_pdf_basename'] ?? '';
        
        if (!empty($pdfNombreBase)) {
            $extension = pathinfo($nombreOrig, PATHINFO_EXTENSION);
            $nombreImagen = $pdfNombreBase . '.' . $extension;
            $targetDir = DOCS_PATH . '/' . $pdfNombreBase . '/';
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }
            
            $rutaDestino = $targetDir . $nombreImagen;
            
            if (move_uploaded_file($nombreTmp, $rutaDestino)) {
                $imageUploaded = true;
                $imageName = $nombreImagen;
                $_SESSION['current_image_path'] = $rutaDestino;
                $_SESSION['current_image_name'] = $imageName;
                $successMessage = "Imagen subida correctamente: " . $imageName;
            } else {
                $error = "Error al guardar la imagen.";
            }
        } else {
            $error = "No se ha identificado el PDF asociado. Por favor, procesa un PDF primero.";
        }
    } else {
        $error = "Error en la subida: " . $_FILES['image']['error'];
    }
}

// Verificar si ya hay imagen subida
if (isset($_SESSION['current_image_path']) && isset($_SESSION['current_image_name'])) {
    $imageUploaded = true;
    $imageName = $_SESSION['current_image_name'];
}

include __DIR__ . '/layout_header.php';
?>

<div style="max-width: 600px; margin: 2rem auto;">
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h2 style="text-align: center; color: #2563eb; margin-bottom: 1.5rem;">📷 Subir Imagen Asociada</h2>
        
        <?php if ($successMessage): ?>
            <div style="background: #dcfce7; border: 1px solid #16a34a; color: #166534; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem;">
                <strong>✅ Éxito:</strong> <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem;">
                <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de subida -->
        <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label for="image" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                    Seleccionar Imagen
                </label>
                <input 
                    type="file" 
                    name="image" 
                    id="image" 
                    accept="image/*" 
                    <?php echo $imageUploaded ? 'disabled' : ''; ?>
                    style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: white; font-size: 0.875rem;"
                >
            </div>
            
            <!-- Botón "Subir Imagen" -->
            <button 
                type="submit" 
                class="btn" 
                <?php echo $imageUploaded ? 'disabled' : ''; ?>
                style="background: <?php echo $imageUploaded ? '#9ca3af' : '#2563eb'; ?>; 
                       color: white; 
                       padding: 0.75rem; 
                       border-radius: 0.375rem; 
                       border: none; 
                       cursor: <?php echo $imageUploaded ? 'not-allowed' : 'pointer'; ?>; 
                       font-size: 1rem; 
                       font-weight: 600;"
            >
                📷 Subir Imagen
            </button>
            
            <!-- Botón "Alta producto en Web" -->
            <button 
                type="button" 
                id="altaProductoBtn"
                class="btn" 
                <?php echo $imageUploaded ? '' : 'disabled'; ?>
                style="background: <?php echo $imageUploaded ? '#10b981' : '#9ca3af'; ?>; 
                       color: white; 
                       padding: 0.75rem; 
                       border-radius: 0.375rem; 
                       border: none; 
                       cursor: <?php echo $imageUploaded ? 'pointer' : 'not-allowed'; ?>; 
                       font-size: 1rem; 
                       font-weight: 600;"
            >
                <?php echo $imageUploaded ? '✓ ' : '🌐 '; ?>Alta producto en Web
            </button>
        </form>
        
        <!-- Modal de Aviso - Solo visible cuando imagen está subida -->
        <div id="avisoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 0.5rem; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
                <!-- Header rojo con aviso -->
                <div style="background: #dc2626; padding: 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚠️</div>
                    <h3 style="color: white; margin: 0; font-size: 1.5rem; font-weight: 700;">¡AVISO!</h3>
                </div>
                
                <!-- Cuerpo del mensaje -->
                <div style="padding: 1.5rem;">
                    <p style="margin: 0 0 1.5rem 0; color: #374151; line-height: 1.6;">
                        <strong style="font-size: 1.1rem; display: block; margin-bottom: 0.75rem;">Sincronización pendiente en WooCommerce.</strong>
                        Debes sincronizar las claves primarias y los metafields antes de continuar para evitar errores en los datos.
                    </p>
                    
                    <!-- Botón Cancelar -->
                    <div style="text-align: center;">
                        <button id="cancelarBtn" type="button" style="background: #dc2626; color: white; padding: 0.75rem 2rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 1rem; font-weight: 600;">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
            <a href="carga_pdf.php" class="btn" style="background: #6b7280; color: white; padding: 0.75rem; border-radius: 0.375rem; text-decoration: none; display: block; text-align: center;">
                ← Volver a Resultados
            </a>
        </div>
    </div>
</div>

<!-- Scripts para el modal -->
<script>
    // Abrir modal al hacer click en "Alta producto en Web"
    const altaProductoBtn = document.getElementById('altaProductoBtn');
    const avisoModal = document.getElementById('avisoModal');
    const cancelarBtn = document.getElementById('cancelarBtn');
    
    if (altaProductoBtn && avisoModal) {
        altaProductoBtn.addEventListener('click', function() {
            // Solo abrir si el botón está habilitado (imagen subida)
            if (!this.disabled) {
                avisoModal.style.display = 'flex';
            }
        });
    }
    
    // Cerrar modal con botón Cancelar
    if (cancelarBtn && avisoModal) {
        cancelarBtn.addEventListener('click', function() {
            avisoModal.style.display = 'none';
        });
    }
    
    // Cerrar modal al hacer click fuera
    if (avisoModal) {
        avisoModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }
</script>

<?php include __DIR__ . '/layout_footer.php'; ?>
