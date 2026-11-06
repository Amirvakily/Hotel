# 360° virtual tour – how to make it / راهنمای تور ۳۶۰

## Is a 3D room from photos possible? / آیا از روی عکس می‌شود اتاق سه‌بعدی ساخت؟

**Yes – the practical way is 360° panoramas.** A 360° photo taken from the middle of a room lets guests look around in every direction; linking several of them with hotspots ("go to the café", "back to the lobby") gives a walk-through tour. This is what the built-in tour builder does, and it runs on any shared host.

**بله – راه عملی، عکس ۳۶۰ درجه است.** یک عکس ۳۶۰ که از وسط اتاق گرفته شود، اجازه می‌دهد مهمان به همه طرف نگاه کند. با وصل کردن چند عکس به هم (نقطه‌های «رفتن به کافه»، «بازگشت به لابی») یک تور قدم‌زدنی ساخته می‌شود. ابزار تور داخل پنل همین کار را انجام می‌دهد.

| Method / روش | Equipment / وسیله | Quality / کیفیت | In this site / در این سایت |
|---|---|---|---|
| 360° camera (Insta360 X3/X4, Ricoh Theta) | دوربین ۳۶۰ | Best / بهترین | ✓ "Full 360° image" |
| Phone 360 app (e.g. *Panorama 360*, *Teleport*) | گوشی | Good / خوب | ✓ "Full 360° image" |
| Phone **Panorama** mode (wide strip) | گوشی | Partial / جزئی | ✓ "Partial panorama" – set the horizontal angle |
| Matterport / Kuula / Polycam / Luma 3D scan | Their app or camera | Real 3D model | ✓ "External 3D model" – paste the embed link |
| True 3D model from ordinary photos (photogrammetry) | Many photos + a powerful computer | Hard / سخت | Use Polycam/Luma, then embed |

## Shooting tips / نکات عکاسی

1. Put the camera on a tripod at **1.4–1.6 m**, in the **middle** of the space. / دوربین روی سه‌پایه، ارتفاع ۱٫۴ تا ۱٫۶ متر، وسط فضا.
2. Turn on **all lights**, open curtains, tidy the room. / همه چراغ‌ها روشن، پرده‌ها باز.
3. Step out of the shot (use the app's timer or remote). / خودتان در عکس نباشید (تایمر یا ریموت).
4. Export as **equirectangular JPG with a 2:1 ratio** (e.g. 5760×2880). / خروجی JPG با نسبت ۲ به ۱.
5. Shoot one photo per space: lobby, each room type, breakfast hall, café, corridor. / از هر فضا یک عکس.

## Adding it to the site / افزودن به سایت

1. Admin → **360° tour** → **Add new**. / پنل ← تور ۳۶۰ ← افزودن.
2. Enter a title, choose the type and pick the image (large images are resized in your browser before upload). / عنوان، نوع و تصویر را انتخاب کنید.
3. Open the **View & hotspots** tab: / تب «نما و نقاط تور»:
   - Turn the image to the view guests should see first → **Use this view as the start view**. / نمای شروع.
   - Turn until the **+** sits on a door → **Hotspot to another scene** → choose the target scene and type a label in each language. / نقطه رفتن به صحنه دیگر.
   - **Info hotspot** adds a small "i" with text (e.g. "Window facing Mount Eynali"). / نقطه توضیح.
4. Save. Tick **Tour starts here** on the scene the tour should open with. / ذخیره؛ صحنه شروع را تیک بزنید.
5. To show a 360° view on a room page: Rooms → edit room → **Photos & 360°** → choose the scene. / برای نمایش در صفحه اتاق، در ویرایش اتاق صحنه را انتخاب کنید.

On phones, guests can tap **Move with your phone** to look around by turning the phone (needs HTTPS).
در گوشی، مهمان با دکمه «حرکت با گوشی» و چرخاندن گوشی اطراف را می‌بیند (نیاز به HTTPS).

## Turning the tour off / خاموش کردن تور

Admin → **360° tour** → switch **Show the 360° tour on the website** off (administrators only). The tour then disappears everywhere: the menu link, the button on the home-page photo, the home-page tour section, the 360° view and badge on room pages, the tour page itself (it returns “not found”) and the sitemap. Your scenes are kept; switch it on again to bring everything back. With no active 360° photo the tour stays hidden on its own.

پنل ← **تور ۳۶۰ درجه** ← کلید «نمایش تور ۳۶۰ در سایت» را خاموش کنید (فقط مدیر کل). تور از همه جای سایت حذف می‌شود: منو، دکمه روی عکس صفحه اصلی، بخش تور صفحه اصلی، نمای ۳۶۰ و نشان ۳۶۰ اتاق‌ها، صفحه تور و نقشه سایت. صحنه‌ها پاک نمی‌شوند و با روشن کردن برمی‌گردند. اگر هیچ تصویر ۳۶۰ فعالی نباشد، تور خودبه‌خود پنهان می‌ماند.

The demo tour that ships with the site was rendered from 3D models, so replace it with real photos of the hotel.
تور نمونه از مدل سه‌بعدی ساخته شده؛ آن را با عکس‌های واقعی هتل جایگزین کنید.
