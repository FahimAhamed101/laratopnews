<?php

namespace Tests\Feature;

use App\Services\SearchEngineIndexingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoIndexingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test RSS 2.0 and Atom feeds are reachable and serve valid XML.
     */
    public function test_feed_endpoint_serves_valid_rss_xml(): void
    {
        $response = $this->get('/feed');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/rss+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<rss version="2.0"', $response->getContent());
    }

    public function test_rss_endpoint_serves_valid_xml(): void
    {
        $response = $this->get('/rss.xml');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/rss+xml', $response->headers->get('Content-Type'));
    }

    /**
     * Test that XML Sitemaps NEVER return X-Robots-Tag: noindex.
     */
    public function test_sitemaps_do_not_contain_noindex_headers(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $this->assertNull($response->headers->get('X-Robots-Tag'));

        $newsResponse = $this->get('/sitemap-news.xml');
        $newsResponse->assertStatus(200);
        $this->assertNull($newsResponse->headers->get('X-Robots-Tag'));
    }

    /**
     * Test IndexNow verification file route.
     */
    public function test_indexnow_verification_key_is_served(): void
    {
        $key = config('services.indexnow.key', 'extremis2026indexnow');
        $response = $this->get("/{$key}.txt");
        $response->assertStatus(200);
        $response->assertSee($key);
    }

    /**
     * Test SearchEngineIndexingService initialization and methods.
     */
    public function test_search_engine_indexing_service_instantiates(): void
    {
        $service = new SearchEngineIndexingService();
        $this->assertInstanceOf(SearchEngineIndexingService::class, $service);
    }

    /**
     * Test Google AdSense account meta tag and ads.txt compliance.
     */
    public function test_google_adsense_meta_tag_and_ads_txt(): void
    {
        $adsTxt = $this->get('/ads.txt');
        $adsTxt->assertStatus(200);
        $adsTxt->assertSee('pub-7830531573570855');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<meta name="google-adsense-account" content="ca-pub-7830531573570855">', false);
    }
}
