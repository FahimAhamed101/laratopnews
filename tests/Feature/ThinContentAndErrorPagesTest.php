<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AdSense / search-quality coverage for thin listing pages and the error page.
 *
 * Internal search results and tag-filtered archives are thin, largely duplicate
 * listings and must not be indexed. Real pages must stay indexable, and a 404
 * must show a branded page rather than a bare framework error.
 */
class ThinContentAndErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_is_indexable(): void
    {
        $response = $this->get('/news');

        $response->assertStatus(200);
        $response->assertSee('name="robots" content="index, follow', false);
        $response->assertDontSee('name="robots" content="noindex', false);
    }

    public function test_search_results_are_not_indexable(): void
    {
        $response = $this->get('/news?search=anything');

        $response->assertStatus(200);
        $response->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_tag_filtered_listing_is_not_indexable(): void
    {
        $response = $this->get('/news?tag=anything');

        $response->assertStatus(200);
        $response->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_homepage_is_indexable(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('name="robots" content="index, follow', false);
    }

    public function test_unknown_page_returns_branded_404(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('We could not find that page');
        // A 404 must never be indexed.
        $response->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_error_page_does_not_leak_debug_output(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $content = $response->getContent();

        $this->assertStringNotContainsString('Whoops', $content);
        $this->assertStringNotContainsString('Stack trace', $content);
        $this->assertStringNotContainsString('vendor/laravel', $content);
        $this->assertStringNotContainsString('APP_DEBUG', $content);
    }
}
