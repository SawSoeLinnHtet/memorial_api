# Memorial API

## Setup

```bash
cd /Users/sawsoelinnhtet/Documents/Codex/2026-04-18-please-make-laravel-api-project-which/memorial-api
php artisan key:generate
php artisan migrate
php artisan api:key:generate
php artisan serve
```

The command saves one key to `.env`:

```dotenv
FRONTEND_API_KEY=memorial_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Use that key from the frontend as either:

```http
X-API-Key: memorial_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

or:

```http
Authorization: Bearer memorial_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Run `php artisan api:key:generate` only when you want to replace the frontend key.

## File Storage

Uploaded photos are stored in Laravel public storage under:

```text
storage/app/public/featured-images
```

Run this once so public URLs work:

```bash
php artisan storage:link
```

## Endpoints

All endpoints are under `/api` and require the API key header.

### Collections

```http
GET /api/collections
POST /api/collections
GET /api/collections/{id}
PUT /api/collections/{id}
DELETE /api/collections/{id}
```

POST or PUT JSON:

```json
{
  "description": "Family memories"
}
```

### Featured Images

```http
GET /api/featured-images
GET /api/featured-images?collection_id=1
POST /api/featured-images
GET /api/featured-images/{id}
POST /api/featured-images/{id}?_method=PUT
DELETE /api/featured-images/{id}
```

Create with multipart form data:

```text
memory_text=A day we remember
collection_id=1
image=@/path/to/photo.jpg
memorial_date=2026-04-18
```

The API stores `image` in Laravel public storage, then saves only the public URL in `featured_images.image_url`.

Example curl:

```bash
curl -X POST http://127.0.0.1:8000/api/featured-images \
  -H "X-API-Key: memorial_your_key_here" \
  -F "memory_text=A day we remember" \
  -F "collection_id=1" \
  -F "memorial_date=2026-04-18" \
  -F "image=@/absolute/path/photo.jpg"
```
