# News Aggregator Backend

A Laravel 12 backend for aggregating news articles.

## Requirements
- Docker and Docker Compose

## Quick Start (Docker)
1. Copy `env.example` to `.env` and adjust values if needed.
2. Start the stack:
   - `docker compose up -d`
3. Generate app key and run migrations inside the app container:
   - `docker exec -it news_scrapper_app bash -lc "php artisan key:generate && php artisan migrate --force"`
4. Open the API at `http://localhost:8000/api`.

### Default MySQL credentials
- Host: `db`
- Port: `3306`
- Database: `news_scrapper`
- User: `news`
- Password: `news_password`

These match the values in `docker-compose.yml` and `env.example`.

## Postman Collection
- Shared collection: https://winter-rocket-321698.postman.co/workspace/Personal-Workspace~e263e835-3272-43bf-9234-f809bf828045/collection/19468282-4126de83-2bee-479b-9b39-299b7c126511?action=share&source=copy-link&creator=19468282
- Import `postman_collection.json` if you prefer a local copy.

## Authentication
- Register or login to obtain a token.
- The collection auto-sets `Authorization: Bearer {{token}}` after login.

## Testing
- Tests use the default Laravel testing setup.
- Run: `php artisan test` or `composer test`
