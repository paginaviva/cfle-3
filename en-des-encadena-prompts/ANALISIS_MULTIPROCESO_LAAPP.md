# Análisis: Transición de LaApp a Modelo Multiproceso

**Fecha:** 2026-04-01  
**Documento de:** Comprensión y aclaraciones  
**Estado:** Pendiente de validación

---

## Índice de Contenidos

1. [Lo que he entendido](#1-lo-que-he-entendido)
2. [Interpretación de la transición](#2-interpretación-de-la-transición)
3. [Dudas y puntos de aclaración](#3-dudas-y-puntos-de-aclaración)
4. [Propuesta de estructura del documento](#4-propuesta-de-estructura-del-documento)

---

## 1. Lo que he entendido

### Contexto actual (Estado monoproceso)

Actualmente, **EnDES (LaApp)** está diseñado como un sistema de **proceso único**:
- Un usuario sube **un PDF**
- El sistema procesa **ese PDF específico**
- Se extraen datos de **ese documento concreto**
- El resultado se asocia exclusivamente a **esa sesión de procesamiento**

El flujo está optimizado para trabajar con **un solo contexto de procesamiento a la vez** por usuario/sesión.

### Lo que entiendo por el cambio solicitado

Debes transformar la concepción arquitectónica de LaApp para que:

#### A) LaApp se convierta en una plataforma multiproceso

En lugar de ser una aplicación que procesa documentos de forma aislada, LaApp debe convertirse en una **plataforma que gestiona múltiples procesos simultáneos o secuenciales**, donde cada proceso es una instancia independiente de trabajo.

#### B) Se defina el concepto PCS (Proceso)

Cada **PCS** debe ser:
- Una **unidad de trabajo identificable y única**
- Un **contexto operativo independiente** (su propio PDF, sus propios resultados, su propia configuración)
- Capaz de coexistir con otros PCS dentro del mismo ecosistema LaApp

#### C) Se mantenga el ecosistema compartido

Aunque cada PCS es único, todos deben:
- Compartir las **mismas funciones base** de LaApp (autenticación, subida de archivos, procesamiento IA, visualización)
- Utilizar la **misma infraestructura** (base de datos, configuración, prompts, clientes de API)
- Beneficiarse del **mismo núcleo funcional** (OpenAIClient, AuthService, etc.)

---

## 2. Interpretación de la transición

### Modelo actual (Monoproceso)

```
┌─────────────────────────────────────────┐
│           LaApp (EnDES)                 │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │  SESIÓN DE USUARIO              │   │
│  │                                 │   │
│  │  1. Sube PDF_A                  │   │
│  │  2. Procesa PDF_A               │   │
│  │  3. Obtiene Resultado_A         │   │
│  │  4. (Opcional) Sube Imagen_A    │   │
│  │                                 │   │
│  │  └─> Si quiere procesar PDF_B:  │   │
│  │      Debe descartar PDF_A       │   │
│  │      o empezar nueva sesión     │   │
│  └─────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Limitación:** El sistema está pensado para "un usuario = un proceso activo a la vez".

---

### Modelo propuesto (Multiproceso)

```
┌─────────────────────────────────────────────────────────┐
│                    LaApp (Plataforma)                   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  ECOSISTEMA COMPARTIDO                          │   │
│  │  - AuthService (autenticación)                  │   │
│  │  - OpenAIClient (procesamiento IA)              │   │
│  │  - Config/Prompts (configuración global)        │   │
│  │  - Storage (docs/, logs/)                       │   │
│  │  - Visualizadores                               │   │
│  └─────────────────────────────────────────────────┘   │
│                         ↓                               │
│  ┌─────────────────────────────────────────────────┐   │
│  │  GESTOR DE PROCESOS (PCS Manager)               │   │
│  └─────────────────────────────────────────────────┘   │
│                         ↓                               │
│    ┌──────────────┬──────────────┬──────────────┐      │
│    ↓              ↓              ↓              ↓      │
│ ┌──────┐      ┌──────┐      ┌──────┐      ┌──────┐   │
│ │PCS-1 │      │PCS-2 │      │PCS-3 │      │PCS-N │   │
│ │      │      │      │      │      │      │      │   │
│ │PDF_A │      │PDF_B │      │PDF_C │      │PDF_N │   │
│ │Res_A │      │Res_B │      │Res_C │      │Res_N │   │
│ │Img_A │      │Img_B │      │-     │      │-     │   │
│ └──────┘      └──────┘      └──────┘      └──────┘   │
│    ↑              ↑              ↑              ↑      │
│    └──────────────┴──────────────┴──────────────┘      │
│                    ↓                                    │
│         Todos comparten el ecosistema LaApp            │
└─────────────────────────────────────────────────────────┘
```

**Ventaja:** Un usuario puede tener **múltiples procesos activos**, cada uno con su propio estado, avanzando de forma independiente.

---

### Dimensiones de la multiprocesalidad

Entiendo que hay varias formas de interpretar "multiproceso":

#### Dimensión 1: Múltiples procesos por usuario
- Un mismo usuario puede tener varios PCS abiertos simultáneamente
- Ejemplo: Usuario "admin" tiene 5 PDFs en diferentes estados de procesamiento

#### Dimensión 2: Múltiples procesos en el sistema
- El sistema puede gestionar procesos de múltiples usuarios concurrentemente
- Ejemplo: 10 usuarios, cada uno con sus propios PCS

#### Dimensión 3: Múltiples tipos de proceso
- Diferentes PCS pueden usar diferentes prompts/configuraciones
- Ejemplo: PCS para "Extracción Cofem", PCS para "Análisis Técnico", PCS para "Validación"

#### Dimensión 4: Estados independientes por proceso
- Cada PCS tiene su propio ciclo de vida
- Ejemplo: PCS-1 en "esperando imagen", PCS-2 en "procesando", PCS-3 en "completado"

---

## 3. Dudas y puntos de aclaración

### Duda 1: Alcance de la multiprocesalidad

**Pregunta:** ¿La multiprocesalidad se refiere a:

- **Opción A:** Múltiples procesos por usuario (un usuario gestiona varios PDFs simultáneamente)?
- **Opción B:** Múltiples usuarios procesando en paralelo (multi-tenant)?
- **Opción C:** Múltiples tipos de procesamiento (diferentes prompts por PCS)?
- **Opción D:** Una combinación de las anteriores?

**Por qué es importante:** Define si el cambio es principalmente de UX (gestión de múltiples procesos en UI), de arquitectura (concurrencia real), o de modelo de negocio (multi-usuario).

---

### Duda 2: Persistencia y gestión de PCS

**Pregunta:** ¿Los PCS deben:

- **Opción A:** Ser efímeros (solo existen durante la sesión del usuario)?
- **Opción B:** Persistir en base de datos (recuperables incluso después de cerrar sesión)?
- **Opción C:** Tener un estado intermedio (persistencia temporal en sesión/ARCHIVOS)?

**Por qué es importante:** Define la complejidad de implementación. La opción B requeriría base de datos, tablas de procesos, historial, etc.

---

### Duda 3: Identificación única de PCS

**Pregunta:** Cuando dices "C/PCS debe ser único", ¿te refieres a que debe tener:

- **Opción A:** Un ID único interno (ej: `pcs_12345`)?
- **Opción B:** Un nombre/título asignado por el usuario (ej: "Fichas Producto X")?
- **Opción C:** Ambos (ID técnico + nombre amigable)?
- **Opción D:** Algún otro tipo de identificador (código, referencia externa)?

**Por qué es importante:** Define cómo se estructura la UI para listar/seleccionar procesos y cómo se referencian internamente.

---

### Duda 4: Relación entre PCS y usuario

**Pregunta:** ¿La relación es:

- **Opción A:** 1 usuario → N PCS (un usuario puede crear múltiples procesos)?
- **Opción B:** 1 PCS → 1 usuario (cada proceso pertenece a un usuario específico)?
- **Opción C:** PCS pueden ser compartidos entre usuarios (colaborativo)?
- **Opción D:** PCS pueden existir sin usuario (procesos automáticos del sistema)?

**Por qué es importante:** Define el modelo de permisos y propiedad de los procesos.

---

### Duda 5: Concurrencia real vs. gestión de estado

**Pregunta:** Cuando hablas de "multiproceso", ¿te refieres a:

- **Opción A:** Concurrencia real (múltiples procesos ejecutándose en paralelo a nivel de servidor)?
- **Opción B:** Gestión de estado múltiple (el usuario puede tener varios procesos "abiertos" pero se procesan de uno en uno)?
- **Opción C:** Ambas cosas?

**Por qué es importante:** La opción A requiere considerar límites de tasa de OpenAI, colas de procesamiento, workers. La opción B es principalmente un cambio de UX/estado.

---

### Duda 6: Funciones compartidas específicas

**Pregunta:** Cuando dices "comparten las funciones y el ecosistema de LaApp", ¿te refieres específicamente a:

- **A)** Mismo conjunto de prompts disponibles para todos los PCS?
- **B)** Misma configuración de OpenAI (API Key, modelo)?
- **C)** Mismo sistema de almacenamiento (carpetas docs/)?
- **D)** Mismo sistema de autenticación?
- **E)** Todos los anteriores?
- **F)** Algunos específicos (¿cuáles)?

**Por qué es importante:** Define qué es "global" vs. qué es "por PCS".

---

### Duda 7: UI/UX para multiproceso

**Pregunta:** ¿Cómo debe verse la interfaz para gestionar múltiples PCS?

- **Opción A:** Dashboard/listado de procesos con estados (como una tabla de "Mis Procesos")?
- **Opción B:** Pestañas/tabs para cambiar entre procesos activos?
- **Opción C:** Navegación tipo "proyecto actual" con selector dropdown?
- **Opción D:** Otro enfoque (¿cuál)?

**Por qué es importante:** Define el esfuerzo de frontend y la experiencia de usuario final.

---

### Duda 8: Estados del ciclo de vida de un PCS

**Pregunta:** ¿Qué estados debe poder tener un PCS?

Propuesta inicial:
1. `CREADO` - PCS registrado pero sin PDF subido
2. `PDF_SUBIDO` - PDF cargado, esperando confirmación
3. `EN_PROCESO` - Enviado a OpenAI, esperando respuesta
4. `COMPLETADO` - Procesamiento finalizado, resultados disponibles
5. `IMAGEN_SUBIDA` - Imagen asociada cargada (opcional)
6. `ARCHIVADO` - PCS cerrado pero persistido
7. `ERROR` - Procesamiento fallido

**¿Es correcta esta lista? ¿Falta o sobra algún estado?**

---

### Duda 9: Alcance del documento a generar

**Pregunta:** El documento que debo producir, ¿debe incluir:

- **A)** Solo la redefinición conceptual del flujo (documento de diseño)?
- **B)** También la especificación técnica de implementación?
- **C)** También los cambios específicos en archivos existentes?
- **D)** También diagramas de arquitectura actualizados?
- **E)** Todo lo anterior?

**Por qué es importante:** Define el alcance y extensión del trabajo a realizar.

---

### Duda 10: Relación con el código actual

**Pregunta:** ¿El modelo multiproceso debe:

- **Opción A:** Ser compatible con el código actual (backward compatible)?
- **Opción B:** Reemplazar completamente el modelo actual (breaking change)?
- **Opción C:** Implementarse de forma gradual (fase 1: concepto, fase 2: implementación)?

**Por qué es importante:** Define si hay que mantener la lógica actual mientras se añade la nueva, o si se puede refactorizar completamente.

---

## 4. Propuesta de estructura del documento final

Una vez aclaradas las dudas, propongo que el documento resultante tenga esta estructura:

```markdown
# Redefinición del Flujo de Procesamiento: Modelo Multiproceso

## 1. Introducción
   1.1. Contexto actual (monoproceso)
   1.2. Objetivo de la transformación
   1.3. Beneficios esperados

## 2. Concepto de Proceso (PCS)
   2.1. Definición formal de PCS
   2.2. Características de un PCS
   2.3. Ciclo de vida de un PCS (estados)
   2.4. Identificación única de PCS

## 3. Arquitectura Multiproceso
   3.1. Ecosistema compartido (qué es global)
   3.2. Componentes por PCS (qué es específico)
   3.3. Relación Usuario ↔ PCS
   3.4. Diagrama de arquitectura

## 4. Flujo de Procesamiento Redefinido
   4.1. Creación de nuevo PCS
   4.2. Gestión de múltiples PCS
   4.3. Navegación entre PCS
   4.4. Flujo detallado paso a paso

## 5. Especificación Técnica
   5.1. Estructura de datos de PCS
   5.2. API/Endpoints necesarios
   5.3. Cambios en base de datos/archivos
   5.4. Gestión de estado y sesión

## 6. Interfaz de Usuario
   6.1. Dashboard de procesos
   6.2. Vista de detalle de PCS
   6.3. Acciones disponibles por estado
   6.4. Wireframes/mockups (si aplica)

## 7. Consideraciones de Implementación
   7.1. Archivos a modificar
   7.2. Nuevos archivos necesarios
   7.3. Migración de datos (si aplica)
   7.4. Testing y validación

## 8. Apéndices
   8.1. Glosario de términos
   8.2. Referencias a documentación existente
   8.3. Ejemplos de uso
```

---

## Resumen de lo entendido

| Concepto | Mi interpretación |
|----------|-------------------|
| **LaApp multiproceso** | Plataforma que gestiona múltiples instancias de procesamiento independientes |
| **PCS (Proceso)** | Unidad de trabajo única con identidad propia, contexto y estado |
| **Unicidad de PCS** | Cada PCS tiene ID propio y es independiente de otros PCS |
| **Ecosistema compartido** | Todos los PCS usan las mismas funciones base de LaApp (auth, IA, storage, config) |
| **Cambio principal** | De "un usuario = un proceso" a "un usuario = N procesos gestionables" |

---

## Estado del documento

- [x] Análisis de comprensión completado
- [ ] Dudas enviadas para aclaración
- [ ] Esperando validación del usuario
- [ ] Proceder con implementación tras confirmación

---

**Próximo paso:** Esperar aclaraciones del usuario sobre las 10 dudas planteadas antes de proceder con la redacción final del documento.

**Nota:** Este documento es preliminar y está sujeto a modificación según las aclaraciones recibidas.
