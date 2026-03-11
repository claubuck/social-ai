# Arquitectura multi-tenant (varias empresas)

La aplicación está diseñada para **varias empresas** (tenants). Toda la data se aísla por `company_id`.

---

## Modelo de datos por empresa

Cada **Company** tiene:

- **users** (usuarios que pertenecen a la empresa)
- **social_accounts** (cuentas de Instagram, Facebook, LinkedIn, Twitter)
- **posts** (publicaciones)
- **content_topics** (temas para generar contenido con IA)
- **comments** (comentarios sobre posts de la empresa)

Las tablas `engagement_metrics` y `comments` están ligadas a **posts**, que a su vez pertenecen a una company. No hay datos compartidos entre empresas.

---

## Cómo se determina la empresa en cada request

### 1. API con API key (n8n, integraciones)

- **Header obligatorio:** `Authorization: Bearer {N8N_API_KEY}` (mismo valor que en `.env`).
- **GET /api/content-topics** (solo Bearer, sin empresa): devuelve temas de **todas** las empresas; cada item incluye `company_id`. n8n usa ese listado e itera; al crear cada post envía **`X-Company-Id`** con el `company_id` del item.
- **Resto de endpoints** (POST /api/posts, GET /api/posts/pending, etc.): en el request debe ir el header **`X-Company-Id`** con el ID de la empresa.
- Si el `X-Company-Id` no corresponde a una company existente, la API responde 404.

### 2. API con token de usuario (Sanctum)

- **Header:** `Authorization: Bearer {token}` (token creado para un usuario).
- La empresa se toma de **`user->company_id`**. No se usa `X-Company-Id`; el usuario ya está asociado a una empresa.

### 3. Web (dashboard)

- El usuario está logueado y tiene `company_id`. Todas las pantallas (temas, etc.) filtran por la empresa del usuario.

---

## Aislamiento en la API

En los controladores de la API se usa **`$request->getCompanyId()`**, que devuelve:

- Con API key: el valor de **`X-Company-Id`** (o `N8N_COMPANY_ID` si está definido).
- Con Sanctum: el **`company_id`** del usuario.

Todas las lecturas y escrituras (temas, posts, comentarios, pending, mark-published) se filtran o asocian a ese `company_id`. No se devuelve ni se modifica data de otras empresas.

---

## Resumen para n8n (varias empresas)

1. En Laravel `.env`: define **solo** `N8N_API_KEY`. No definas `N8N_COMPANY_ID`.
2. En cada nodo HTTP Request de n8n envía:
   - `Authorization: Bearer {N8N_API_KEY}`
   - `X-Company-Id: {id}` (el ID de la empresa para la que corre ese flujo).
3. Usa un workflow por empresa o un solo workflow que lea el ID de empresa (ej. variable) y lo envíe en `X-Company-Id` en cada llamada a la API.

Los IDs de empresa son los `id` de la tabla `companies` (también visibles en la pantalla “Temas de contenido” para cada usuario según su empresa).
