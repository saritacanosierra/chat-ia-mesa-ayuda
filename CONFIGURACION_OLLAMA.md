# Configuración de Ollama

Esta guía te ayudará a cambiar de Google Gemini a Ollama (LLM local gratuito).

## ¿Qué es Ollama?

Ollama es un servicio local que permite ejecutar modelos de lenguaje grandes (LLMs) en tu propia computadora, sin necesidad de API keys ni límites de uso. Es completamente gratuito y funciona offline.

## Instalación de Ollama

### Windows
1. Descarga el instalador desde: https://ollama.ai/download
2. Ejecuta el instalador
3. Ollama se iniciará automáticamente

### Linux
```bash
curl -fsSL https://ollama.ai/install.sh | sh
```

### Mac
```bash
brew install ollama
```

## Descargar un Modelo

Una vez instalado Ollama, necesitas descargar un modelo. Abre una terminal y ejecuta:

```bash
# Modelo recomendado (equilibrado entre calidad y velocidad)
ollama pull llama2

# Otras opciones:
ollama pull mistral      # Más rápido
ollama pull codellama    # Mejor para código
ollama pull llama2:13b   # Versión más grande (mejor calidad, más lento)
```

## Configuración en el Proyecto

### Opción 1: Usar Gemini con Ollama como respaldo (Recomendado)

Esta es la configuración más robusta. Gemini será el principal, pero si falla (rate limits, errores), automáticamente usará Ollama:

```env
# Usar Gemini como principal
AI_PROVIDER=gemini
GEMINI_API_KEY=tu-api-key-de-gemini

# Activar fallback automático a Ollama (por defecto: true)
AI_USE_FALLBACK=true

# Configuración de Ollama (opcional, estos son los valores por defecto)
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama2
```

### Opción 2: Usar solo Ollama

```env
# Cambiar a Ollama
AI_PROVIDER=ollama

# Configuración de Ollama (opcional, estos son los valores por defecto)
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama2

# Opcional: Si quieres usar Gemini solo para embeddings (mejor calidad)
# GEMINI_API_KEY=tu-api-key-de-gemini
```

### Opción 3: Usar Ollama con Gemini como respaldo

```env
# Usar Ollama como principal
AI_PROVIDER=ollama
OLLAMA_MODEL=llama2

# Gemini como respaldo
GEMINI_API_KEY=tu-api-key-de-gemini
AI_USE_FALLBACK=true
```

### 2. Verificar que Ollama esté corriendo

Antes de usar la aplicación, asegúrate de que Ollama esté corriendo:

```bash
# Verificar que Ollama esté corriendo
curl http://localhost:11434/api/tags
```

Si ves una respuesta JSON, Ollama está funcionando correctamente.

## Opciones de Configuración

### Usar solo Ollama (completamente gratuito)
```env
AI_PROVIDER=ollama
OLLAMA_MODEL=llama2
```

### Usar Ollama para generación + Gemini para embeddings (recomendado)
```env
AI_PROVIDER=ollama
OLLAMA_MODEL=llama2
GEMINI_API_KEY=tu-api-key-de-gemini
```

Esto usa Ollama para generar respuestas (gratis) pero Gemini para embeddings (mejor calidad de búsqueda).

### Volver a Gemini
```env
AI_PROVIDER=gemini
GEMINI_API_KEY=tu-api-key-de-gemini
```

## Modelos Disponibles en Ollama

## 🎯 ¿Cuál Modelo es Mejor para Mesa de Ayuda?

### Recomendación Principal: **llama3** o **llama3.2** ⭐

**Para la mayoría de casos de mesa de ayuda, recomiendo:**

```bash
ollama pull llama3
# o la versión más reciente
ollama pull llama3.2
```

**Configuración en `.env`:**
```env
OLLAMA_MODEL=llama3
# o
OLLAMA_MODEL=llama3.2
```

**¿Por qué?**
- ✅ **Excelente calidad** - Entiende contexto y genera respuestas claras
- ✅ **Equilibrado** - Buen balance entre calidad y velocidad
- ✅ **Actualizado** - Versión más reciente con mejor rendimiento
- ✅ **Requisitos razonables** - ~8GB RAM (más común)
- ✅ **Ideal para texto** - Perfecto para chat y respuestas de ayuda

### Comparación Rápida

| Modelo | Calidad | Velocidad | RAM | Mejor Para |
|--------|---------|-----------|-----|------------|
| **llama3 / llama3.2** ⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | 8GB | **Mesa de ayuda (RECOMENDADO)** |
| **llama2** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | 8GB | General, buena opción |
| **mistral** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | 8GB | Si necesitas máxima velocidad |
| **qwen2.5** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | 8GB | Alta calidad, multilingüe |
| **llama2:13b** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | 16GB | Si tienes más RAM y quieres mejor calidad |
| **phi** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | 4GB | Si tienes poca RAM |
| **codellama** | ⭐⭐⭐ | ⭐⭐⭐ | 8GB | Solo si necesitas código |

### Recomendaciones por Escenario

#### 🏆 **Mejor Opción General: llama3 o llama3.2**
```bash
ollama pull llama3.2
```
```env
OLLAMA_MODEL=llama3.2
```
**Ideal para:** Mesa de ayuda, chat general, respuestas profesionales

#### ⚡ **Si Necesitas Máxima Velocidad: mistral**
```bash
ollama pull mistral
```
```env
OLLAMA_MODEL=mistral
```
**Ideal para:** Respuestas rápidas, muchos usuarios simultáneos

#### 💎 **Si Tienes 16GB+ RAM: llama3:8b o llama2:13b**
```bash
ollama pull llama3:8b
# o
ollama pull llama2:13b
```
```env
OLLAMA_MODEL=llama3:8b
```
**Ideal para:** Máxima calidad, servidores con más recursos

#### 💻 **Si Tienes Poca RAM (<8GB): phi o orca-mini**
```bash
ollama pull phi
# o
ollama pull orca-mini
```
```env
OLLAMA_MODEL=phi
```
**Ideal para:** Sistemas con recursos limitados

#### 🌍 **Si Necesitas Multilingüe: qwen2.5**
```bash
ollama pull qwen2.5
```
```env
OLLAMA_MODEL=qwen2.5
```
**Ideal para:** Soporte en múltiples idiomas

### Modelos Populares Recomendados

#### Modelos Generales (Chat y Texto)
- **llama2** o **llama2:7b**: Modelo equilibrado, recomendado para la mayoría de casos (requiere ~8GB RAM)
- **llama2:13b**: Versión más grande, mejor calidad pero más lento (requiere ~16GB RAM)
- **llama2:70b**: Versión muy grande, requiere mucha RAM (requiere ~40GB RAM)
- **mistral** o **mistral:7b**: Más rápido, buena calidad (requiere ~8GB RAM)
- **mistral:13b**: Versión más grande de Mistral (requiere ~16GB RAM)
- **gemma:7b**: Modelo de Google, equilibrado y eficiente (requiere ~8GB RAM)
- **phi**: Modelo pequeño y rápido (requiere ~4GB RAM)
- **orca-mini**: Modelo pequeño, ideal para pruebas (requiere ~4GB RAM)

#### Modelos Especializados en Código
- **codellama** o **codellama:7b**: Especializado en código y programación (requiere ~8GB RAM)
- **codellama:13b**: Versión más grande para código complejo (requiere ~16GB RAM)
- **deepseek-coder**: Modelo especializado en código (requiere ~8GB RAM)
- **qwen2.5-coder**: Modelo Qwen especializado en código (requiere ~8GB RAM)

#### Modelos Multimodales y Avanzados
- **llama3**: Versión más reciente de Llama (requiere ~8GB RAM)
- **llama3:70b**: Versión grande de Llama 3 (requiere ~40GB RAM)
- **qwen2.5**: Modelo chino de alta calidad (requiere ~8GB RAM)
- **qwen2.5:14b**: Versión más grande de Qwen (requiere ~16GB RAM)
- **deepseek-r1**: Modelo avanzado de DeepSeek (requiere ~8GB RAM)

### Ver Modelos Instalados

#### Opción 1: Desde la Terminal (Recomendado)
```bash
ollama list
```

Esto mostrará algo como:
```
NAME            ID              SIZE    MODIFIED
llama2          abc123...       3.8 GB  2 hours ago
mistral         def456...       4.1 GB  1 day ago
codellama       ghi789...       3.8 GB  3 days ago
```

#### Opción 2: Endpoint de API (Recomendado)
Puedes consultar los modelos desde la API:
```bash
curl http://localhost:8000/ollama-models
```

O desde el navegador:
```
http://localhost:8000/ollama-models
```

Este endpoint te mostrará:
- ✅ Si Ollama está disponible
- 📦 Lista de modelos instalados con tamaño y fecha
- ⭐ Modelo actual configurado en `.env`
- ⚠️ Advertencias si el modelo configurado no está instalado

La respuesta incluirá:
```json
{
  "success": true,
  "message": "Modelos obtenidos correctamente",
  "models": [
    {
      "name": "llama2",
      "size": "3.8 GB",
      "modified": "2024-01-15 10:30:00",
      "digest": "abc123..."
    }
  ],
  "current_model": "llama2",
  "ollama_url": "http://localhost:11434"
}
```

### Buscar Modelos Disponibles

Para buscar modelos disponibles en el repositorio de Ollama:
```bash
# Ver modelos populares en el sitio web
# Visita: https://ollama.com/library

# O busca desde la terminal (si tienes acceso)
ollama search [término]
```

### Descargar un Modelo

Para descargar un modelo específico:
```bash
# Modelos generales
ollama pull llama2
ollama pull mistral
ollama pull gemma:7b
ollama pull llama3

# Modelos de código
ollama pull codellama
ollama pull deepseek-coder
ollama pull qwen2.5-coder

# Modelos grandes (requieren más RAM)
ollama pull llama2:13b
ollama pull llama3:70b
ollama pull qwen2.5:14b
```

### Configurar un Modelo Específico

Una vez que hayas descargado un modelo, configúralo en tu archivo `.env`:

1. **Abre el archivo `.env`** en la raíz del proyecto

2. **Busca o agrega la línea `OLLAMA_MODEL`**:
```env
OLLAMA_MODEL=llama2
```

3. **Reemplaza `llama2` con el nombre del modelo que quieres usar**:
```env
# Ejemplos:
OLLAMA_MODEL=qwen2.5-coder    # Para código
OLLAMA_MODEL=deepseek-coder   # Para código avanzado
OLLAMA_MODEL=mistral          # Más rápido
OLLAMA_MODEL=llama3           # Más reciente
OLLAMA_MODEL=llama2:13b       # Versión más grande
```

4. **Verifica que el modelo esté instalado**:
```bash
# Desde la terminal de Ollama
ollama list

# O desde la API
curl http://localhost:8000/ollama-models
```

5. **Reinicia el servidor** si está corriendo para que tome los cambios.

**Nota**: El nombre del modelo debe coincidir exactamente con el nombre que muestra `ollama list`. Por ejemplo, si instalaste `qwen2.5-coder`, usa exactamente `qwen2.5-coder` en el `.env`.

### Usar Modelos Personalizados de Ollama

Ollama permite crear modelos personalizados con prompts específicos. Esto es útil para adaptar el comportamiento del modelo a tu caso de uso (por ejemplo, mesa de ayuda).

#### Crear un Modelo Personalizado

1. **Descarga el modelo base**:
```bash
ollama pull llama3.2
# o cualquier otro modelo base
```

2. **Crea un Modelfile** (archivo de configuración):
```bash
# Crea un archivo llamado Modelfile
echo "FROM llama3.2" > Modelfile
echo "SYSTEM Eres un asistente de mesa de ayuda profesional y amigable. Tu objetivo es ayudar a los usuarios con sus consultas de manera clara, concisa y útil." >> Modelfile
```

3. **Crea el modelo personalizado**:
```bash
ollama create mi-mesa-ayuda -f Modelfile
```

4. **Configúralo en tu `.env`**:
```env
OLLAMA_MODEL=mi-mesa-ayuda
```

#### Ejemplo: Modelo Personalizado para Mesa de Ayuda

Puedes crear un modelo específico para tu aplicación de mesa de ayuda:

```bash
# 1. Descargar modelo base
ollama pull llama3.2

# 2. Crear Modelfile personalizado
cat > Modelfile << EOF
FROM llama3.2
SYSTEM Eres un asistente de mesa de ayuda profesional y amigable. 
Tu objetivo es ayudar a los usuarios con sus consultas técnicas y administrativas.
- Responde de manera clara y concisa
- Usa un tono profesional pero amigable
- Proporciona soluciones prácticas
- Si no sabes algo, admítelo y ofrece contactar con soporte técnico
EOF

# 3. Crear el modelo
ollama create mesa-ayuda-asistente -f Modelfile

# 4. Configurar en .env
# OLLAMA_MODEL=mesa-ayuda-asistente
```

#### Usar Modelos de Otros Usuarios (Ollama Hub)

Si alguien ha publicado un modelo en Ollama Hub (como `umarketing343/quokka`), puedes usarlo directamente:

1. **Descargar el modelo**:
```bash
ollama pull umarketing343/quokka
```

2. **Configurarlo en `.env`**:
```env
OLLAMA_MODEL=umarketing343/quokka
```

3. **Verificar que esté instalado**:
```bash
# Desde la terminal de Ollama
ollama list

# O desde la API
curl http://localhost:8000/ollama-models
```

**Nota**: Los modelos personalizados funcionan igual que los modelos oficiales. Solo necesitas usar el nombre exacto del modelo en tu archivo `.env`.

### Nota sobre los Modelos de la Imagen

Los modelos que aparecen en la imagen (gpt-oss, deepseek-v3.1, qwen3-coder, qwen3-vl, minimax-m2, alm-4.6) son **modelos cloud** de otros proveedores, **NO son modelos de Ollama**.

Ollama tiene sus propios modelos que puedes descargar e instalar localmente. Algunos modelos similares disponibles en Ollama incluyen:
- **qwen2.5-coder** (similar a qwen3-coder)
- **deepseek-coder** (similar a deepseek-v3.1)
- **qwen2.5** (similar a qwen3-vl)

Para ver todos los modelos disponibles en Ollama, visita: https://ollama.com/library

## Sistema de Fallback Automático

El sistema ahora soporta **fallback automático** entre Gemini y Ollama:

### ¿Cómo funciona?

1. **Proveedor Principal**: Intenta usar el proveedor configurado (Gemini u Ollama)
2. **Fallback Automático**: Si el proveedor principal falla (rate limits, errores, etc.), automáticamente intenta con el otro
3. **Transparente**: El usuario no nota la diferencia, solo recibe la respuesta

### Ejemplo de uso:

```env
# Gemini como principal, Ollama como respaldo
AI_PROVIDER=gemini
GEMINI_API_KEY=tu-api-key
AI_USE_FALLBACK=true
OLLAMA_MODEL=llama2
```

**Escenario**: 
- Usuario hace una pregunta → Sistema intenta con Gemini
- Gemini responde con error 429 (rate limit) → Sistema automáticamente usa Ollama
- Usuario recibe la respuesta sin saber que hubo un cambio

### Ventajas del Fallback

✅ **Alta disponibilidad** - Si un servicio falla, el otro toma el relevo
✅ **Sin interrupciones** - El usuario siempre recibe una respuesta
✅ **Mejor experiencia** - No hay errores por rate limits o problemas temporales
✅ **Flexible** - Puedes desactivar el fallback si quieres: `AI_USE_FALLBACK=false`

## Ventajas de Ollama

✅ **Completamente gratuito** - Sin límites de uso
✅ **Funciona offline** - No necesitas internet después de descargar el modelo
✅ **Sin rate limits** - Puedes hacer todas las peticiones que quieras
✅ **Privacidad** - Todo se procesa localmente
✅ **Sin costos** - No hay facturación ni API keys

## Desventajas

⚠️ **Requiere recursos** - Necesitas RAM suficiente (mínimo 8GB recomendado)
⚠️ **Más lento** - Depende de tu hardware
⚠️ **Embeddings simples** - Ollama no tiene embeddings nativos de alta calidad (puedes usar Gemini solo para embeddings)

## Solución de Problemas

### Error: "Ollama no está disponible"
- Verifica que Ollama esté corriendo: `ollama serve`
- Verifica la URL en `.env`: `OLLAMA_BASE_URL=http://localhost:11434`

### Error: "Model not found"
- Descarga el modelo: `ollama pull llama2`
- Verifica el nombre del modelo en `.env`: `OLLAMA_MODEL=llama2`

### Respuestas muy lentas
- Usa un modelo más pequeño: `ollama pull mistral`
- O reduce el tamaño del modelo: `OLLAMA_MODEL=llama2:7b`

### Respuestas de baja calidad
- Usa un modelo más grande: `OLLAMA_MODEL=llama2:13b`
- O combina con Gemini para embeddings: configura `GEMINI_API_KEY`

## Notas Importantes

1. **Primera ejecución**: La primera vez que uses un modelo, Ollama lo descargará (puede tardar varios minutos dependiendo del tamaño).

2. **Memoria RAM**: Los modelos grandes requieren mucha RAM. Para `llama2:13b` necesitas al menos 16GB de RAM.

3. **Embeddings**: Ollama no tiene embeddings nativos de alta calidad. Si necesitas mejor calidad en la búsqueda de documentos, considera usar Gemini solo para embeddings manteniendo Ollama para generación.

4. **Rendimiento**: El rendimiento depende de tu hardware. En CPUs modernos, `llama2` puede generar respuestas en 5-15 segundos.

