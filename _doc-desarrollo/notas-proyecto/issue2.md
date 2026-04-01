**Issue**
La acción “Subir imagen” no carga la imagen seleccionada, redirige incorrectamente a la pantalla de carga de PDF y presenta errores de layout en la zona de acciones

**Descripción**
En la pantalla donde se gestiona la imagen asociada al PDF procesado, la funcionalidad de subida de imagen no está operando como se requiere. Al hacer clic en el botón **“Subir imagen”**, en lugar de enviarse la imagen previamente seleccionada desde la caja **“Imagen Asociada”**, la interfaz redirige a la pantalla de carga de PDF.

Además, se han detectado dos incidencias adicionales en la misma área: se está mostrando un logo donde no debe mostrarse ningún elemento, y la ubicación del botón **“Alta producto en Web”** no coincide con la posición esperada definida en la referencia visual.

**Referencia visual**

* Imagen de referencia: `temp/03.png`
* La numeración visual está vinculada explícitamente con los puntos descritos en este issue.

**Problemas detectados**

1. **[Referencia visual 1]** Debe eliminarse el logo mostrado en la parte superior del bloque.
   Según lo indicado, en esa ubicación no debe mostrarse ningún elemento.

2. **[Referencia visual 2]** Al hacer clic en el botón **“Subir imagen”**, no se está ejecutando la subida de la imagen seleccionada en la caja **“Imagen Asociada”**.
   Comportamiento actual reportado:

   * no sube la imagen
   * redirige o vuelve a la pantalla de carga de PDF

   Requisito funcional:

   * debe subirse la imagen previamente seleccionada en **“Imagen Asociada”**
   * debe mantenerse la regla ya definida: la imagen se guardará en la misma carpeta que el PDF con el mismo nombre

3. **[Referencia visual 3]** La ubicación actual del botón **“Alta producto en Web”** es incorrecta.

4. **[Referencia visual 4]** La ubicación correcta del botón **“Alta producto en Web”** debe ser debajo del botón **“Subir imagen”**, en la posición señalada en la referencia visual.

**Comportamiento esperado**

* **[Referencia visual 1]** No debe mostrarse ningún logo ni imagen en la posición marcada.
* **[Referencia visual 2]** Al pulsar **“Subir imagen”**, debe enviarse la imagen seleccionada en la caja **“Imagen Asociada”** y no debe producirse navegación a la pantalla de carga de PDF.
* **[Referencia visual 2]** La imagen subida debe conservar la regla de almacenamiento ya definida: misma carpeta que el PDF y mismo nombre base.
* **[Referencia visual 3]** El botón **“Alta producto en Web”** no debe permanecer en su ubicación actual.
* **[Referencia visual 4]** El botón **“Alta producto en Web”** debe renderizarse debajo de **“Subir imagen”**.

**Notas técnicas**

* El fallo principal afecta a la acción asociada al botón **“Subir imagen”**, que actualmente dispara un comportamiento incorrecto de navegación en lugar de ejecutar la subida del archivo seleccionado.
* La fuente del archivo a subir debe ser la selección realizada en el bloque **“Imagen Asociada”**.
* Debe respetarse la regla funcional ya definida previamente:

  * guardar la imagen en la misma carpeta que el PDF
  * usar el mismo nombre que el PDF
* El issue también incluye una corrección de layout/posición sobre el botón **“Alta producto en Web”** y la eliminación del logo visible en la posición marcada como **[Referencia visual 1]**.
* La numeración visual debe mantenerse explícitamente durante la implementación para evitar ambigüedad entre la acción de subida y la corrección de layout.

**Resumen**
La pantalla presenta un fallo funcional y dos errores de maquetación en la zona de gestión de imagen asociada. Debe corregirse la acción del botón **“Subir imagen”** para que suba el archivo seleccionado sin redirigir a la carga de PDF, eliminar el logo indicado y recolocar el botón **“Alta producto en Web”** en la posición marcada como correcta.
