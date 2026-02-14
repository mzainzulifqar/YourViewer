# Open Analytics

A self-hosted Google Analytics (GA4) dashboard built with Laravel 12, Tailwind CSS 4, Chart.js, and Alpine.js.

## Features

- **Property Selector** — Lists all GA4 properties under a service account, with search
- **Dashboard** — 3-column layout with:
  - Overview stats (sessions, users, pageviews, bounce rate, etc.) with period comparison
  - Interactive line chart (sessions/users/pageviews tabs)
  - Top pages, traffic sources, device breakdown, geography, events
  - AI-generated insight banners (warning, success, opportunity, info)
  - 11 date range presets (today, yesterday, 7/14/30/90 days, 6/12 months, this/last month, this year)
- **Pages & Screens** — Detailed drill-down page with multi-line area chart (top 10 pages, toggleable) and searchable/sortable/paginated table
- **REST API** — 10 JSON endpoints covering all dashboard data
- **Swagger Docs** — Classic Swagger UI at `/docs/api` with full parameter enums, response schemas, and Try It Out
- **Caching** — All GA4 API calls cached (15-30 min) to stay within quotas

## Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- A Google Cloud service account with GA4 Data API and Admin API enabled

## Setup

### 1. Clone & install

```bash
git clone <repo-url> open-analytics
cd open-analytics
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Google Cloud credentials

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project (or select an existing one)
3. Enable the **Google Analytics Data API** and **Google Analytics Admin API**
4. Go to **IAM & Admin > Service Accounts** and create a service account
5. Create a JSON key for the service account and download it
6. Place the JSON key file in `storage/app/` (e.g. `storage/app/service-account.json`)
7. In your GA4 property, go to **Admin > Property Access Management** and add the service account email as a Viewer

### 3. Configure environment

Update your `.env` file:

```env
GOOGLE_APPLICATION_CREDENTIALS=storage/app/service-account.json
```

### 4. Database & assets

```bash
php artisan migrate
npm install && npm run build
```

## Configuration

Authentication is handled by Laravel Breeze (email/password login and registration).

## Running

```bash
# Development (serves app + queue + logs + vite in parallel)
composer dev

# Or just the server
php artisan serve
```

## Routes

### Web

| Route | Description |
|---|---|
| `/` | Property selector (lists all GA4 properties) |
| `/analytics/{propertyId}` | Analytics dashboard for a property |
| `/analytics/{propertyId}/pages` | Pages & Screens detail page |
| `/login` | Login page |
| `/docs/api` | Swagger API documentation |

### API

All API endpoints require authentication via `Authorization: Bearer <token>` or `X-Api-Key: <key>` header.

| Endpoint | Description |
|---|---|
| `GET /api/analytics/properties` | List all GA4 properties |
| `GET /api/analytics/{id}/dashboard` | Full dashboard payload (all data in one call) |
| `GET /api/analytics/{id}/overview` | Overview stats with period comparison |
| `GET /api/analytics/{id}/top-pages` | Top pages by pageviews |
| `GET /api/analytics/{id}/traffic-sources` | Traffic sources breakdown |
| `GET /api/analytics/{id}/devices` | Device category breakdown |
| `GET /api/analytics/{id}/geography` | Geography by country |
| `GET /api/analytics/{id}/events` | Events breakdown |
| `GET /api/analytics/{id}/pages` | Detailed pages & screens (table + chart) |
| `GET /api/analytics/{id}/insights` | AI-generated insights |

All endpoints accept an optional `?range=` query parameter:

`today`, `yesterday`, `7days`, `14days`, `30days` (default), `90days`, `6months`, `12months`, `this_month`, `last_month`, `this_year`

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Tailwind CSS 4, Alpine.js 3, Chart.js 4
- **Google APIs**: `google/analytics-data` (GA4 Data API v1beta), `google/analytics-admin` (Admin API v1beta)
- **API Docs**: `darkaonline/l5-swagger` (Swagger UI + swagger-php)

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -am 'Add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

## License

[MIT](LICENSE)
