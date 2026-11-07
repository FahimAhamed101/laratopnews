# AdSense remediation — extremis.top

Why the account was not approved, what has been fixed in the code, and what you
still have to do on the server and in the database before re-applying.

---

## The two hard blockers

**1. Every article page returned HTTP 500.**
Google's reviewer clicks a headline and gets an error page, not an article. On
its own this triggers *"Low value content"* / *"site unavailable"*.

Cause: `showNews()` in `app/Http/Controllers/Frontend/HomeController.php`
dereferenced `$news->slug` without checking that `$news` existed. The lookup ran
`->withLocalize()`, which filters `language = getLanguage()`. Your site
auto-detects the visitor's language, so a visitor detected as `bn` reading an
`en` article matched zero rows and the page crashed.

**2. `APP_DEBUG=true` in production.**
Those 500 pages rendered a full Laravel debug trace to the public, including
`HomeController.php` source code, your PHP version and your Laravel version.

---

## What has been fixed in the code

| File | Change |
|---|---|
| `app/Http/Controllers/Frontend/HomeController.php` | Article lookup now falls back to any language, and `abort_if(! $news, 404)` replaces the crash. Related/next/previous queries are scoped to the article's own language. |
| `app/helpers.php` | New `normalizeStoredUrl()` helper. Rewrites dev hosts (`localhost`, `127.0.0.1`, `*.local`, `*.test`) stored in the database to the live app URL, preserving path, query and fragment. |
| `app/Providers/AppServiceProvider.php` | Forces `app.debug` off when running in production, so a bad deploy can never leak source again. Also rewrites the legacy "Daily Metro News" brand to `APP_NAME` until the database is cleaned. |
| `resources/views/frontend/layouts/footer.blade.php` | Footer grid + social URLs pass through `normalizeStoredUrl()`. |
| `resources/views/frontend/layouts/header.blade.php` | Social URLs normalised. |
| `resources/views/frontend/contact.blade.php` | Social URLs normalised; breadcrumb no longer a fake link. |
| `resources/views/frontend/home.blade.php` | Top-bar ad URL normalised. |
| `resources/views/frontend/news.blade.php` | Sidebar + news-page ad URLs normalised; author bylines null-safe; **search and tag-filtered listings set `noindex, follow`**; fixed a breadcrumb typo (`javascirt:;`) that crawled to a dead URL. |
| `resources/views/frontend/layouts/master.blade.php` | `robots` meta is now overridable per page (`@yield('robots', …)`). |
| `resources/views/errors/404.blade.php` | **New.** Branded 404 page with search and navigation, instead of a bare framework error. Sends `noindex, follow`. |
| `tests/Feature/NewsDetailsPageTest.php` | **New.** Regression tests for the article-page 500. |
| `tests/Feature/ThinContentAndErrorPagesTest.php` | **New.** Guards the noindex rules and asserts the error page leaks no debug output. |
| `resources/views/frontend/news-details.blade.php` | Ad + social-count URLs normalised; author bylines and schema.org author null-safe. |
| `resources/views/frontend/home-components/main-news.blade.php` | Ad + social URLs normalised; byline `href="javascript;"` (which crawls to a 404 at `/javascript`) replaced with plain text; author null-safe. |
| `resources/views/frontend/home-components/hero-silder.blade.php` | Same byline fix. |
| `resources/views/frontend/home-components/breaking-news.blade.php` | Author null-safe. |
| `resources/views/frontend/about.blade.php`, `terms.blade.php`, `privacy-policy.blade.php` | Breadcrumbs no longer render `href="javascript:;"`. |
| `config/ai_news.php` | Auto-generation, auto-update and **auto-delete** now default to **disabled**. Removed the hardcoded ngrok dev-tunnel API endpoint. |
| `routes/console.php` | Schedule guards default to disabled. |
| `.env.example` | Rewritten with production-safe defaults (`APP_ENV=production`, `APP_DEBUG=false`, real mail address, `ASSET_URL`, AI automation off). |
| `.env` | AI automation disabled, ngrok endpoint cleared, `ASSET_URL` added. Backup at `.env.backup-adsense-fix`. |

`php artisan view:cache` compiles all 152 Blade templates with no errors.

---

## Deploy steps

On the server, in the project root:

```bash
# 1. Pull the updated code.

# 2. Make sure production is NOT in debug mode.
#    Edit .env and confirm these three lines:
#        APP_ENV=production
#        APP_DEBUG=false
#        APP_URL=https://extremis.top
#        ASSET_URL=https://extremis.top

# 3. Rebuild caches.
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Stop the AI jobs from being scheduled.
php artisan schedule:clear-cache
```

Confirm debug is off:

```bash
curl -s https://extremis.top/news-details/does-not-exist | grep -ci "whoops\|stack trace\|APP_DEBUG"
# expected: 0
```

---

## Database fixes

Run `deploy/fix-database.sql` against the production database. It fixes the
things that live in the database rather than the code:

- "Daily Metro News" brand strings → "Extremis News"
- The `http://localhost:8000/...` footer and social URLs
- `hello@example.com` contact address

**Back up first:**

```bash
mysqldump -u USER -p extremi1_laravel123 > backup_before_adsense_fix.sql
mysql -u USER -p extremi1_laravel123 < deploy/fix-database.sql
```

The last query in that file prints a verification table — every "hits" value
should be `0` except the ones you deliberately left (e.g. placeholder social
profiles you chose to disable rather than update).

---

## Content: the part that actually decides the outcome

The technical fixes stop the site from being broken. They do not, by themselves,
satisfy Google's content policies. Two things still need your attention.

### Stop publishing unreviewed AI content

`config/ai_news.php` was generating an article **every hour** from a fixed pool
of ~35 generic topics, auto-updating them every 6 hours, and **deleting any post
that got fewer than 50 views after 30 days**. Articles were published under two
bylines and carried boilerplate like *"…was reported by the Daily Metro News desk
after reviewing…"*.

That is Google's **"scaled content abuse"** and **"low value content"** policy,
verbatim. It is the most common reason news sites fail review right now.
Deleting your own published articles also makes the site look abandoned and
breaks any inbound links.

The jobs are now off. If you want to use AI to draft, keep a human in the loop:
draft → edit → add a real source and a real byline → publish. Never publish on a
schedule with no review.

### Add substantive, recent content

Current inventory is roughly a dozen short articles, all dated June 2026 — about
three months stale as of September 2026, which reads as an abandoned site. Before
re-applying, publish a body of genuine reporting:

- Real, dated, sourced news on your actual subject matter
- Named authors with a real author page and a short bio
- **A properly filled-out About page.** This one is a hard requirement, and it
  is easy to miss: `about.blade.php` renders `{!! @$about->content !!}` straight
  from the `abouts` table. If that row is empty the page renders **blank**, which
  is an automatic failure. Fill it in at Admin → About, or with SQL:
  ```sql
  SELECT id, language, CHAR_LENGTH(content) AS len FROM abouts;
  ```
  It needs to say who runs the site, where it is based, what it covers, and how
  to reach you.
- A real postal or business address on the contact page if you have one.
  Check `contacts` too — the phone and address columns may still hold demo data.

### Review the remaining items

- `social_links` / `social_counts` still point at `facebook.com/dailymetro` and
  `youtube.com/@dailymetro`. Point them at your real profiles or disable them.
- Check the footer copyright text and the About page for leftover "Daily Metro"
  wording.
- Confirm `contact@extremis.top` (or whatever you set) actually receives mail.

---

## Re-applying

Do not re-apply immediately. Google re-reviews the live site, so the fixes have
to be deployed **and** the content problem has to be addressed first. A hasty
re-application that fails again costs you a review cycle.

Suggested order:

1. Deploy the code, run the SQL, clear caches.
2. Crawl the site yourself. Every headline must open a real article. No link may
   point at `localhost`. `view-source:` must show no stack traces.
3. Publish several weeks of genuine, human-reviewed content.
4. Confirm `https://extremis.top/ads.txt` still returns
   `google.com, pub-7830531573570855, DIRECT, f08c47fec0942fa0`.
5. Then request review.

---

## Already correct — leave alone

- `ads.txt` — valid
- AdSense verification meta tag and script in `layouts/master.blade.php`
- Google Consent Mode v2 implementation in `layouts/master.blade.php`
- Privacy policy — genuinely substantive (DART cookies, CCPA, GDPR, children's data)
- `robots.txt` — correctly allows crawling, blocks `/admin/`, `/dashboard/`, `/profile/`, `/login`, `/register`
- `sitemap.xml` and `sitemap-news.xml`
