# ClosetCraft — Digital Wardrobe Planner

Catalogue your clothes, generate head-to-toe outfits, and plan them on a calendar. Private to your account.

## Features

- **Accounts** — sign up, log in, log out (hashed passwords); every user only ever sees their own clothes and fits
- **Closet catalogue** — upload clothing photos, tag type, color, occasion, and gender (male / female / unisex)
- **Smart filters** — by type, occasion, gender, color, or live text search
- **Outfit generator** — occasion-aware shuffle assembling a full fit: top + bottom *or* a dress, plus shoes, outerwear, accessory — via AJAX or plain PHP
- **Gender-aware styling** — pick male / female / unisex per shuffle; male fits never include dresses, and female-tagged pieces are skipped for male fits (and vice versa)
- **Saved outfits** — name favorite fits and pin them to calendar dates
- **Calendar view** — monthly grid (`?m=YYYY-MM`) showing which fit goes on which day
- **Glass UI** — frosted-glass light and dark themes with a one-click toggle (saved in a cookie)
- **Offline-safe images** — placeholder tiles are generated in-app (no external image service), so they render in both themes with or without internet
- **JSON API** — `api.php?action=list|random|stats` (login required, scoped to your account)

## Tech stack

PHP 8 + PDO (SQLite out of the box, MySQL script included) · HTML5 · CSS3 (custom glass theme) · JavaScript + jQuery · Bootstrap 5 (CDN) · No build step

## Getting started

Requirements: PHP 8+ with the SQLite extension (standard on most installs).

```bash
cd closetCraft
php -S localhost:8000
```

Open http://localhost:8000/ — the SQLite database (`data/wardrobe.db`) is created automatically with demo items on first run. Create an account; the first account on a fresh install inherits the demo items.

### Using MySQL instead

1. Import `database/closetcraft_mysql.sql` (includes `GetItemsByOccasion` and `CountItemsByType` stored procedures).
2. Point the PDO connection in `includes/db.php` at your MySQL server.

### Deploying to Render

The repo ships a `Dockerfile`, `.htaccess`, and `render.yaml` (Docker runtime — Render has no native PHP). Deploy via **New → Blueprint**, no build/start commands needed. Note: the free tier has no persistent disk, so uploads and DB changes made on the live site reset on redeploy — treat your local copy as the source of truth.

## Usage

1. **Sign up / Log in** — your wardrobe is private to your account.
2. **Add Item** — upload a photo (jpg/png/webp, ≤ 2MB), set type, color, occasion, and who it's for.
3. **Closet** — filter and search; Edit to retag anything, Delete to remove it.
4. **Generator** — pick an occasion and style (male / female / unisex), hit Shuffle it, then Save with a name and calendar date.
5. **Outfits** — open any saved fit to see every piece; **Calendar** shows fits by date.

The more items you add per occasion, the more varied the shuffles get.

## Project structure

```
index.php            landing + personal stats
signup.php           account creation
login.php / logout.php  session auth
closet.php           catalogue + filters
upload.php           add / edit items (file uploads)
generator.php        random fit generator + save form
outfits.php          saved fits (detail view at ?view=ID)
calendar.php         monthly plan (?m=YYYY-MM)
api.php              JSON endpoints (auth required)
includes/            config, PDO database layer, header/footer
assets/css|js        glass theme stylesheet, app scripts
uploads/             uploaded item photos
data/                SQLite database (auto-created)
database/            MySQL schema + stored procedures
Dockerfile           Render/Docker deploy
render.yaml          Render blueprint
```

## API

All endpoints require login (401 otherwise) and return only the caller's data.

| Endpoint | Description |
|---|---|
| `api.php?action=list` | All of your closet items |
| `api.php?action=random&occasion=party&style=male` | Random fit for an occasion + style |
| `api.php?action=stats` | Your item counts per type |

## License

MIT — free for personal and educational use.
