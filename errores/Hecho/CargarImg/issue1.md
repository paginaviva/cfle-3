**Issue**
Agregar flujo de subida de imagen asociada al PDF procesado y mostrar logo corporativo en `carga_pdf.php`

**Descripción**
En la pantalla de resultado posterior subida y ejecución de OpenAI API (ver img `temp/01.png`), se requiere incorporar un nuevo flujo de carga de imagen asociado al proceso actual de PDF, además de mostrar el logo de la empresa en la interfaz.

La numeración de la imagen `temp/01.png` está vinculada explícitamente con los cambios solicitados y debe conservarse en este issue.

**Referencia visual**

* Imagen principal de referencia: `temp/01.png`
* Imagen de comportamiento esperado complementaria: `temp/02.png`
* La numeración visual de `temp/01.png` está vinculada explícitamente con los puntos descritos en este issue.

**Problemas detectados**

1. **[Referencia visual 1]** Se debe agregar un nuevo botón con el texto: `Subir imagen`.
   Requisitos indicados:

   * debe abrir la ventana del explorador del sistema operativo para seleccionar un archivo de imagen
   * debe permitir subida desde entornos Win/Mac
   * el archivo de imagen debe guardarse en la misma carpeta donde se subió el PDF
   * el nombre del archivo de imagen debe ser el mismo que el del PDF
   * una vez subida y confirmada la imagen, debe deshabilitarse el botón indicado en **[Referencia visual 2]**

2. **[Referencia visual 2]** Se debe agregar un nuevo botón con el texto: `Subir imagen`.
   Requisitos indicados:

   * este botón debe mostrarse inicialmente deshabilitado
   * debe ser habilitado por la acción completada en **[Referencia visual 1]**
   * una vez habilitado, debe mostrar un ventana emergente similar al reflejado en `temp/02.png`

3. **[Referencia visual 3]** Debe mostrarse el logo de la empresa en la interfaz.
   Recurso indicado:

   * URL del logo: `https://srrhhmx.s-ul.eu/P6Za8iMR`

**Comportamiento esperado**

* **[Referencia visual 1]** Debe existir un botón `Subir imagen` operativo que abra el selector de archivos del sistema para cargar una imagen.
* **[Referencia visual 1]** Tras la subida y confirmación de la imagen, el archivo debe persistirse en la misma carpeta del PDF y con el mismo nombre base que dicho PDF.
* **[Referencia visual 2]** El segundo botón `Subir imagen` debe permanecer deshabilitado hasta que finalice correctamente la subida de imagen desde **[Referencia visual 1]**.
* **[Referencia visual 2]** Una vez habilitado, debe adoptar una visualización/estado equivalente al mostrado en `temp/02.png`.
* **[Referencia visual 3]** La pantalla debe mostrar el logo corporativo utilizando la imagen proporcionada.

**Notas técnicas**

* Pantalla afectada: `https://wa.cofemlevante.com/src/php/carga_pdf.php`
* Texto requerido para ambos botones nuevos: `Subir imagen`
* Compatibilidad solicitada para apertura del selector de archivos: Win/Mac
* Regla de almacenamiento:

  * misma carpeta que el PDF
  * mismo nombre que el PDF para el archivo de imagen
* Dependencia funcional explícita:

  * la finalización correcta de **[Referencia visual 1]** habilita el botón de **[Referencia visual 2]**
* Referencia visual de estado esperado para el segundo botón: `temp/02.png`
* Recurso gráfico corporativo:

  * `https://srrhhmx.s-ul.eu/P6Za8iMR`

**Resumen**
Se solicita ampliar la pantalla de carga/procesado con dos botones nuevos para gestionar la subida de una imagen asociada al PDF, estableciendo una dependencia de habilitación entre ambos, y añadir el logo corporativo en la cabecera/zona indicada. La relación con la numeración visual debe mantenerse explícitamente en los puntos **[Referencia visual 1]**, **[Referencia visual 2]** y **[Referencia visual 3]**.
