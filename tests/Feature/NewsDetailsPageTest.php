<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the AdSense blocker where every article page returned
 * HTTP 500 because showNews() dereferenced a null $news.
 *
 * The lookup was scoped with ->withLocalize(), which filters on the visitor's
 * auto-detected language. A visitor detected as 'bn' opening an 'en' article
 * matched zero rows and the page crashed.
 *
 * Note: these models deliberately have no $fillable, so attributes are assigned
 * one by one, matching how the admin controllers create records.
 */
class NewsDetailsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeLanguage('English', 'en', true);
        $this->makeLanguage('Bengali', 'bn', false);
    }

    private function makeLanguage(string $name, string $lang, bool $default): Language
    {
        $language = new Language();
        $language->name = $name;
        $language->lang = $lang;
        $language->slug = $lang;
        $language->default = $default;
        $language->status = 1;
        $language->save();

        return $language;
    }

    private function makeArticle(string $language = 'en'): News
    {
        $admin = new Admin();
        $admin->image = 'uploads/avatar.png';
        $admin->name = 'Test Editor';
        $admin->email = 'editor@extremis.top';
        $admin->password = bcrypt('secret');
        $admin->status = 1;
        $admin->save();

        $category = new Category();
        $category->language = $language;
        $category->name = 'Technology';
        $category->slug = 'technology-'.$language;
        $category->show_at_nav = 1;
        $category->status = 1;
        $category->save();

        $news = new News();
        $news->language = $language;
        $news->category_id = $category->id;
        $news->author_id = $admin->id;
        $news->image = 'uploads/test.jpg';
        $news->title = 'Test Article Title';
        $news->slug = 'test-article-title';
        $news->content = str_repeat('A substantive paragraph of reporting. ', 40);
        $news->meta_title = 'Test Article Title';
        $news->meta_description = 'A test article.';
        $news->is_approved = 1;
        $news->status = 1;
        $news->save();

        return $news;
    }

    public function test_article_page_renders_when_visitor_language_matches_article(): void
    {
        $this->makeArticle('en');

        $response = $this->withSession(['language' => 'en'])
            ->get('/news-details/test-article-title');

        $response->assertStatus(200);
        $response->assertSee('Test Article Title');
    }

    public function test_article_page_renders_when_visitor_language_differs_from_article(): void
    {
        $this->makeArticle('en');

        // The visitor is auto-detected as Bengali but the article is English.
        // This is the exact case that used to produce a 500.
        $response = $this->withSession(['language' => 'bn'])
            ->get('/news-details/test-article-title');

        $response->assertStatus(200);
        $response->assertSee('Test Article Title');
    }

    public function test_unknown_slug_returns_404_not_500(): void
    {
        $response = $this->get('/news-details/this-slug-does-not-exist');

        $response->assertStatus(404);
    }

    public function test_unapproved_article_is_not_publicly_reachable(): void
    {
        $article = $this->makeArticle('en');
        $article->is_approved = 0;
        $article->status = 0;
        $article->save();

        $response = $this->get('/news-details/test-article-title');

        $response->assertStatus(404);
    }
}
