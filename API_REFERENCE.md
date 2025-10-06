# 📚 Referencia del API Ford - Solo para Consulta

> **⚠️ IMPORTANTE:** Esta carpeta del API (`ebook-api`) es solo para referencia. NO debe modificarse. Solo sirve para entender cómo funciona el backend.

## 🔗 Endpoints Principales del API

### 1. `/sites/actives` - Sitios Activos
**URL:** `https://ford-api-ford-api.ppm09i.easypanel.host/sites/actives`

**Descripción:** Devuelve la lista de sitios marcados como activos en la base de datos.

**Respuesta esperada:**
```json
[
    {
        "id": 9,
        "url": "https://www.fordlapiedad.mx/",
        "title": "Ford La Piedad",
        "folderName": "fordlapiedad",
        "active": true,
        "images": [
            {
                "id": 725,
                "filename": "cover_octubre.jpg",
                "src": "https://ford-api-ford-api.ppm09i.easypanel.host/public/images/725/...",
                "reftype": "portada"
            },
            {
                "id": 726,
                "filename": "opengraph_octubre.jpg",
                "src": "https://ford-api-ford-api.ppm09i.easypanel.host/public/images/726/...",
                "reftype": "opengraph"
            }
        ]
    },
    {
        "id": 1,
        "url": "https://www.cavsacolima.mx/",
        "title": "Ford Cavsa Motors",
        "folderName": "fordcavsamotors",
        "active": true,
        "images": [...]
    }
]
```

### 2. `/sites/by_folder_name/{folderName}` - Sitio Individual
**URL:** `https://ford-api-ford-api.ppm09i.easypanel.host/sites/by_folder_name/fordlapiedad`

**Descripción:** Devuelve información detallada de un sitio específico.

**⚠️ PROBLEMA CONOCIDO:** Este endpoint devuelve `"[object Object]"` en el campo `title` en lugar del título real.

## 🏗️ Estructura del API (Referencia)

### Archivos Principales:
- `index.js` - Servidor principal Fastify
- `app/routes/sites.js` - Rutas de sitios
- `app/models/site.js` - Modelo de sitio
- `app/models/image.js` - Modelo de imágenes

### Base de Datos:
- **Sites:** Tabla principal con información de sitios
- **Images:** Imágenes asociadas a cada sitio
- **Cars:** Vehículos por sitio
- **Contact_mails:** Emails de contacto

## 🔄 Cómo el Cliente Usa el API

### Flujo Normal:
1. **Cliente llama** → `/sites/actives`
2. **API devuelve** → Lista de sitios activos
3. **Cliente procesa** → Cada sitio individualmente
4. **Cliente genera** → Carpetas desde `basesite`
5. **Cliente actualiza** → JSON con datos del API

### Flujo de Fallback (Cuando API falla):
1. **Cliente detecta** → Error de conectividad
2. **Cliente usa** → `local_api_data.json`
3. **Cliente genera** → Sitios con datos locales
4. **Usuario ve** → Mensaje de advertencia

## 📁 Estructura de Datos que Espera el Cliente

### Campos Requeridos:
```json
{
    "id": "integer",
    "folderName": "string", // Nombre de la carpeta a crear
    "title": "string",      // Título del sitio
    "url": "string",        // URL del sitio
    "active": "boolean",    // Si está activo
    "images": [             // Imágenes del sitio
        {
            "id": "integer",
            "filename": "string",
            "src": "string",    // URL completa de la imagen
            "reftype": "string" // "portada" o "opengraph"
        }
    ]
}
```

### Campos Opcionales:
- `headTitle` - Título para el head HTML
- `map` - Código del mapa embebido
- `terms` - Términos y condiciones
- `facebook` - URL de Facebook
- `whatsapp` - Número de WhatsApp

## 🛠️ Modificaciones Realizadas en el Cliente

### Problema del Título "[object Object]":
**Causa:** El endpoint `/sites/by_folder_name/{folder}` devuelve un objeto en lugar de string.

**Solución en Cliente:**
```php
// En functions.php - editJson()
// Usar título del API /sites/actives en lugar del individual
if (!empty($correctTitle)) {
    $jsonArray['title']['title'] = $correctTitle;
}
```

### Sistema de Fallback:
**Problema:** Servidor no puede conectar al API externo.

**Solución:**
1. Múltiples URLs de prueba
2. Datos locales como respaldo
3. Mensajes informativos al usuario

## 🚫 Lo que NO se Debe Modificar

- **Carpeta `ebook-api/`** - Solo para referencia
- **Base de datos del API** - Manejada por el equipo del backend
- **Endpoints del API** - Estructura fija
- **URLs del API** - Configuradas en producción

## ✅ Lo que SÍ se Puede Modificar

- **Cliente `ebook-client/`** - Toda la lógica del cliente
- **Archivo `local_api_data.json`** - Datos de fallback
- **Función `processSitesOneByOne()`** - Lógica de procesamiento
- **Templates en `basesite/`** - Estructura base de los sitios

## 🎯 Resumen

El API es la fuente de datos, el cliente es el generador de sitios. El cliente debe ser robusto y funcionar incluso cuando el API no esté disponible, usando datos locales como respaldo.
