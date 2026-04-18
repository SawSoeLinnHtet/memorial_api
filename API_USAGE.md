# Memorial API

## Setup

```bash
cd /Users/sawsoelinnhtet/Documents/Codex/2026-04-18-please-make-laravel-api-project-which/memorial-api
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan api:key:generate frontend
php artisan serve
```

Use the generated key from the frontend as either:

```http
X-API-Key: memorial_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

or:

```http
Authorization: Bearer memorial_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

The plain key is shown once. The database stores only its SHA-256 hash.

## Google Drive

Create a Google Cloud service account, enable the Google Drive API, download the service account JSON, and share your target Drive folder with the service account email.

Add these values to `.env`:

```dotenv
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
GOOGLE_DRIVE_CREDENTIALS_PATH=/absolute/path/to/service-account.json
GOOGLE_DRIVE_MAKE_PUBLIC=true
```

You can use `GOOGLE_DRIVE_CREDENTIALS_JSON` instead of `GOOGLE_DRIVE_CREDENTIALS_PATH`, but a file path is easier locally.

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

The API uploads `image` to Google Drive, then saves only the returned URL in `featured_images.image_url`.

Example curl:

```bash
curl -X POST http://127.0.0.1:8000/api/featured-images \
  -H "X-API-Key: memorial_your_key_here" \
  -F "memory_text=A day we remember" \
  -F "collection_id=1" \
  -F "memorial_date=2026-04-18" \
  -F "image=@/absolute/path/photo.jpg"
```
