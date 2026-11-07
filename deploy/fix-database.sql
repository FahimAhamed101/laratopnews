-- ============================================================================
--  AdSense remediation - database fixes for extremis.top
-- ============================================================================
--  These are the AdSense blockers that live in the DATABASE, not in the code.
--  Run against the PRODUCTION database.
--
--  ALWAYS take a backup first:
--      mysqldump -u USER -p extremi1_laravel123 > backup_before_adsense_fix.sql
--
--  Safe to re-run: every statement is idempotent.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- 1. Brand consistency: "Daily Metro News" -> "Extremis News"
-- ----------------------------------------------------------------------------
-- The domain, logo, legal pages and AdSense account all say "Extremis News",
-- but titles and copy still carry the old "Daily Metro News" brand. A publisher
-- must present one consistent identity to pass review.

UPDATE settings
   SET value = REPLACE(value, 'Daily Metro News', 'Extremis News')
 WHERE value LIKE '%Daily Metro News%';

UPDATE settings
   SET value = REPLACE(value, 'Daily Metro', 'Extremis News')
 WHERE value LIKE '%Daily Metro%';

UPDATE footer_infos
   SET copyright = REPLACE(copyright, 'Daily Metro', 'Extremis News')
 WHERE copyright LIKE '%Daily Metro%';

UPDATE abouts
   SET content = REPLACE(content, 'Daily Metro News', 'Extremis News')
 WHERE content LIKE '%Daily Metro News%';

UPDATE abouts
   SET content = REPLACE(content, 'Daily Metro', 'Extremis News')
 WHERE content LIKE '%Daily Metro%';


-- ----------------------------------------------------------------------------
-- 2. Development URLs in production
-- ----------------------------------------------------------------------------
-- These were saved from a local dev environment and are served to visitors and
-- to Googlebot. (The application also rewrites these at render time as a safety
-- net, but the stored data should be corrected too.)

UPDATE footer_grid_ones
   SET url = REPLACE(REPLACE(url, 'http://localhost:8000', 'https://extremis.top'),
                          'http://127.0.0.1:8000', 'https://extremis.top')
 WHERE url LIKE '%localhost%' OR url LIKE '%127.0.0.1%';

UPDATE footer_grid_twos
   SET url = REPLACE(REPLACE(url, 'http://localhost:8000', 'https://extremis.top'),
                          'http://127.0.0.1:8000', 'https://extremis.top')
 WHERE url LIKE '%localhost%' OR url LIKE '%127.0.0.1%';

UPDATE footer_grid_threes
   SET url = REPLACE(REPLACE(url, 'http://localhost:8000', 'https://extremis.top'),
                          'http://127.0.0.1:8000', 'https://extremis.top')
 WHERE url LIKE '%localhost%' OR url LIKE '%127.0.0.1%';

UPDATE social_links
   SET url = REPLACE(REPLACE(url, 'http://localhost:8000', 'https://extremis.top'),
                          'http://127.0.0.1:8000', 'https://extremis.top')
 WHERE url LIKE '%localhost%' OR url LIKE '%127.0.0.1%';

UPDATE social_counts
   SET url = REPLACE(REPLACE(url, 'http://localhost:8000', 'https://extremis.top'),
                          'http://127.0.0.1:8000', 'https://extremis.top')
 WHERE url LIKE '%localhost%' OR url LIKE '%127.0.0.1%';

UPDATE ads
   SET home_top_bar_ad_url = REPLACE(home_top_bar_ad_url, 'http://localhost:8000', 'https://extremis.top')
 WHERE home_top_bar_ad_url LIKE '%localhost%';

UPDATE ads
   SET home_middle_ad_url = REPLACE(home_middle_ad_url, 'http://localhost:8000', 'https://extremis.top')
 WHERE home_middle_ad_url LIKE '%localhost%';

UPDATE ads
   SET news_page_ad_url = REPLACE(news_page_ad_url, 'http://localhost:8000', 'https://extremis.top')
 WHERE news_page_ad_url LIKE '%localhost%';

UPDATE ads
   SET side_bar_ad_url = REPLACE(side_bar_ad_url, 'http://localhost:8000', 'https://extremis.top')
 WHERE side_bar_ad_url LIKE '%localhost%';


-- ----------------------------------------------------------------------------
-- 3. Contact address
-- ----------------------------------------------------------------------------
-- A reachable address on your own domain is required. Replace the value below
-- with a mailbox that actually exists and that you monitor.

UPDATE contacts
   SET email = 'contact@extremis.top'
 WHERE email LIKE '%example.com%' OR email = '' OR email IS NULL;


-- ----------------------------------------------------------------------------
-- 4. Placeholder social profiles  -- REQUIRES YOUR INPUT
-- ----------------------------------------------------------------------------
-- These currently point at facebook.com/dailymetro and youtube.com/@dailymetro,
-- which are not your accounts. Either point them at real, active profiles for
-- this site, or remove the rows so the icons stop rendering.
--
-- Inspect first:
--     SELECT id, icon, url, status FROM social_links;
--
-- Then either update (replace with your real URLs):
--     UPDATE social_links SET url = 'https://facebook.com/YOUR_PAGE'  WHERE url LIKE '%dailymetro%';
--     UPDATE social_links SET url = 'https://youtube.com/@YOUR_CHANNEL' WHERE url LIKE '%dailymetro%';
--
-- Or disable them so they are not rendered:
--     UPDATE social_links SET status = 0 WHERE url LIKE '%dailymetro%';
--     UPDATE social_counts SET status = 0 WHERE url LIKE '%dailymetro%';


-- ----------------------------------------------------------------------------
-- 5. Verify the result
-- ----------------------------------------------------------------------------

-- Should return zero rows:
SELECT 'settings with old brand' AS check_name, COUNT(*) AS hits
  FROM settings WHERE value LIKE '%Daily Metro%'
UNION ALL
SELECT 'footer urls still on localhost', COUNT(*) FROM footer_grid_ones WHERE url LIKE '%localhost%'
UNION ALL
SELECT 'footer urls still on localhost', COUNT(*) FROM footer_grid_twos WHERE url LIKE '%localhost%'
UNION ALL
SELECT 'footer urls still on localhost', COUNT(*) FROM footer_grid_threes WHERE url LIKE '%localhost%'
UNION ALL
SELECT 'social urls still on localhost', COUNT(*) FROM social_links WHERE url LIKE '%localhost%'
UNION ALL
SELECT 'placeholder emails', COUNT(*) FROM contacts WHERE email LIKE '%example.com%';

-- Inventory of published content (AdSense wants substantive, recent content):
SELECT language, COUNT(*) AS published_articles, MIN(created_at) AS oldest, MAX(created_at) AS newest
  FROM news
 WHERE status = 1 AND is_approved = 1
 GROUP BY language;

-- The About page renders {!! $about->content !!} directly. An empty row means a
-- blank About page, which is an automatic review failure. Anything under a few
-- hundred characters needs to be rewritten.
SELECT id, language, CHAR_LENGTH(content) AS content_length FROM abouts;

-- Contact details are shown on the contact page and in the footer. Check for
-- demo/placeholder values.
SELECT id, language, address, phone, email FROM contacts;
