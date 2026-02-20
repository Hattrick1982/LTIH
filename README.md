# LangerThuisinHuis Foto-assessment - PHP Rewrite

Deze repository bevat nu een volledige PHP-versie van de foto-assessment app in `/Users/patrickvannuland/Documents/LTIH/Risico PHP/php-app`.

De Node.js/Next.js implementatie blijft tijdelijk in de repo als referentie tijdens migratie, maar productie draait zonder Node runtime.

## Gekozen architectuur

Gekozen optie: **C) Plain PHP met micro-architectuur**.

Korte motivatie:
- De huidige app is functioneel compact (ongeveer 2.5k LOC, geen relationele database, tijdelijke storage).
- Een lichte router + servicelaag in PHP levert snelle, transparante parity zonder framework-overhead.
- Hierdoor is productie volledig Node-vrij en blijft route-compatibiliteit direct beheersbaar.

## Functional parity (routes)

Web:
- `/`
- `/assessment`
- `/assessment/new?room=...`
- `/assessment/result/{assessmentId}`
- `/assessment/result/{assessmentId}/print`
- `/woonkamer`
- `/slaapkamer`
- `/keuken`
- `/contact`

API:
- `POST /api/assessment/upload`
- `POST /api/assessment/analyze`
- `GET /api/assessment/{assessmentId}`
- `DELETE /api/assessment/{assessmentId}`
- `POST /api/assessment/{assessmentId}/delete` (compat)
- `GET /api/assessment/{assessmentId}/checklist.pdf` (redirect naar print-view fallback)

## Belangrijkste features

- Nederlands UI en flow behouden (Start, Ruimtes, Upload, Analyse, Resultaat, Print, Adviesgesprek).
- Upload validatie: JPG/PNG, max 10MB, max 5 bestanden.
- Image processing in PHP GD:
  - EXIF metadata gestript door re-encoding.
  - resize max breedte 1600px.
  - compressie (JPEG kwaliteit 82, PNG compressie 9).
- Tijdelijke opslag in `TEMP_STORAGE_PATH`.
- Delete endpoint verwijdert assessment + gekoppelde uploads.
- Cleanup script voor TTL cleanup.
- OpenAI Responses API integratie met structured JSON schema-validatie.
- Accessibility toggle (`Tekst` / `A+`) via localStorage.

## Vereisten

- PHP `8.2+`
- Extensies:
  - `curl`
  - `fileinfo`
  - `gd`
  - `json`
  - `exif` (aanbevolen voor oriëntatiecorrectie)
- Composer (voor autoload + PHPUnit)

## Installatie lokaal

```bash
cd /Users/patrickvannuland/Documents/LTIH/Risico\ PHP/php-app
cp .env.example .env
composer install
php -S 127.0.0.1:8080 -t public
```

Open:
- [http://127.0.0.1:8080/assessment](http://127.0.0.1:8080/assessment)

## Environment variabelen

`/Users/patrickvannuland/Documents/LTIH/Risico PHP/php-app/.env`

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-5.2
MAIN_SITE_URL=https://langerthuisinhuis.nl
TEMP_STORAGE_PATH=/tmp/ltih-assessment
ASSESSMENT_TTL_HOURS=24
OPENAI_TIMEOUT_SECONDS=45
OPENAI_RETRIES=2
```

## Cleanup (TTL)

```bash
cd /Users/patrickvannuland/Documents/LTIH/Risico\ PHP/php-app
php bin/assessment-cleanup.php
```

Cron voorbeeld (elk uur):

```cron
0 * * * * cd /Users/patrickvannuland/Documents/LTIH/Risico\ PHP/php-app && /usr/bin/php bin/assessment-cleanup.php >> /tmp/assessment-cleanup.log 2>&1
```

## Tests

```bash
cd /Users/patrickvannuland/Documents/LTIH/Risico\ PHP/php-app
composer test
```

Tests dekken minimaal:
- schema validation
- upload validation (type/size)
- analyze endpoint met mocked analyzer

## Docker (php-fpm + nginx)

```bash
cd /Users/patrickvannuland/Documents/LTIH/Risico\ PHP/php-app
docker build -t ltih-assessment-php .
docker run --rm -p 8080:8080 --env-file .env ltih-assessment-php
```

## Productie-notes

- Geen secrets in git.
- Gebruik alleen environment variabelen voor keys/tokens.
- Geef `storage` schrijfrechten aan de runtime-user (`www-data` in Docker).
- Node runtime is niet nodig voor productie van deze PHP rewrite.
