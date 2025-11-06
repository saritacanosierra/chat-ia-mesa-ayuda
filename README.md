<<<<<<< HEAD
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
=======
# chat-IA-mesa 



## Getting started

To make it easy for you to get started with GitLab, here's a list of recommended next steps.

Already a pro? Just edit this README.md and make it your own. Want to make it easy? [Use the template at the bottom](#editing-this-readme)!

## Add your files

- [ ] [Create](https://docs.gitlab.com/ee/user/project/repository/web_editor.html#create-a-file) or [upload](https://docs.gitlab.com/ee/user/project/repository/web_editor.html#upload-a-file) files
- [ ] [Add files using the command line](https://docs.gitlab.com/topics/git/add_files/#add-files-to-a-git-repository) or push an existing Git repository with the following command:

```
cd existing_repo
git remote add origin http://172.16.1.15/produc_dev/chat-ia-mesa.git
git branch -M main
git push -uf origin main
```

## Integrate with your tools

- [ ] [Set up project integrations](http://172.16.1.15/produc_dev/chat-ia-mesa/-/settings/integrations)

## Collaborate with your team

- [ ] [Invite team members and collaborators](https://docs.gitlab.com/ee/user/project/members/)
- [ ] [Create a new merge request](https://docs.gitlab.com/ee/user/project/merge_requests/creating_merge_requests.html)
- [ ] [Automatically close issues from merge requests](https://docs.gitlab.com/ee/user/project/issues/managing_issues.html#closing-issues-automatically)
- [ ] [Enable merge request approvals](https://docs.gitlab.com/ee/user/project/merge_requests/approvals/)
- [ ] [Set auto-merge](https://docs.gitlab.com/user/project/merge_requests/auto_merge/)

## Test and Deploy

Use the built-in continuous integration in GitLab.

- [ ] [Get started with GitLab CI/CD](https://docs.gitlab.com/ee/ci/quick_start/)
- [ ] [Analyze your code for known vulnerabilities with Static Application Security Testing (SAST)](https://docs.gitlab.com/ee/user/application_security/sast/)
- [ ] [Deploy to Kubernetes, Amazon EC2, or Amazon ECS using Auto Deploy](https://docs.gitlab.com/ee/topics/autodevops/requirements.html)
- [ ] [Use pull-based deployments for improved Kubernetes management](https://docs.gitlab.com/ee/user/clusters/agent/)
- [ ] [Set up protected environments](https://docs.gitlab.com/ee/ci/environments/protected_environments.html)

***

# Editing this README

When you're ready to make this README your own, just edit this file and use the handy template below (or feel free to structure it however you want - this is just a starting point!). Thanks to [makeareadme.com](https://www.makeareadme.com/) for this template.

## Suggestions for a good README

Every project is different, so consider which of these sections apply to yours. The sections used in the template are suggestions for most open source projects. Also keep in mind that while a README can be too long and detailed, too long is better than too short. If you think your README is too long, consider utilizing another form of documentation rather than cutting out information.

## Name
Choose a self-explaining name for your project.

## Description
Let people know what your project can do specifically. Provide context and add a link to any reference visitors might be unfamiliar with. A list of Features or a Background subsection can also be added here. If there are alternatives to your project, this is a good place to list differentiating factors.

## Badges
On some READMEs, you may see small images that convey metadata, such as whether or not all the tests are passing for the project. You can use Shields to add some to your README. Many services also have instructions for adding a badge.

## Visuals
Depending on what you are making, it can be a good idea to include screenshots or even a video (you'll frequently see GIFs rather than actual videos). Tools like ttygif can help, but check out Asciinema for a more sophisticated method.

## Installation
Within a particular ecosystem, there may be a common way of installing things, such as using Yarn, NuGet, or Homebrew. However, consider the possibility that whoever is reading your README is a novice and would like more guidance. Listing specific steps helps remove ambiguity and gets people to using your project as quickly as possible. If it only runs in a specific context like a particular programming language version or operating system or has dependencies that have to be installed manually, also add a Requirements subsection.

## Usage
Use examples liberally, and show the expected output if you can. It's helpful to have inline the smallest example of usage that you can demonstrate, while providing links to more sophisticated examples if they are too long to reasonably include in the README.

## Support
Tell people where they can go to for help. It can be any combination of an issue tracker, a chat room, an email address, etc.

## Roadmap
If you have ideas for releases in the future, it is a good idea to list them in the README.

## Contributing
State if you are open to contributions and what your requirements are for accepting them.

For people who want to make changes to your project, it's helpful to have some documentation on how to get started. Perhaps there is a script that they should run or some environment variables that they need to set. Make these steps explicit. These instructions could also be useful to your future self.

You can also document commands to lint the code or run tests. These steps help to ensure high code quality and reduce the likelihood that the changes inadvertently break something. Having instructions for running tests is especially helpful if it requires external setup, such as starting a Selenium server for testing in a browser.

## Authors and acknowledgment
Show your appreciation to those who have contributed to the project.

## License
For open source projects, say how it is licensed.

## Project status
If you have run out of energy or time for your project, put a note at the top of the README saying that development has slowed down or stopped completely. Someone may choose to fork your project or volunteer to step in as a maintainer or owner, allowing your project to keep going. You can also make an explicit request for maintainers.
>>>>>>> b8204b6d1e50ce29d48ddf2ada120c2f16bf7e9e
