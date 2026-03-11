# Integración n8n — Flujos de automatización

La app es **multi-tenant** (varias empresas). Ver [ARQUITECTURA_MULTI_TENANT.md](ARQUITECTURA_MULTI_TENANT.md) para el diseño general.

**La publicación en redes la hace siempre Laravel** (comando `posts:publish-scheduled` + cola). n8n solo genera el contenido; cuando n8n devuelve a Laravel (POST /api/posts), Laravel crea el post y, si se indica, lo publica en la red.

---

## Flujo manual: "Generar publicación" (Laravel → n8n → Laravel)

El usuario en Laravel hace clic en **Generar publicación**, escribe un tema y envía. Laravel llama al **webhook de n8n**. n8n genera el contenido con IA y **vuelve a Laravel** (POST /api/posts). Laravel crea el post y lo publica (o lo programa).

### 1. Configuración en Laravel

En `.env`:

```env
N8N_WEBHOOK_GENERATE_URL=https://tu-n8n.com/webhook/generar-post
```

(URL del webhook que crees en n8n.)

### 2. Qué envía Laravel al webhook (POST)

Laravel hace **POST** al `N8N_WEBHOOK_GENERATE_URL` con body JSON:

```json
{
  "topic": "texto que escribió el usuario",
  "company_id": 1,
  "platform": "instagram",
  "publish_immediately": true,
  "publish_at": null
}
```

- `publish_immediately`: si es `true`, al crear el post Laravel lo encola y lo publica en la red en seguida.
- `publish_at`: si no es "publicar ya", puede ser una fecha ISO para programar.

### 3. Workflow en n8n (webhook que recibe y devuelve a Laravel)

| Nodo | Configuración |
|------|----------------|
| **1. Webhook** | Método POST. Respuesta: "Received" (o devolver 200 rápido). El body tendrá `topic`, `company_id`, `platform`, `publish_immediately`, `publish_at`. |
| **2. OpenAI** | Prompt: *"Genera un post viral para {{ $json.body.topic }} (plataforma: {{ $json.body.platform }}). Incluye emojis y hashtags. Máximo 120 palabras."* Entrada: el body del webhook. |
| **3. HTTP Request — Crear y publicar en Laravel** | **POST** `{{LARAVEL_URL}}/api/posts`. Headers: `Authorization: Bearer {{N8N_API_KEY}}`, `X-Company-Id: {{ $json.body.company_id }}`. Body (JSON): `platform: {{ $json.body.platform }}`, `content: {{ salida OpenAI }}`, `status: "scheduled"`, `publish_immediately: {{ $json.body.publish_immediately }}`, y si hay `publish_at` incluirlo. |

Laravel recibe el POST, crea el post y, si `publish_immediately` es true, encola el job que publica en la red (Instagram, Facebook, etc.).

### 4. Resumen del flujo

```
Usuario (Laravel) → "Generar publicación" + tema
    → Laravel POST a n8n webhook (topic, company_id, platform, publish_immediately)
    → n8n: OpenAI genera el texto
    → n8n: POST /api/posts a Laravel (content, X-Company-Id, publish_immediately)
    → Laravel: crea el post y, si publish_immediately, encola PublishPostJob
    → Laravel (worker): publica en la red y marca post como published
```

---

## Autenticación

Una sola **API key** en el servidor (`.env` → `N8N_API_KEY`). En cada request: **Header** `Authorization: Bearer tu-clave`.

- **GET /api/content-topics**: no hace falta enviar empresa. Laravel devuelve temas de **todas** las empresas, cada uno con `company_id`. n8n usa ese listado e itera.
- **POST /api/posts**, **GET /api/posts/pending**, etc.: en esos requests sí envía el header **`X-Company-Id`** con el ID de la empresa (el mismo `company_id` que viene en cada tema al crear el post).

---

## Cómo probar el flujo (después de crear temas)

### 1. Configurar API key en .env (multi-tenant)

En el servidor Laravel, en `.env`:

```env
N8N_API_KEY=mi-clave-n8n-secreta
```

No definas `N8N_COMPANY_ID`; la empresa se envía en cada request con el header `X-Company-Id`.

### 2. Probar sin n8n (curl)

Solo API key (listado de todas las empresas):

```bash
curl -H "Authorization: Bearer mi-clave-n8n-secreta" https://tu-dominio.com/api/content-topics
```

Respuesta: array de objetos con `id`, `company_id` y `topic`, ej. `[{"id":1,"company_id":1,"topic":"tips de marketing"},{"id":2,"company_id":2,"topic":"promociones"}]`.

### 3. Probar con n8n (Flujo 1)

1. En n8n crea un workflow nuevo.
2. Añade un nodo **Schedule** (Cron): por ejemplo “Every day at 9:00 AM”.
3. Añade un nodo **HTTP Request**:
   - Method: **GET**
   - URL: `https://tu-dominio.com/api/content-topics`
   - Headers: solo `Authorization` = `Bearer {{N8N_API_KEY}}` (no envíes X-Company-Id).
4. La respuesta es un array de objetos: `{ "id", "company_id", "topic" }` de **todas** las empresas.
5. En n8n haz un **Loop** sobre ese array. Para cada item: **OpenAI** (genera texto para `item.topic`), luego **HTTP Request** `POST /api/posts` con header **`X-Company-Id`** = `item.company_id` y body con el contenido generado. Así cada post se crea en la empresa correcta.

### Qué pasa después de crear el tema

- El tema queda guardado en la base de datos asociado a tu empresa.
- **GET /api/content-topics** (solo Bearer): devuelve temas de todas las empresas, cada uno con `company_id`. n8n no envía empresa en esta llamada.
- Para **crear el post** (POST /api/posts) n8n envía el header **`X-Company-Id`** con el `company_id` del item actual del loop, así el post se asocia a la empresa correcta.

---

## Flujo completo: de temas a publicación

Resumen en cadena:

```
1. Cron (n8n) cada día 9:00
       ↓
2. n8n → GET /api/content-topics (solo Bearer)
       ↓
3. Laravel devuelve [{ id, company_id, topic }, ...] de todas las empresas
       ↓
4. n8n hace LOOP sobre cada item
       ↓
5. Para cada item: OpenAI genera el texto del post a partir de item.topic
       ↓
6. n8n → POST /api/posts con header X-Company-Id: item.company_id y body (content, platform, status: scheduled, publish_at)
       ↓
7. Laravel guarda el post en la empresa correcta (status: scheduled)
       ↓
8. Cuando llega publish_at: Laravel (comando posts:publish-scheduled + cola) publica en la red O n8n hace Flujo 2 (obtiene pending, publica en API de la red, marca published)
```

**Después de que n8n guarda el post en Laravel:** el post queda en estado `scheduled` con `publish_at`. La publicación real en Instagram/Facebook/etc. puede hacerla **Laravel** (comando `posts:publish-scheduled` cada minuto + job que llama a las APIs) o **n8n** (Flujo 2: obtener pendientes, publicar en la red, marcar como publicado). Ver Flujo 2 más abajo.

---

## Flujo 1: Generar contenido automáticamente

**Cron** → **GET content-topics (Laravel)** → **Loop** → **OpenAI** → **POST /api/posts (Laravel)**.

### Nodos

| Nodo | Configuración |
|------|----------------|
| **1. Cron** | Ejecutar cada día a las 9:00 (o el intervalo que quieras). |
| **2. HTTP Request — Obtener temas** | `GET {{BASE_URL}}/api/content-topics`, header `Authorization: Bearer {{N8N_API_KEY}}`. Respuesta: `[{ "id", "company_id", "topic" }, ...]`. |
| **3. Loop / SplitInBatches** | Iterar sobre el array de temas (cada item tiene `company_id` y `topic`). |
| **4. OpenAI** | Prompt: *"Genera un post viral para Instagram sobre {{ $json.topic }}. Incluye emojis y 10 hashtags. Máximo 120 palabras."* Entrada: el item actual del loop. |
| **5. HTTP Request — Guardar post en Laravel** | `POST {{BASE_URL}}/api/posts`. Headers: `Authorization: Bearer ...`, **`X-Company-Id`** = `{{ $json.company_id }}` (del item del loop). Body (JSON): `platform: "instagram"`, `content: {{ salida OpenAI }}`, `status: "scheduled"`, `publish_at: "2025-03-15T18:00:00"` (ej. mañana 18:00 en ISO). |

### Endpoints Laravel

- **GET /api/content-topics**  
  Con API key y sin empresa: devuelve temas de **todas** las empresas: `[{ "id", "company_id", "topic" }, ...]`. Con empresa (header X-Company-Id o usuario Sanctum): solo temas de esa empresa (mismo formato).

- **POST /api/posts**  
  Crea el post. Campos: `platform`, `content`, opcional `image_url`, `publish_at`, `status` (por defecto `draft`; para programado usa `scheduled`). Requiere header `X-Company-Id` cuando usas API key.

### Qué pasa después de guardar el post (paso 7)

El post queda en Laravel con `status: scheduled` y `publish_at`. Para **llevarlo a la red** (Instagram, Facebook, etc.) hay dos opciones:

- **Opción A — Laravel:** Ejecuta en el servidor el scheduler (ej. `php artisan schedule:work` o cron que llame `schedule:run`). Cada minuto corre el comando `posts:publish-scheduled`, que encola un job por cada post debido; el job usa `SocialPublisher` y publica en la API de la red y marca el post como `published`. No hace falta n8n para publicar.
- **Opción B — n8n (Flujo 2):** Un workflow en n8n que cada X minutos llama `GET /api/posts/pending` (por empresa, con `X-Company-Id`), publica cada post en la API de la red (Meta, Twitter, etc.) y luego llama `POST /api/posts/{id}/mark-published`. La lógica de “publicar en la red” vive en n8n; Laravel solo guarda el estado.

---

## Flujo 2: Publicación automática

**Cron** → **HTTP Request obtener pendientes** → **Switch plataforma** → **Publicar en API de la red** → **HTTP Request actualizar estado**.

### Nodos

| Nodo | Configuración |
|------|----------------|
| **1. Cron** | Cada minuto (o cada 5 min). |
| **2. HTTP Request — Obtener posts pendientes** | `GET {{BASE_URL}}/api/posts/pending` con headers `Authorization: Bearer ...` y `X-Company-Id: {id}`. Laravel devuelve `{ "posts": [ ... ] }` de esa empresa. |
| **3. Switch** | Por `post.platform`: instagram, facebook, linkedin, twitter. |
| **4. HTTP Request — Publicar en la red** | Ej. Instagram: `POST https://graph.facebook.com/v21.0/{{ page_id }}/media` (crear media) y luego `.../media_publish`. Necesitas el `access_token` de la cuenta; puedes guardarlo en Laravel en `social_accounts` y exponer un endpoint interno o tenerlo en n8n (credenciales). |
| **5. HTTP Request — Actualizar estado** | `POST {{BASE_URL}}/api/posts/{{ $json.post.id }}/mark-published` (o `/published`) con mismos headers (Bearer + X-Company-Id). |

### Endpoints Laravel

- **GET /api/posts/pending**  
  Lista de posts listos para publicar (scheduled y `publish_at` ya pasado).

- **POST /api/posts/{id}/mark-published** o **POST /api/posts/{id}/published**  
  Marca el post como `published` después de haberlo publicado en la red desde n8n.

---

## Flujo 3: Responder comentarios

**Webhook** → **OpenAI** → **HTTP Request responder comentario**.

### Opción A: Webhook en n8n (recomendado)

Quien envía el comentario al webhook puede ser:

- Un proceso que recibe el webhook de Meta (Instagram/Facebook) y reenvía a n8n, o  
- Laravel (si implementas un webhook que reciba de Meta y reenvíe a la URL de n8n).

Payload que debe recibir n8n (y que luego usa para OpenAI y Laravel): `comentario`, `usuario`, `post` (o equivalente: texto del comentario, nombre de usuario, id de post).

### Nodos

| Nodo | Configuración |
|------|----------------|
| **1. Webhook** | Recibe POST con body ej. `{ "comment": "...", "user": "...", "post_id": 1, "platform_comment_id": "123", "platform": "instagram" }`. |
| **2. (Opcional) Registrar comentario en Laravel** | `POST {{BASE_URL}}/api/comments` con headers Bearer + X-Company-Id. Body: `post_id`, `platform`, `platform_comment_id`, `username`, `text`. |
| **3. OpenAI** | Prompt: *"Responde este comentario como un community manager amable: {{ $json.comment }}"* (o `$json.text` si usas el comentario guardado). |
| **4. HTTP Request — Responder** | `POST {{BASE_URL}}/api/comments/{{ $json.comment.id }}/reply` Body: `{ "message": "{{ $json.openaiResponse }}" }`. Laravel publica la respuesta en la red (Instagram/Facebook vía Meta Graph API) y actualiza el comentario. |

### Endpoints Laravel

- **POST /api/comments**  
  Registra un comentario: `post_id`, `platform`, `platform_comment_id`, `username`, `text`. Respuesta incluye el `comment` con `id` para usarlo en reply.

- **POST /api/comments/{id}/reply**  
  Body: `{ "message": "..." }`. Laravel publica la respuesta en la red (Instagram/Facebook implementado; LinkedIn/Twitter se pueden añadir) y marca el comentario como respondido.

---

## Resumen de endpoints para n8n

| Método | Ruta | Uso |
|--------|------|-----|
| GET | /api/content-topics | Flujo 1: listar temas |
| POST | /api/posts | Flujo 1: crear post (scheduled/draft) |
| GET | /api/posts/pending | Flujo 2: posts a publicar |
| POST | /api/posts/{id}/mark-published | Flujo 2: marcar publicado |
| POST | /api/posts/{id}/published | Alias de lo anterior |
| POST | /api/comments | Flujo 3: registrar comentario |
| POST | /api/comments/{id}/reply | Flujo 3: publicar respuesta (IA) |

Base URL: tu dominio Laravel, ej. `https://tu-app.com`. Prefijo `/api`.  
- **GET /api/content-topics**: solo `Authorization: Bearer {N8N_API_KEY}`.  
- **POST /api/posts** (y resto que escriben por empresa): `Authorization` + **`X-Company-Id: {id}`** (el `company_id` del item del loop).
