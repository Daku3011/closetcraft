# ClosetCraft — Digital Wardrobe Planner

B.Tech Web Technology project covering **all syllabus units**.

## Features (project topic)
- Upload clothing images + tag colors (`upload.php` + `uploads/`)
- Filter by type, color, occasion (`closet.php?type=&color=&occasion=`)
- Random outfit generator (`generator.php` + `api.php?action=random` via AJAX)
- Save favorite outfits to a calendar (`outfits.php` → `calendar.php?m=YYYY-MM`)

## Run (needs PHP, any OS)
```bash
# install PHP (mac): brew install php
php -S localhost:8000
# open http://localhost:8000/index.php
```
No MySQL needed — app auto-creates `data/wardrobe.db` (SQLite via PDO, prepared statements).
MySQL version with **stored procedures** is in `database/closetcraft_mysql.sql`.

## Syllabus mapping (for viva / report)
| Unit | Where |
|---|---|
| 1 HTTP, Client-Server, design, navigation | `includes/header.php` nav, this README, responsive layout |
| 2 HTML tags, tables, links, images, meta, frames(iframe), forms, media, APIs, HTML5 validation | `index.php` (meta/video/iframe), `closet.php` (table), all forms use `required/pattern/accept` |
| 3 CSS: variables, gradients, animation, positioning, media queries, `*` `^` `$`, Bootstrap | `assets/css/style.css` + Bootstrap CDN |
| 4 JS: vars/arrays/functions/loops/popups/DOM/inbuilt/regex/events/callbacks/fn-args/JSON | `assets/js/app.js`, embedded script in `index.php`, internal script in `footer.php` |
| 5 PHP: vars/decision/loop, PHP+HTML, arrays/functions/string, forms, uploads, dates/timezone, regex, exceptions, session/query-string/hidden/cookies/session-vars | `includes/config.php`, `upload.php`, `closet.php`, `calendar.php` |
| 6 DB: connectivity, CRUD, prepared statements, stored procedures | `includes/db.php`, `api.php`, `database/closetcraft_mysql.sql` (`CALL GetItemsByOccasion`) |
| 7 AJAX, JQuery, trends | `api.php` (JSON) + `fetch` + JQuery live search in `app.js` |

## Demo flow for examiner
1. `upload.php` — add item with photo + color (shows validation, file upload, regex, exception, prepared insert).
2. `closet.php` — filter `?type=top&occasion=casual`, live search (JQuery), table (HTML tables), delete via query string.
3. `generator.php` — click **Generate via AJAX** (popup + JSON + callback + hidden fields), then Save with calendar date.
4. `calendar.php` — month grid (`?m=` navigation), outfits on dates. `api.php?action=stats` simulates stored-procedure output.
