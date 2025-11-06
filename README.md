# Chat IA con RAG (Retrieval-Augmented Generation)

Aplicación completa para entrenar una IA con archivos PDF, TXT, XLSX o MD y hacer preguntas sobre su contenido usando un chat interactivo.

## 🚀 Características

- **Backend**: PHP con Slim Framework (estructura MVC)
- **Frontend**: HTML/JS con diseño moderno y responsive (estructura MVC)
- **IA**: Google Gemini - Compatible con PHP 8.0+
- **Base de datos vectorial**: Almacenamiento local con búsqueda por similitud coseno
- **Soporte de archivos**: PDF, TXT, XLSX (Excel) y MD (Markdown)
- **Sistema de roles**: Coordinador (sube archivos) y Usuario (solo chat)

## 📋 Requisitos previos

- PHP 8.0 o superior
- Composer (gestor de dependencias de PHP)
- Servidor web (Apache/Nginx) o PHP built-in server
- Extensión PHP: `mbstring`, `json`, `fileinfo`, `curl`
- API key de Gemini: https://makersuite.google.com/app/apikey (Plan gratuito generoso)

## 🛠️ Instalación

### 1. Clonar o descargar el proyecto

```bash
cd "mesa ayuda iframe"
```

### 2. Instalar dependencias con Composer

```bash
composer install
```

Si no tienes Composer instalado:
- **Windows**: Descarga desde https://getcomposer.org/download/
- **Linux/Mac**: 
  ```bash
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  ```

### 3. Configurar variables de entorno

Copia el archivo `.env.example` a `.env`:

```bash
copy .env.example .env
```

Abre el archivo `.env` y agrega tu API key de Gemini:

```
GEMINI_API_KEY=tu-api-key-de-gemini
```

Obtén tu API key en: https://makersuite.google.com/app/apikey

### 4. Iniciar el servidor

**Opción A: Servidor PHP built-in (desarrollo)**
```bash
php -S localhost:8000 -t public
```

**Opción B: Apache/Nginx**
- Configura el DocumentRoot apuntando a la carpeta `public/`
- Asegúrate de que `.htaccess` esté habilitado

El servidor estará disponible en `http://localhost:8000`

### 5. Abrir el frontend

Abre el archivo `index.html` en tu navegador. Puedes hacerlo de dos formas:

**Opción A: Doble clic**
- Busca el archivo `index.html` en tu explorador de archivos
- Haz doble clic para abrirlo

**Opción B: Desde la terminal**
- **Windows:**
  ```bash
  start index.html
  ```
- **Linux:**
  ```bash
  xdg-open index.html
  ```
- **Mac:**
  ```bash
  open index.html
  ```

## 📖 Uso

### Vista Coordinador (Archivos BD)

1. Abre `archivos-bd.html` en tu navegador
2. Haz clic en "Seleccionar archivo"
3. Elige un archivo PDF, TXT, XLSX o MD desde tu computadora
4. Haz clic en "Subir y Procesar"
5. Espera a que se procese el archivo (esto puede tomar unos segundos)

### Vista Usuario (Chat)

1. Abre `index.html` en tu navegador
2. Verás la información de la red y el chat disponible
3. Escribe tu pregunta sobre el contenido del archivo cargado
4. Haz clic en "Enviar" o presiona Enter
5. La IA responderá basándose en el contenido del archivo

## 🔧 Estructura del proyecto (MVC)

```
mesa ayuda iframe/
├── app/                    # Backend PHP (MVC)
│   ├── Controllers/        # Controladores
│   │   ├── AskController.php
│   │   ├── UploadController.php
│   │   └── NetworkInfoController.php
│   └── Models/             # Modelos
│       ├── DocumentModel.php
│       ├── GeminiModel.php
│       ├── NetworkModel.php
│       └── VectorStoreModel.php
├── assets/                 # Frontend (MVC)
│   ├── css/                # Estilos
│   │   ├── main.css
│   │   ├── usuario.css
│   │   └── archivos-bd.css
│   └── js/                 # JavaScript MVC
│       ├── config.js
│       ├── models/         # Modelos
│       ├── views/          # Vistas
│       ├── controllers/    # Controladores
│       └── utils/          # Utilidades
├── public/                 # Punto de entrada
│   ├── index.php
│   └── .htaccess
├── index.html              # Vista Usuario
├── archivos-bd.html        # Vista Coordinador
├── composer.json           # Dependencias PHP
├── .env                    # Variables de entorno
├── uploads/                # Archivos temporales (se crea automáticamente)
└── vector_db/              # Base vectorial (se crea automáticamente)
```

## 📡 Endpoints de la API

### `GET /`
Verifica que el servidor está funcionando.

### `GET /network-info`
Obtiene información básica de la red.

**Response:**
```json
{
  "network_name": "Red de Mesa de Ayuda",
  "description": "Sistema de asistencia basado en IA",
  "features": [...],
  "status": "operativo"
}
```

### `POST /upload`
Sube un archivo PDF, TXT, XLSX o MD para entrenar la IA.

**Request:**
- `file`: Archivo PDF, TXT, XLSX o MD (multipart/form-data)

**Response:**
```json
{
  "message": "Archivo procesado exitosamente",
  "chunks": 15,
  "status": "success"
}
```

### `POST /ask`
Hace una pregunta sobre el contenido del archivo entrenado.

**Request:**
```json
{
  "question": "¿Cuál es el tema principal del documento?"
}
```

**Response:**
```json
{
  "answer": "El tema principal es...",
  "source": "Fragmento 1 de documento.pdf, Fragmento 2 de documento.pdf"
}
```

## 🎨 Personalización

### Cambiar el modelo de Gemini

En `app/Models/GeminiModel.php`, línea 23, puedes cambiar el modelo:

```php
$this->model = 'gemini-2.5-pro';  // Cambiar a gemini-2.5-pro para mejor calidad
```

### Ajustar el tamaño de los chunks

En `app/Models/DocumentModel.php`, función `splitIntoChunks`:

```php
$chunkSize = 1500;      // Aumentar para chunks más grandes
$chunkOverlap = 300;   // Aumentar para más solapamiento
```

### Cambiar el número de documentos recuperados

En `app/Controllers/AskController.php`, línea 47:

```php
$similarChunks = $vectorStoreModel->searchSimilar($questionEmbedding, 5); // Cambiar k
```

## ⚠️ Solución de problemas

### Error: "GEMINI_API_KEY no encontrada"
- Verifica que el archivo `.env` existe y contiene tu API key de Gemini
- Asegúrate de que el archivo se llama exactamente `.env` (con el punto)
- Obtén tu API key en: https://makersuite.google.com/app/apikey

### Error: "Class not found"
Ejecuta:
```bash
composer dump-autoload
```

### Error: "mbstring extension not found"
Instala la extensión PHP mbstring:
- **Windows**: Descomenta `extension=mbstring` en `php.ini`
- **Linux**: `sudo apt-get install php-mbstring`
- **Mac**: Ya viene incluido generalmente

### Error: "No hay archivo entrenado"
- Primero debes subir un archivo usando `archivos-bd.html`
- Verifica que el servidor backend esté corriendo

### Error de conexión en el frontend
- Verifica que el servidor backend está corriendo en `http://localhost:8000`
- Si cambias el puerto, actualiza las URLs en `assets/js/config.js`

### El archivo no se sube
- Verifica que el archivo es PDF, TXT, XLSX o MD
- Revisa que el tamaño del archivo no sea demasiado grande
- Mira los logs del servidor para ver errores específicos

## 📝 Notas

- La primera vez que subes un archivo, puede tardar más tiempo en procesarse
- Los archivos se procesan y luego se eliminan (solo se guarda la base vectorial)
- La base de datos vectorial se guarda en `vector_db/data.json` y persiste entre sesiones
- Si subes un nuevo archivo, se reemplazará el anterior
- El sistema de roles permite que los coordinadores suban archivos y los usuarios solo hagan preguntas

## 🔒 Seguridad

- En producción, cambia CORS en `public/index.php` por los orígenes específicos
- No compartas tu archivo `.env` ni tu API key de Gemini
- Considera agregar autenticación para los endpoints en producción
- Valida y sanitiza todas las entradas del usuario

## 📄 Licencia

Este proyecto es de código abierto y está disponible para uso personal y educativo.

## 📚 Referencias

- [Slim Framework](https://www.slimframework.com/)
- [Google Gemini API](https://ai.google.dev/)
- [Composer](https://getcomposer.org/)
