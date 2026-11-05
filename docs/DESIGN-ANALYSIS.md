# Design analysis: what good hotel websites do, and what Venus Hotel uses

This note explains the design decisions behind the Venus Hotel (هتل ونوس) website.
It compares international premium hotel sites with Iranian hotel sites, then lists
what we adopted, what we changed, and what we left out.

## 1. Sites reviewed

| Group | Sites | Why they matter |
|---|---|---|
| Ultra-luxury | Aman, Rosewood, Four Seasons, Burj Al Arab | Set the visual language of "premium": large photos, calm pacing, refined type |
| Design / lifestyle | citizenM, Ace Hotel, The Hoxton | Show how a mid-price hotel can feel premium: clear info, personality, fast pages |
| Iranian | Espinas Palace (Tehran), Parsian Azadi, Shah Abbasi (Isfahan), Pars El Goli (Tabriz) | Local habits: Persian first, prices in Toman, phone/WhatsApp booking, Persian digits |
| Roundups | Krisha Web "13 Best Hotel Website Designs 2026", RMS "Hotel website examples" (Feb 2026), Colorlib hotel websites list, DesignRush luxury list | Current trends: accessibility, authentic local storytelling, pacing that matches the brand |

## 2. Common patterns on premium hotel sites

1. **Full-screen hero** with one strong photo or a slow slideshow, the hotel name, and one or two
   clear actions. Text is short. Motion is slow (fade, gentle zoom), never flashy.
2. **Header that gets out of the way**: transparent over the hero, solid after scrolling,
   always one tap from "Rooms" and "Contact".
3. **Rooms shown as products**: big photo, size in m², bed type, max guests, view, key amenities,
   and a "from" price. Four Seasons and Rosewood add a compare view; citizenM leads with the price.
4. **A sense of place**: Aman and Rosewood spend as much space on the city and culture as on the
   building. RMS (2026) notes authentic local storytelling works better than badges.
5. **Separate pages for each food and drink venue** (restaurant, café, bar) with hours, a short
   menu and their own photos.
6. **Gallery by category** (rooms, lobby, dining, exterior) with a full-screen viewer.
7. **Virtual tours**: more and more hotels embed 360° views (Matterport, Kuula or a custom viewer).
8. **Location**: map, directions, distances to landmarks and the airport.
9. **Reviews and trust signals**: star rating, guest quotes, licenses.
10. **FAQ and policies**: check-in/out times, children, pets, cancellations.
11. **Performance and accessibility**: fast first paint, readable contrast, keyboard friendly,
    reduced-motion support (highlighted as a 2026 trend in the RMS roundup).

## 3. What Iranian hotel sites do differently

- **Persian first, right-to-left**, with English (and in Tabriz and Mashhad, Arabic) as extras.
- **Prices in Toman**, often a full price table, sometimes "approved rate" vs. "hotel rate".
- **Booking happens by phone, WhatsApp or Telegram**, not a booking engine. Phone numbers are
  large and click-to-call on mobile.
- **Most traffic is mobile**, often on slow connections. Heavy sliders and Google-hosted fonts or
  maps can be slow or blocked, so everything should be self-hosted.
- **Local map apps** (Neshan, Balad) are used more than Google Maps.
- **Persian digits and Jalali dates** are expected.

Common weaknesses we avoided: cluttered home pages, low-resolution sliders, prices that are
hard to read, broken English pages, no clear way to see what differs between similar rooms.

## 4. What Venus Hotel adopts

| Decision | Reason |
|---|---|
| Persian default, English and Arabic (Iraqi-friendly wording) switch; full RTL/LTR | Tabriz receives many Iraqi visitors |
| Modern design system: warm off-white base, graphite, one teal accent (a quiet nod to the Blue Mosque tiles), bold Vazirmatn / Plus Jakarta Sans type, large rounded cards, glass surfaces | Feels current and premium without ornament; switchable colour presets (incl. lapis & gold) in the admin |
| Floating “island” header, inset rounded hero, bento grids, overlay image cards, carousels, scrolling amenities bar, big-wordmark footer | Patterns used by today’s design-led hotels (citizenM, Ace, Hoxton) |
| A geometric eight-pointed star (Venus, the morning star) as the logo mark | A recognisable signature that still reads as modern |
| Hero slideshow with slow zoom, three stars, and a **guest-count bar** (“How many guests? → See rooms & prices”) | Info first: the price question is answered in one tap |
| **Guest-count selector on the rooms page**: pick the number of guests and every price updates; rooms too small are dimmed | The price depends on the number of guests |
| **Price table** (all rooms × all guest counts) and **compare view** | Hotels in Iran are expected to show rates; guests can compare at a glance |
| **Triple-room variants told apart** with a coloured badge, bed-layout icons (3 singles vs. double + single), size, view and a "what's different" line | There are three kinds of 3-bed room |
| Prices optional per guest count; when empty, the price is hidden or shows "Call for price" | Some rates are not published |
| Extra people per room (e.g. 4 guests in a triple) priced as the full-room price + a per-person charge; children under 5 free; a “7+” group option | Matches how the hotel actually sells rooms |
| **Price presets** (e.g. “Nowruz”, “Normal season”) applied to every room in one tap, plus a bulk ± % tool | Seasonal price changes take seconds |
| WhatsApp/call buttons with a pre-filled message ("Deluxe Triple, 3 guests") instead of a booking engine | The site is for showing the hotel, not reservations |
| Breakfast (free) and Café as separate sections and pages, with their own menus and photos | They are different spaces |
| Gallery with categories, lightbox, swipe | Show every part of the hotel |
| 360° virtual tour with linked scenes and hotspots, plus per-room 360 views | Lets guests "walk" the hotel |
| Map with a location picked in the admin, plus Neshan, Balad, Google Maps and Waze buttons | Matches how people navigate in Iran |
| Tabriz guide: bazaar, Blue Mosque, El Goli, Arg, Kandovan, with distances | Sense of place |
| Self-hosted fonts and libraries, responsive WebP images, page cache | Fast in Iran, no blocked CDNs |
| Reduced-motion support, keyboard navigation, alt text fields | Accessibility |

## 5. What we left out (on purpose)

- Online booking engine and payment (not needed; contact buttons instead).
- Autoplay video with sound, pop-ups, chat widgets from third parties.
- Third-party analytics by default (a private, built-in visit counter is included; custom code can
  be added in the admin if wanted).

## 6. About 3D rooms from photos

| Method | What you need | Works here? |
|---|---|---|
| **360° panorama (equirectangular, 2:1)** | A 360 camera (Insta360, Ricoh Theta) or a phone 360 app | **Yes** – the built-in tour builder uses it. Best quality-to-effort ratio |
| Phone "Panorama" mode (partial, wide strip) | Any phone | **Yes** – supported as a partial panorama (set the horizontal angle in the admin) |
| Matterport / Kuula / Polycam / Luma 3D scans | Their app or a scanning camera; hosted by them | **Yes** – paste the embed link as an "external 3D" scene |
| Building a real 3D model from normal photos (photogrammetry, Gaussian splats) | Many photos + GPU processing | Not on shared hosting; use one of the services above and embed it |

See [360-TOUR-GUIDE.md](360-TOUR-GUIDE.md) for how to shoot and upload panoramas.

## Sources

- Krisha Web, "13 Best Hotel Website Designs You Will Love in 2026": https://www.krishaweb.com/blog/best-hotel-website-designs/
- RMS Cloud, "Hotel website examples" (13 Feb 2026): https://www.rmscloud.com/blog/hotel-website-examples
- Colorlib, "Hotel websites": https://colorlib.com/wp/hotel-websites/
- DesignRush, "Best luxury website designs": https://www.designrush.com/best-designs/websites/trends/best-luxury-website-designs
- Website Builder Expert, "Best Hotel Website Design": https://www.websitebuilderexpert.com/designing-websites/hotel-website-design/
- Coax Soft, "Best hotel website design examples": https://coaxsoft.com/blog/best-hotel-website-design-examples
- Wikipedia, "Pars El-Gölü Hotel": https://en.wikipedia.org/wiki/Pars_El-G%C3%B6l%C3%BC_Hotel
