# Manual de Usuario - Chat IA Mesa de Ayuda

## 1) ¿Qué es esta app?
Aplicación de chat que responde preguntas usando IA (Google Gemini) entrenada con tus documentos (PDF, TXT, XLSX, MD). Soporta 2 vistas:
- Vista Usuario: hacer preguntas y ver respuestas.
- Vista Archivos BD: subir/eliminar archivos y consultar preguntas frecuentes.

## 2) ¿Cómo funciona (alto nivel)?
1. Subes documentos en Archivos BD.
2. El backend los procesa y los convierte en fragmentos indexados (vector_db).
3. Cuando preguntas, la app busca los fragmentos más similares y se los envía a Gemini para generar una respuesta con base en esos fragmentos.
4. Devuelve respuesta + fuente.

### 2.1 Detalle técnico (RAG con Gemini)
- Frontend envía la pregunta al backend:
  - `assets/js/models/QuestionModel.js` → `askQuestion()` hace POST a `/ask`.
- Backend orquesta el flujo RAG:
  - `app/Controllers/AskController.php` → `askQuestion()`
    - Genera embedding de la pregunta: `AIModel::getEmbedding()`
    - Recupera fragmentos similares: `VectorStoreModel::searchSimilar()` (similitud coseno)
    - Llama a Gemini con el contexto: `AIModel::generateAnswer()`
- Conexión con Gemini (HTTP cURL):
  - `app/Models/AIModel.php` delega en `app/Models/GeminiModel.php`
  - Embeddings: endpoint `text-embedding-004`
  - Generación: endpoint `gemini-2.0-flash:generateContent`
- Dónde se almacenan los vectores:
  - `vector_db/data.json` (embeddings y chunks de texto)


## 3) Requisitos
- Windows con XAMPP o PHP 8 + Composer.
- API key de Gemini (`GEMINI_API_KEY`) en un archivo `.env` en la raíz del proyecto.

## 4) Cómo ejecutar el backend
- Opción A (recomendada para rapidez): Servidor PHP integrado
  1. Abrir PowerShell en la carpeta del proyecto
     ```powershell
     cd "C:\xampp\htdocs\chat mesa ayuda"
     & "C:\xampp\php\php.exe" ".\composer.phar" install --ignore-platform-req=ext-gd
     if (!(Test-Path .env)) { Set-Content -Path .env -Value "GEMINI_API_KEY=TU_API_KEY_DE_GEMINI" }
     & "C:\xampp\php\php.exe" -S localhost:8000 -t public
     ```
  2. Verificar: `http://localhost:8000/` (debe mostrar JSON con “API de Chat IA funcionando”).

- Opción B: Apache con XAMPP (DocumentRoot → `public/`)
  - Frontend: `http://localhost/chat%20mesa%20ayuda/`
  - Backend: `http://localhost/chat%20mesa%20ayuda/public/`

## 5) Cómo usar (paso a paso)
### 5.1 Vista Usuario (chat)
1. Abre `index.html` (doble clic) o `http://localhost/chat%20mesa%20ayuda/` si usas Apache.
2. En la tarjeta superior verás info del sistema.
3. Escribe tu pregunta y presiona “Enviar”.
4. La respuesta aparecerá con su fuente.

Consejo: Si no has subido archivos, la IA te lo indicará.

### 5.2 Vista Archivos BD (gestión de documentos)
1. Desde el ícono ⚙️ en la cabecera, ingresa a “Configuración”.
2. Autentícate (contraseña definida en base de datos; ver `insert_password.sql`).
3. Sube un archivo (PDF/TXT/XLSX/MD) y espera el procesamiento.
4. Revisa “Archivos almacenados” y “Preguntas frecuentes”.
5. Puedes eliminar archivos específicos.

## 6) Estructura de archivos (¿para qué sirve cada uno?)
- `index.html`: Vista Usuario (chat). Carga JS MVC del frontend.
- `archivos-bd.html`: Vista Archivos BD (subidas, tabla de archivos, preguntas frecuentes).

- `assets/css/main.css`: estilos globales (tema, contenedor, botones, mensajes de estado).
- `assets/css/usuario.css`: estilos específicos del chat (burbujas, input, modales, menú).
- `assets/css/archivos-bd.css`: estilos de la vista de archivos (subida, tabla, modales).

- `assets/js/config.js`: configuración de endpoints y autodetección de `API_BASE_URL` (file://, Apache o 8000).
- `assets/js/models/*.js`: capa de modelos en el frontend (estado de app, red, preguntas, archivos, preguntas frecuentes).
- `assets/js/views/*.js`: capa de vistas (render de mensajes, info de red, tabla de archivos, subida).
- `assets/js/controllers/*.js`: orquestación de eventos y flujo (chat y archivos BD).
- `assets/js/utils/sanitize.js`: sanitización básica para contenido dinámico.

- `public/index.php`: punto de entrada del backend (Slim). Define rutas REST.
- `public/.htaccess`: rewrite a `index.php` y cabeceras CORS.

- `app/Controllers/*.php`: controladores REST del backend
  - `AskController.php`: recibe preguntas y genera respuestas con Gemini.
  - `UploadController.php`: procesa y parsea archivos subidos.
  - `FilesController.php`: lista y elimina archivos registrados.
  - `FrequentQuestionsController.php`: lista/busca/elimina preguntas frecuentes.
  - `NetworkInfoController.php`: información básica del sistema.
  - `AuthController.php`: verificación y cambio de contraseña.

- `app/Models/*.php`: lógica de negocio y acceso a datos
  - `GeminiModel.php`: comunicación con Gemini (modelo, API key).
  - `DocumentModel.php`: parseo y fragmentación de documentos.
  - `VectorStoreModel.php`: búsqueda por similitud en `vector_db`.
  - `FileStoreModel.php` y `DatabaseFileStoreModel.php`: almacenamiento/registro de archivos.
  - `FrequentQuestionsModel.php`: persistencia de preguntas frecuentes.
  - `AIModel.php`, `ConfigModel.php`, `Database.php`, `NetworkModel.php`: utilidades y configuración del sistema.

- `vector_db/*.json`: base vectorial (índice de fragmentos y archivos).
- `uploads/`: almacenamiento temporal de subidas.
- `composer.json` / `composer.lock`: dependencias PHP y versiones exactas.
- `composer.phar`: Composer local (si no tienes Composer global).
- `.env`: API key de Gemini.
- `logs/php_errors.log`: registro de errores del backend.

## 7) Seguridad y buenas prácticas
- Mantén `.env` fuera de control de versiones y no compartas tu API key.
- Restringe CORS a tu dominio en producción (editar en `public/index.php`).
- Valida tamaño/mime de archivos al subir (ya implementado, mantén límites razonables).
- Usa contraseñas robustas para la configuración y considera tokens/sesión server‑side.

## 8) Solución de problemas
- No responde el backend:
  - Verifica `http://localhost:8000/` o `http://localhost/chat%20mesa%20ayuda/public/`.
  - PowerShell: `Invoke-WebRequest http://localhost:8000/ -UseBasicParsing`.
- `ERR_CONNECTION_REFUSED` en el front:
  - Asegúrate de haber iniciado el backend. Si abres `index.html` con `file://`, la app usa `http://localhost:8000` por defecto.
- `GEMINI_API_KEY no encontrada`:
  - Crea `.env` con `GEMINI_API_KEY=TU_API_KEY` y reinicia.
- `composer install` pide `ext-gd`:
  - Habilita `extension=gd` en `C:\xampp\php\php.ini` o instala ignorando temporalmente: `--ignore-platform-req=ext-gd`.

## 9) Preguntas frecuentes
- ¿Puedo usarlo sin XAMPP?
  - Sí, con el servidor PHP integrado (Sección 4A).
- ¿Puedo cambiar el tema visual?
  - Edita `assets/css/main.css` y `assets/css/usuario.css` (variables y gradientes).
- ¿Dónde cambio el modelo de Gemini?
  - `app/Models/GeminiModel.php` (propiedad `$this->model`).

---
Este manual resume qué hace la app, cómo ejecutarla y qué función cumple cada archivo relevante.


