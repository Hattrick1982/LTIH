# Foto-assessment Woonveiligheid (MVP 1)

MVP voor LangerThuisinHuis waarmee bewoners of mantelzorgers foto's uploaden en een AI-gestuurde risico-analyse ontvangen voor:

- `bathroom`
- `stairs_hall`

## Functionaliteit

- Begeleide uploadflow (`/assessment/new`) met ruimte-specifieke foto-prompts
- Uploadvalidatie (JPG/PNG, max 10MB per foto, max 5 foto's)
- Verplichte consent checkbox
- Server-side beeldoptimalisatie met `sharp`
  - EXIF metadata gestript
  - resize naar max 1600px breed
  - compressie toegepast
- AI-analyse via OpenAI Responses API + Structured Outputs (Zod)
- Resultaatpagina met:
  - risicoscore + label
  - top issues
  - aanbevolen acties
  - aanvullingsvragen
  - disclaimer
- Printbare checklist (`/assessment/result/[assessmentId]/print`)
- Verwijderknop: verwijdert assessment + tijdelijke foto's

## API endpoints

- `POST /api/assessment/upload`
- `POST /api/assessment/analyze`
- `GET /api/assessment/[assessmentId]`
- `DELETE /api/assessment/[assessmentId]`
- `POST /api/assessment/[assessmentId]/delete` (compat)

## Benodigde environment variables

- `OPENAI_API_KEY` (verplicht)
- `OPENAI_MODEL` (optioneel, default: `gpt-5.2`)
- `TEMP_STORAGE_PATH` (optioneel, default: systeem tmp map + `ltih-assessment`)

Voorbeeld `.env.local`:

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-5.2
TEMP_STORAGE_PATH=/tmp/ltih-assessment
```

## Lokaal draaien

1. Installeer dependencies:
   ```bash
   npm install
   ```
2. Start development server:
   ```bash
   npm run dev
   ```
3. Open:
   - `http://localhost:3000/assessment`

## Testen

```bash
npm run test
npm run lint
npm run build
```

## Privacy en beperking

- Geuploade foto's en analyses worden tijdelijk opgeslagen.
- Via de verwijderknop worden assessment en geassocieerde foto's verwijderd.
- De analyse is informatief en bevat geen medisch advies of diagnose.
