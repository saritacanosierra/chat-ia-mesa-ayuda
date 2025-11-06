# 🚀 Guía rápida para levantar el backend y ver el frontend

Sigue uno de estos caminos. Si algo falla, baja al apartado “Solución de problemas”.

---

## Opción A) XAMPP/Apache (sin usar puerto 8000)
Ideal si ya tienes XAMPP y prefieres que Apache sirva el backend.

1) Abre XAMPP Control Panel y enciende Apache.
2) Coloca el proyecto en: `C:\xampp\htdocs\chat mesa ayuda\`
3) Abre en el navegador:
   - Frontend: `http://localhost/chat%20mesa%20ayuda/`
   - Backend: `http://localhost/chat%20mesa%20ayuda/public/` (debe mostrar un JSON)
4) Crea el archivo `.env` en la raíz del proyecto con tu API key de Gemini:
   ```
   GEMINI_API_KEY=TU_API_KEY_DE_GEMINI
   ```

Notas:
- Si usas esta opción, abre el frontend por HTTP (no con file://) para que apunte al backend correcto.

---

## Opción B) Servidor PHP integrado (puerto 8000)
Rápido y sin configuración de Apache. Usaremos el PHP de XAMPP.

1) Abre PowerShell en la carpeta del proyecto:
```powershell
cd "C:\xampp\htdocs\chat mesa ayuda"
```
2) Instala dependencias con el composer.phar incluido:
```powershell
& "C:\xampp\php\php.exe" ".\composer.phar" install
```
   - Si aparece error por `ext-gd`, mira “Solución de problemas”.

3) Crea el archivo `.env` (si no existe):
```powershell
Set-Content -Path .env -Value "GEMINI_API_KEY=TU_API_KEY_DE_GEMINI"
```

4) Inicia el servidor en 8000:
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

5) Comprueba el backend:
```powershell
Invoke-RestMethod http://localhost:8000/
```
   - Debe devolver un JSON con “API de Chat IA funcionando”.

6) Abre el frontend:
- Doble clic a `index.html` (se abre como `file://`), o abre con un servidor estático si prefieres.
- Con el ajuste en `assets/js/config.js`, el frontend apuntará a `http://localhost:8000` cuando se abra con `file://`.

---

## Copiar y pegar (PowerShell) – levantar todo de una
Pega esto tal cual en PowerShell para iniciar el backend en `http://localhost:8000`.

```powershell
cd "C:\xampp\htdocs\chat mesa ayuda"

# 1) Instalar dependencias (omite el requisito ext-gd si aún no lo activaste)
& "C:\xampp\php\php.exe" ".\composer.phar" install --ignore-platform-req=ext-gd

# 2) Crear .env si no existe (reemplaza TU_API_KEY_DE_GEMINI por la tuya)
if (!(Test-Path .env)) { Set-Content -Path .env -Value "GEMINI_API_KEY=TU_API_KEY_DE_GEMINI" }

# 3) Iniciar servidor en 8000
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

---

## Verificación rápida (para cualquier opción)
- Respuesta del backend:
```powershell
Invoke-WebRequest http://localhost:8000/ -UseBasicParsing
# o si usas Apache
Invoke-WebRequest "http://localhost/chat%20mesa%20ayuda/public/" -UseBasicParsing
```
- Solo comprobar el puerto 8000:
```powershell
Test-NetConnection localhost -Port 8000
```

---

## Solución de problemas

- Error Composer por `ext-gd` (necesario para `phpspreadsheet`):
  - Abre `C:\xampp\php\php.ini`
  - Busca `;extension=gd` y quita el `;` → `extension=gd`
  - Guarda el archivo y reinicia el comando de Composer:
    ```powershell
    & "C:\xampp\php\php.exe" ".\composer.phar" install
    ```
  - Alternativa temporal (menos recomendable):
    ```powershell
    & "C:\xampp\php\php.exe" ".\composer.phar" install --ignore-platform-req=ext-gd
    ```

- El frontend marca `ERR_CONNECTION_REFUSED`:
  - Asegúrate de que el backend esté corriendo (verificación rápida arriba).
  - Si abriste el frontend con `file://`, inicia el backend en `http://localhost:8000`.
  - Si usas Apache, abre el frontend por `http://localhost/chat%20mesa%20ayuda/` (no uses `file://`).

- Falta la API key:
  - Crea `.env` en la raíz del proyecto con:
    ```
    GEMINI_API_KEY=TU_API_KEY_DE_GEMINI
    ```
  - Obtén tu API key en: https://makersuite.google.com/app/apikey

- Logs de errores del backend:
  - Revisa `logs/php_errors.log`.

---

## Resumen de URLs
- Opción A (Apache)
  - Frontend: `http://localhost/chat%20mesa%20ayuda/`
  - Backend: `http://localhost/chat%20mesa%20ayuda/public/`
- Opción B (PHP integrado)
  - Backend: `http://localhost:8000/`
  - Frontend: abre `index.html` (file://) o sirvelo con un servidor estático.

