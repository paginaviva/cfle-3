OPENAI_INTEGRATION.md: describe la integración con la interfaz de programación de aplicaciones de OpenAI tal con Responses + GPT-5.1.

---

# Integración con la interfaz de programación de aplicaciones de OpenAI en EnDES

Este documento describe de forma detallada cómo EnDES integra la interfaz de programación de aplicaciones de OpenAI para analizar fichas técnicas en formato PDF y generar un resultado estructurado en formato JSON con la clave raíz `Matriz`.

Su objetivo es que cualquier persona (o sistema) que lea este documento entienda con precisión:

* Qué modelos y endpoints se usan.
* Cómo se suben y utilizan los archivos PDF.
* Cómo se construye la petición a la interfaz de respuestas.
* Cómo se procesa la respuesta para obtener la `Matriz`.
* Qué errores pueden proceder de OpenAI y cómo se gestionan.

No modifica el documento EnDESReadme, sino que lo complementa.

---

## 1. Alcance de la integración

EnDES utiliza la interfaz de programación de aplicaciones de OpenAI para:

1. Recibir un archivo PDF que contiene una ficha técnica de producto.
2. Enviarlo, junto con instrucciones detalladas (prompt), al endpoint de **respuestas** de OpenAI.
3. Obtener una respuesta con estructura JSON normalizada:

   ```json
   {
     "Matriz": [ ... ]
   }
   ```
4. Guardar ese JSON en disco y, opcionalmente, visualizarlo como tablas y tarjetas de producto.

La integración actual no utiliza ya la arquitectura de asistentes. Todo el análisis se resuelve con una única llamada al endpoint de respuestas.

---

## 2. Modelos y endpoints utilizados

### 2.1. Endpoint principal

EnDES utiliza el endpoint de respuestas de OpenAI:

* `https://api.openai.com/v1/responses`

Todas las peticiones que invocan al modelo pasan por este endpoint.

### 2.2. Endpoint de subida de archivos

Para subir los documentos PDF:

* `https://api.openai.com/v1/files`

El sistema sube el PDF al servicio de archivos de OpenAI y recibe un identificador único `file_id`. Este identificador es el que se utilizará después como entrada en la llamada a la interfaz de respuestas.

### 2.3. Modelo por defecto y lista blanca

El modelo por defecto de EnDES es:

* `gpt-5.1`

Existe una lista blanca de modelos válidos para la integración. Actualmente, la política recomendada es:

* Permitir únicamente `gpt-5.1` en la lista blanca.
* Si el usuario envía un valor distinto, el sistema fuerza el uso de `gpt-5.1`.

Esta lista blanca se utiliza antes de construir el cuerpo de la petición a la interfaz de respuestas, de modo que nunca se envía un modelo no deseado.

---

## 3. Uso de archivos y relación con `file_id`

### 3.1. Flujo de subida de archivo

Para cada ejecución de análisis:

1. El usuario sube un archivo PDF a través del formulario web.

2. El servidor guarda temporalmente ese archivo en el sistema de ficheros local.

3. El sistema llama al endpoint `/v1/files` con:

   * El contenido binario del PDF.
   * El tipo de contenido `application/pdf`.
   * Un parámetro `purpose`, configurado como `user_data` (o equivalente), que indica que el archivo se utilizará como entrada de datos para modelos de lenguaje.

4. La respuesta de OpenAI contiene, entre otros campos, un identificador único del archivo:

   ```json
   {
     "id": "file-XXXXXXXXXXXX",
     ...
   }
   ```

5. EnDES guarda este `id` en una variable interna, que llamamos `file_id`.

### 3.2. Qué no hace EnDES con el archivo

Es importante aclarar lo que **no** hace el flujo:

* No indexa el archivo en un almacén vectorial.
* No realiza búsquedas semánticas en el conjunto de archivos.
* No mantiene un almacén de largo plazo basado en embeddings.

El archivo se sube únicamente para que el modelo pueda leer su contenido como parte de la petición de análisis.

### 3.3. Cómo se utiliza `file_id`

El `file_id` se utiliza exclusivamente como entrada de tipo `input_file` dentro de la petición al endpoint de respuestas.

Modelo mental:

1. Se sube un archivo → Se recibe un `file_id`.
2. Ese `file_id` se incluye en el bloque `input_file` de la petición a `/v1/responses`.
3. El modelo, a través de ese bloque, accede al contenido del PDF.

Mientras el archivo permanezca disponible en OpenAI, el mismo `file_id` puede reutilizarse en varias peticiones.

---

## 4. Estructura de la llamada a la interfaz de respuestas

Esta sección detalla la estructura exacta del cuerpo de la petición que EnDES envía al endpoint `/v1/responses`, usando la opción A (lectura directa del PDF mediante `input_file`, sin intérprete de código).

### 4.1. Campos obligatorios

La petición incluye los siguientes campos principales:

* `model`:
  El modelo elegido. Por defecto, `gpt-5.1`.
* `instructions`:
  Texto completo del prompt de extracción, definido en `prompts.php`. Incluye:

  * Descripción del rol del modelo.
  * Proceso 1 (extracción de productos).
  * Proceso 2 (extracción de tablas técnicas).
  * Contrato de salida en forma de objeto con clave raíz `Matriz`.
* `input`:
  Lista de mensajes de entrada. En la integración actual se envía un único mensaje con:

  * `role`: `"user"`.
  * `content`: un array de bloques tipados:

    * Un bloque `input_text` con la instrucción de usuario.
    * Un bloque `input_file` con el identificador `file_id` del PDF.
* `stream`:
  Indicado como `false` para recibir la respuesta como un único bloque.

### 4.2. Ejemplo de cuerpo de petición (JSON conceptual)

A modo de ejemplo conceptual, la estructura de la petición es:

```json
{
  "model": "gpt-5.1",
  "instructions": "<texto completo del prompt de extracción>",
  "input": [
    {
      "role": "user",
      "content": [
        {
          "type": "input_text",
          "text": "Analiza el archivo PDF adjunto siguiendo todas las instrucciones anteriores y devuelve un único objeto JSON con la clave Matriz."
        },
        {
          "type": "input_file",
          "file_id": "file-XXXXXXXXXXXX"
        }
      ]
    }
  ],
  "stream": false
}
```

Notas:

* El texto de `instructions` procede de la configuración de prompts y puede incluir secciones, listas y ejemplos.
* El texto de `input_text` se mantiene más corto y describe la acción concreta sobre el archivo adjunto.
* El bloque `input_file` conecta el identificador `file_id` con el modelo para que este pueda leer el contenido del PDF.

### 4.3. Ejemplo orientativo de construcción del cuerpo en el servidor

En pseudocódigo:

```php
$model       = 'gpt-5.1';
$promptText  = /* texto de prompts.php */;
$userContent = "Analiza el archivo PDF adjunto siguiendo todas las instrucciones anteriores y devuelve un único objeto JSON con la clave Matriz.";
$fileId      = /* id devuelto por /v1/files */;

$payload = [
    'model'        => $model,
    'instructions' => $promptText,
    'input'        => [
        [
            'role'    => 'user',
            'content' => [
                [
                    'type' => 'input_text',
                    'text' => $userContent,
                ],
                [
                    'type'    => 'input_file',
                    'file_id' => $fileId,
                ],
            ],
        ],
    ],
    'stream' => false,
];
```

Este `$payload` se envía al endpoint de respuestas con cabecera de autorización y tipo de contenido `application/json`.

---

## 5. Formato de la respuesta y extracción de la `Matriz`

### 5.1. Estructura general de la respuesta

La respuesta del endpoint de respuestas contiene, además de campos de metadatos (identificador de respuesta, estado, marca de tiempo, etcétera), uno o varios elementos de salida en una estructura similar a:

```json
{
  "id": "resp_...",
  "status": "completed",
  "output": [
    {
      "role": "assistant",
      "content": [
        {
          "type": "output_text",
          "text": "{ \"Matriz\": [ ... ] }"
        }
      ]
    }
  ]
}
```

EnDES se centra en:

* La clave `output`.
* El último elemento de `output`.
* El primer bloque de `content` dentro de ese elemento.
* La propiedad `text`, que debe contener un JSON válido con la estructura acordada.

### 5.2. Estrategia de extracción del JSON

El servidor sigue la siguiente estrategia:

1. Obtiene la última salida del array `output`.
2. Extrae el primer bloque de `content`.
3. Lee el campo `text`.
4. Intenta interpretar el contenido de `text` como JSON válido:

   * Si el texto está rodeado por un bloque de formato (por ejemplo, delimitado por marcas de código con etiqueta de formato), el servidor intenta extraer únicamente la parte correspondiente al objeto JSON.
   * Si el texto es ya un objeto JSON puro, se procesa directamente.
5. Aplica `json_decode` (o equivalente) para producir una estructura interna.

En caso de éxito, se obtiene un objeto similar a:

```json
{
  "Matriz": [
    {
      "nombre_del_producto": "...",
      "codigo_referencia": "...",
      "clasificacion_sistema": "...",
      "categoria_producto": "...",
      "descripcion_del_producto": "...",
      "idiomas_detectados": "...",
      "lista_caracteristicas": "..."
    },
    {
      "titulo_tabla": "...",
      "html_tabla": "<table>...</table>"
    }
  ]
}
```

### 5.3. Validaciones sobre `Matriz`

Tras decodificar el JSON, el servidor realiza varias comprobaciones:

* Comprueba que existe la clave `Matriz`.
* Comprueba que `Matriz` es un array.
* Recuenta el número de elementos.
* Si la matriz está vacía (`[]`), lo registra en el archivo de log y lo indica en la interfaz de usuario.

El resultado se guarda en un fichero `.result.json` asociado al PDF original y se utiliza para la visualización posterior.

---

## 6. Gestión de errores específicos de la plataforma de OpenAI

EnDES distingue claramente entre errores internos (por ejemplo, permisos de ficheros locales) y errores que provienen de la plataforma de OpenAI. Esta sección describe los segundos y cómo se gestionan.

### 6.1. Errores de red y de transporte

Durante las llamadas a `/v1/files` y `/v1/responses` pueden producirse errores de transporte, por ejemplo:

* Problemas de conectividad de red.
* Tiempo de espera agotado.
* Errores internos en la biblioteca de transporte.

En estos casos:

1. La llamada devuelve un fallo a nivel de cURL (o biblioteca equivalente).
2. El cliente de OpenAI lanza una excepción con un mensaje que indica:

   * Que se ha producido un error de transporte.
   * El mensaje de error asociado.
3. EnDES captura ese error, lo registra en el archivo de log con el detalle disponible y muestra un mensaje de error genérico al usuario del tipo:

   * “Error al comunicarse con el servicio de inteligencia artificial. Inténtelo de nuevo más tarde.”

No se intenta continuar el flujo si la llamada falla a este nivel.

### 6.2. Errores HTTP devueltos por OpenAI

Si la llamada llega a OpenAI pero la respuesta tiene un código de estado HTTP fuera del rango 2xx, el cliente trata este caso como error de interfaz de programación de aplicaciones.

Casos típicos:

* **400 Solicitud incorrecta**
  Ejemplos:

  * Formato del cuerpo no válido.
  * Combinación incorrecta de campos (`input`, `instructions`, tipos de bloques).
  * Uso de un modelo no apto para el endpoint.

* **401 No autorizado**
  Ejemplo:

  * Clave de la interfaz de programación de aplicaciones ausente o incorrecta.

* **403 Prohibido**
  Ejemplo:

  * Clave válida, pero sin permisos para el recurso solicitado.

* **404 No encontrado**
  Ejemplo:

  * `file_id` inexistente o ya no disponible.
  * Endpoint incorrecto (por ejemplo, error al escribir la dirección de la ruta).

* **429 Demasiadas solicitudes**
  Ejemplo:

  * Se superan los límites de uso (por frecuencia o volumen).

* **500 y superiores**
  Ejemplos:

  * Errores internos en los servicios de OpenAI.

En estos casos, el cliente:

1. Lee el cuerpo de la respuesta, que suele incluir un objeto `error` con:

   * `message`: descripción del problema.
   * `type`: tipo de error.
   * Posiblemente `param` y `code`.
2. Construye un mensaje de error a partir de:

   * El código HTTP.
   * El texto de error devuelto.
3. Lanza una excepción con ese mensaje.
4. El código de EnDES captura la excepción, la registra en el archivo de log y muestra un mensaje de error al usuario.

La lógica actual prioriza la claridad en el registro y la seguridad en la interfaz de usuario. El mensaje que ve el usuario suele ser más genérico; el log contiene la información detallada.

### 6.3. Errores de contenido o formato en la respuesta

Incluso con código HTTP 200, pueden darse situaciones en las que la estructura de la respuesta no sea la esperada:

1. La clave `output` no exista o esté vacía.
2. El último elemento de `output` no tenga campo `content`.
3. El primer bloque de `content` no tenga campo `text`.
4. El `text` contenga un texto que no sea un JSON válido o que no incluya la clave `Matriz`.

En estos casos:

1. El servidor detecta la ausencia de campos esperados (`output`, `content`, `text`).
2. Lanza una excepción con un mensaje que incluye, en lo posible, la respuesta completa o su parte relevante.
3. Registra el error en el archivo de log, con información suficiente para depurar.
4. Muestra al usuario un mensaje del tipo:

   * “Se ha producido un error al interpretar la respuesta de la inteligencia artificial.”

Si el problema es que el JSON no es válido, la fase de `json_decode` fallará y se tratará igualmente como error, con registro y mensaje al usuario.

### 6.4. Casos concretos de errores relacionados con OpenAI

A continuación se enumeran algunos casos típicos y cómo se tratan.

#### 6.4.1. Modelo no disponible o no válido

Situación:

* El código intenta usar un modelo que no está habilitado o no es apto para el endpoint.
* La plataforma devuelve un error HTTP (normalmente 400 o 404) con un mensaje que indica que el modelo no es válido para esa interfaz.

Gestión:

1. La llamada devuelve un error HTTP.
2. El cliente lo traduce en una excepción con el mensaje de error de la plataforma.
3. EnDES registra el error y muestra al usuario un mensaje de fallo de análisis.
4. Para prevenir este caso, se utiliza una lista blanca de modelos y se fuerza a `gpt-5.1` si se recibe un valor no permitido.

#### 6.4.2. Error en el formato de la petición

Situación:

* El cuerpo enviado a `/v1/responses` no cumple el formato requerido.
* Falta algún campo obligatorio o hay combinaciones no válidas (por ejemplo, tipos de bloque en `content` incorrectos).

Gestión:

1. La plataforma devuelve un error HTTP 400 con explicación.
2. El cliente lo convierte en excepción.
3. EnDES lo registra en el archivo de log con el mensaje devuelto.
4. El usuario ve un mensaje genérico de error de análisis.

La corrección de este tipo de errores se hace ajustando el formato de `payload` en el código, no en tiempo de ejecución por parte del usuario.

#### 6.4.3. Tamaño de archivo excedido

Situación:

* El PDF supera el tamaño máximo admitido por el servicio de archivos de OpenAI.
* La llamada a `/v1/files` devuelve un error.

Gestión:

1. El cliente detecta código HTTP de error en la subida.
2. EnDES lanza una excepción y registra el mensaje devuelto por la plataforma.
3. El usuario recibe un mensaje indicando que el archivo no se ha podido subir y que revise el tamaño.

Es recomendable documentar en la fase de despliegue los tamaños máximos aceptables según el plan de uso contratado.

#### 6.4.4. Matriz vacía (`{"Matriz": []}`) sin error explícito

Situación:

* La llamada a `/v1/responses` se completa sin errores (HTTP 200).
* El modelo devuelve un objeto JSON válido, pero con `Matriz` vacía.

Gestión:

1. EnDES interpreta el JSON sin errores.
2. La validación confirma que `Matriz` existe, pero con longitud cero.
3. El sistema:

   * Registra en el log que se ha obtenido una matriz vacía.
   * Muestra al usuario que el análisis se ha completado, pero indica que no se han detectado productos ni tablas.

Este caso no se considera error de la plataforma, sino resultado de la inferencia. Es útil para detectar casos de fichas mal formateadas, PDFs que no son realmente fichas técnicas o prompts que requieren ajustes.

---

## 7. Consideraciones para extensión o migración

Este diseño de integración facilita:

1. **Migración a otros lenguajes de programación**
   Basta con replicar:

   * La llamada a `/v1/files` para subir el PDF.
   * La construcción del cuerpo de la petición a `/v1/responses` con `instructions`, `input_text` e `input_file`.
   * La extracción del texto de salida y la decodificación del JSON.

2. **Cambio o ampliación de modelos**
   La lógica de lista blanca y modelo por defecto permite:

   * Añadir modelos adicionales tras verificarlos.
   * Mantener `gpt-5.1` como opción segura por defecto.

3. **Incorporación futura de herramientas adicionales**
   Si se decide más adelante usar:

   * Intérprete de código.
   * Búsqueda en archivos.
     se podrán añadir entradas en el campo `tools` del cuerpo de la petición, manteniendo el esquema actual de uso de `file_id`.

---

## 8. Resumen

* EnDES integra la interfaz de programación de aplicaciones de OpenAI mediante:

  * Subida de PDFs al servicio de archivos.
  * Llamadas al endpoint de respuestas con el modelo `gpt-5.1`.
  * Uso del identificador de archivo `file_id` como entrada de tipo `input_file`.
* El contrato de salida está claramente definido: un objeto JSON con clave raíz `Matriz`.
* La gestión de errores distingue entre:

  * Problemas de red.
  * Errores HTTP devueltos por OpenAI.
  * Inconsistencias en la estructura de la respuesta.
  * Casos de inferencia sin datos (matriz vacía).
* El documento sirve como referencia para mantener, depurar o portar la integración a otros entornos sin necesidad de leer todo el código fuente.
