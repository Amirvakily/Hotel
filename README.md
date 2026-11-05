# Venus Hotel · هتل ونوس

Website and admin panel for **Venus Hotel**, a 3-star hotel in Tabriz, Iran, with **Truth Café** (کافه تروث) and free breakfast.
Persian first (RTL), with English and Iraqi-friendly Arabic. Built to *show* the hotel – rooms, prices, photos and a 360° tour – with call / WhatsApp buttons instead of online booking.

- **Runs on cPanel shared hosting**: plain PHP 8, no Composer, no Node, SQLite by default (MySQL optional).
- **Install guides:** [INSTALL.fa.md](INSTALL.fa.md) (فارسی) · [INSTALL.md](INSTALL.md) (English)
- **Admin guide (فارسی):** [docs/ADMIN-GUIDE.fa.md](docs/ADMIN-GUIDE.fa.md)
- **360° tour guide:** [docs/360-TOUR-GUIDE.md](docs/360-TOUR-GUIDE.md)
- **Design research:** [docs/DESIGN-ANALYSIS.md](docs/DESIGN-ANALYSIS.md)

## Features

**Public site**
- Modern design: floating glass header, inset hero slideshow with a guest-count bar, bento grids, carousels, overlay cards, scrolling amenities bar, page transitions, scroll reveals, parallax; respects *reduced motion*.
- Rooms with **price by number of guests**, extra people (e.g. 4 guests in a triple at the 3-guest price + a per-person charge), children-under-5-free counter, a "7+" group option, and "Call for price" when a price is empty.
- Three triple-room types told apart by colour badge, a drawn **bed plan**, size, view, "what's different" line, and a **compare table**; full **price table** view.
- Room pages with photo grid + lightbox, sticky price card, WhatsApp message pre-filled with room and guests, 360° view.
- **360° virtual tour** (Pannellum) with linked scenes, hotspots, auto-rotate, gyroscope, full screen; external 3D models (Matterport/Kuula/Polycam) can be embedded.
- Gallery with categories + swipeable lightbox; breakfast and café pages with menus; Tabriz guide with distances; FAQ; reviews; contact form (spam-protected) → admin inbox.
- Map (Leaflet + OpenStreetMap) with **Neshan, Balad, Google Maps and Waze** buttons.
- Persian / Jalali dates and digits, Arabic-Indic digits for Arabic, approximate prices in USD / Iraqi dinar.
- SEO: hreflang, sitemap.xml, robots.txt, Open Graph, schema.org `Hotel`.
- Fast: responsive WebP images with blurred placeholders, lazy loading, self-hosted fonts and libraries (nothing from blocked CDNs), full-page cache.

**Admin panel** (`/admin`, works on phones)
- Rooms, amenities, gallery (drag-and-drop bulk upload), hero slides, home-page sections (reorder / show / hide / edit), café & breakfast menus, sights, reviews, FAQ, custom pages.
- **Prices & presets**: edit every room price in one grid, bulk ± % with rounding, save named presets ("Nowruz") and apply them to all rooms in one tap.
- **360° tour builder**: upload panoramas (resized in the browser), set the start view, place hotspots by aiming, link scenes, preview live.
- Map picker (click, drag, or paste a Google Maps / Neshan / Balad link), media library with alt text, messages inbox with CSV export.
- Settings for contact, social, prices/currencies, colours (5 presets), languages, menu order, page texts, SEO and custom code, maintenance mode.
- Edit any interface text in all three languages; users with *administrator* / *editor* roles; one-click backup & restore (zip).
- Security: hashed passwords, login throttling, CSRF tokens, re-encoded uploads, no PHP in `uploads/`, private `app/` and `storage/`.

## Project layout

```
public/            ← upload the contents of this folder to public_html
  index.php        front controller (site + /admin)
  install/         web installer (locks itself after use)
  app/             PHP code (lib/, controllers/, views/, lang/, seed/) – blocked from the web
  assets/          css, js, fonts, icons, vendor (Pannellum, Leaflet, Sortable)
  storage/         config, SQLite database, cache, logs, backups – blocked from the web
  uploads/         photos and panoramas
release/           ready-to-upload cPanel zip (venus-hotel-1.0.0.zip)
scripts/           build-release.sh (cPanel zip), minify.sh (CSS/JS), install-vps.sh, nginx.conf, dev-server.sh
tests/             Playwright smoke + end-to-end tests
tools/             dev-only generators: demo 3D renders + 360 panoramas, sight illustrations, icon sprite
```

## Quick start (local)

```bash
php public/app/cli.php install --user=admin --pass=venus12345   # add --no-demo for an empty site
bash scripts/dev-server.sh                                      # http://localhost:8000  ·  /admin
```

## Editing CSS / JS

The site serves `site.min.css` / `site.min.js` (and `admin.min.*`) only while they match their source files, so edits to `site.css` or `site.js` show up at once. Run `bash scripts/minify.sh` (needs Node.js) to refresh the minified copies, and `bash scripts/build-release.sh` to rebuild the zip in `release/`.

## Tests

```bash
tests/reset-install.sh                       # fresh demo install (local only)
bash scripts/dev-server.sh &                 # PORT=8080 for the commands below
node tests/admin-smoke.cjs http://127.0.0.1:8080 admin venus12345
node tests/admin-e2e.cjs   http://127.0.0.1:8080 admin venus12345 public/app/seed/media/cafe.jpg
```

## Demo content

All demo prices, phone numbers and the address are placeholders – change them in the admin panel.
The demo photos and 360° panoramas are **3D renders** made with `tools/render` (three.js), and the Tabriz sights are illustrations from `tools/posters.mjs`; replace them with real photos of the hotel.

## Credits & licences

Vazirmatn (OFL) · Plus Jakarta Sans (OFL) · Lucide icons (ISC) · Pannellum (MIT) · Leaflet (BSD-2) · SortableJS (MIT) · map data © OpenStreetMap contributors.
