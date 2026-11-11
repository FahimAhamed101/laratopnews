<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Generate the standard XML Sitemap for search engines (Google, Bing, etc.).
     */
    public function index(): Response
    {
        $siteName = config('app.name', 'Extremis News');
        $baseUrl = url('/');

        // Static routes
        $staticUrls = [
            [
                'loc' => $baseUrl,
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'hourly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('news'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'hourly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('about'),
                'lastmod' => now()->subDays(7)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('contact'),
                'lastmod' => now()->subDays(7)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('privacy-policy'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('terms-of-service'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ];

        // Categories
        $categories = Category::where('status', 1)->get();
        $categoryUrls = [];
        foreach ($categories as $cat) {
            $categoryUrls[] = [
                'loc' => route('news', ['category' => $cat->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        // Active Authors (Journalists / Writers)
        $authors = \App\Models\Admin::where('status', 1)->get();
        $authorUrls = [];
        foreach ($authors as $auth) {
            $authorUrls[] = [
                'loc' => route('author.profile', ['id' => $auth->id, 'slug' => Str::slug($auth->name)]),
                'lastmod' => ($auth->updated_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Active News Posts
        $newsPosts = News::where('status', 1)
            ->where('is_approved', 1)
            ->orderBy('updated_at', 'DESC')
            ->take(2000)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
            . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Add static URLs
        foreach ($staticUrls as $item) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($item['loc']) . "</loc>\n";
            $xml .= "    <lastmod>" . $item['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // Add Category URLs
        foreach ($categoryUrls as $item) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($item['loc']) . "</loc>\n";
            $xml .= "    <lastmod>" . $item['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // Add Author Profile URLs
        foreach ($authorUrls as $item) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($item['loc']) . "</loc>\n";
            $xml .= "    <lastmod>" . $item['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // Add News URLs with Images
        foreach ($newsPosts as $post) {
            $postUrl = route('news-details', $post->slug);
            $lastMod = ($post->updated_at ?? $post->created_at ?? now())->toAtomString();

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($postUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastMod . "</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.9</priority>\n";

            if (!empty($post->image)) {
                $imageUrl = asset($post->image);
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($post->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                $xml .= "      <image:caption>" . htmlspecialchars(Str::limit($post->meta_description ?? $post->title, 150), ENT_XML1, 'UTF-8') . "</image:caption>\n";
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Generate Google News-specific XML Sitemap (last 48 hours articles).
     * Google uses this format for immediate Google News carousels and indexation.
     */
    public function newsSitemap(): Response
    {
        $siteName = config('app.name', 'Extremis News');

        // Google News accepts articles published within the last 48 hours
        $newsPosts = News::where('status', 1)
            ->where('is_approved', 1)
            ->where('created_at', '>=', now()->subHours(48))
            ->orderBy('created_at', 'DESC')
            ->take(1000)
            ->get();

        // Fallback: If no articles in last 48h, provide latest 10 articles so sitemap is never empty
        if ($newsPosts->isEmpty()) {
            $newsPosts = News::where('status', 1)
                ->where('is_approved', 1)
                ->orderBy('created_at', 'DESC')
                ->take(10)
                ->get();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        foreach ($newsPosts as $post) {
            $postUrl = route('news-details', $post->slug);
            $pubDate = ($post->created_at ?? now())->toAtomString();
            $lang = $post->language === 'bn' ? 'bn' : 'en';

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($postUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <news:news>\n";
            $xml .= "      <news:publication>\n";
            $xml .= "        <news:name>" . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . "</news:name>\n";
            $xml .= "        <news:language>" . $lang . "</news:language>\n";
            $xml .= "      </news:publication>\n";
            $xml .= "      <news:publication_date>" . $pubDate . "</news:publication_date>\n";
            $xml .= "      <news:title>" . htmlspecialchars($post->title, ENT_XML1, 'UTF-8') . "</news:title>\n";
            $xml .= "    </news:news>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
