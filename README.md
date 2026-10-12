# ClosetCraft — Digital Wardrobe Planner

Catalogue your clothes, generate head-to-toe outfits, and plan them on a calendar.

## Features

- **Closet catalogue** — upload clothing photos, tag type, color, occasion, and gender (male / female / unisex)
- **Smart filters** — slice your wardrobe by type, occasion, gender, color, or text search with live filtering
- **Outfit generator** — occasion-aware shuffle that assembles a full fit (top + bottom *or* dress, plus shoes, outerwear, accessory), via AJAX or plain PHP
- **Gender-aware styling** — male fits never include dresses; female fits can style around matching dresses
- **Saved outfits** — name your favorite fits and pin them to dates
- **Calendar view** — monthly grid showing which fit goes on which day
- **Glass UI** — frosted-glass light and dark themes with a one-click toggle (preference saved in a cookie)
- **JSON API** — `api.php?action=list|random|stats` for integrations

## Tech stack

PHP 8 + PDO (SQLite out of the box, MySQL script included) · HTML5 · CSS3 · JavaScript + jQuery · Bootstrap 5 (CDN) · No build step

## Getting started

Requirements: PHP 8+ (SQLite extension enabled, standard on most installs).

```bash
cd closetCraft
php -S localhost:8000
```

Open http://localhost:8000/index.php — the SQLite database (`data/wardrobe.db`) is created automatically with demo items on first run.

### Using MySQL instead

1. Import `database/closetcraft_mysql.sql` (includes `GetItemsByOccasion` and `CountItemsByType` stored procedures).
2. Point the PDO connection in `includes/db.php` at your MySQL server.

## Usage

1. **Add Item** — upload a photo (jpg/png/webp, ≤ 2MB), set type, color, occasion, and who it's for.
2. **Closet** — filter and search; click Edit to retag anything, Delete to remove it.
3. **Generator** — pick an occasion and style (male / female / unisex), hit Shuffle it, then Save with a name and calendar date.
4. **Outfits** — open any saved fit to see every piece; **Calendar** shows fits by date.

The more items you add per occasion, the more varied the shuffles get.

## Project structure

```
index.php            home page
closet.php           catalogue + filters
upload.php           add / edit items (file uploads)
generator.php        random fit generator + save form
outfits.php          saved fits (detail view at ?view=ID)
calendar.php         monthly plan (?m=YYYY-MM)
api.php              JSON endpoints
includes/            config, PDO database layer, header/footer
assets/css|js        theme stylesheet, app scripts
uploads/             uploaded item photos
data/                SQLite database (auto-created, git-ignored)
database/            MySQL schema + stored procedures
```

## API

| Endpoint | Description |
|---|---|
| `api.php?action=list` | All closet items |
| `api.php?action=random&occasion=party&style=male` | Random fit for an occasion + style |
| `api.php?action=stats` | Item counts per type |

## License

MIT — free for personal and educational use.
