<?php
/**
 * Formulario F2 - Subida de Imagen Asociada al PDF
 * 
 * Este formulario permite subir una imagen asociada al PDF procesado.
 * La imagen se guarda en la misma carpeta que el PDF con el mismo nombre.
 * 
 * Uso: Incluir en carga_pdf.php dentro de la sección de resultados
 * 
 * @package EnDES
 * @version 1.0
 */
?>

<!-- Sección de subida de imagen -->
<div style="background: #f0f9ff; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem; border: 1px solid #bae6fd;">
    <h4 style="margin: 0 0 0.75rem 0; color: #0369a1; font-size: 0.95rem;">📷 Imagen Asociada</h4>

    <?php if ($imageUploaded): ?>
        <!-- Imagen ya subida -->
        <div style="padding: 0.75rem; background: #dcfce7; border-radius: 0.375rem;">
            <p style="margin: 0 0 0.75rem 0; color: #166534; font-size: 0.875rem;">
                <strong>✓ Imagen subida:</strong> <?php echo htmlspecialchars($imageName); ?>
            </p>
            <!-- Botón HABILITADO (imagen ya subida) -->
            <button type="button" class="btn" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; width: 100%;">
                ✓ Alta producto en Web
            </button>
        </div>
    <?php else: ?>
        <!-- Formulario para subir imagen -->
        <form method="POST" enctype="multipart/form-data" id="imageUploadForm" action="carga_pdf.php">
            <!-- Input file VISIBLE -->
            <input type="file" name="image" id="imageUpload" accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: white; font-size: 0.875rem; margin-bottom: 0.75rem;" required>
            
            <!-- Botón "Subir imagen" - SUBE la imagen -->
            <button type="submit" class="btn" style="background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; border: none; cursor: pointer; font-size: 0.875rem; width: 100%; margin-bottom: 0.75rem;">
                📷 Subir imagen
            </button>
            
            <!-- Botón "Alta producto en Web" - se habilita cuando imagen está subida -->
            <button type="button" id="altaProductoBtn" class="btn" <?php echo $imageUploaded ? '' : 'disabled'; ?> style="background: <?php echo $imageUploaded ? '#2563eb' : '#9ca3af'; ?>; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: <?php echo $imageUploaded ? 'pointer' : 'not-allowed'; ?>; font-size: 0.875rem; width: 100%;">
                🌐 Alta producto en Web
            </button>
        </form>
        
        <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.75rem;">
            La imagen se guardará en la misma carpeta que el PDF con el mismo nombre.
        </p>
    <?php endif; ?>
</div>
