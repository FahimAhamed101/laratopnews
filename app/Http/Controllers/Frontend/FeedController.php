<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    /**
     * Generate RSS 2.0 / Atom feed for Google News, search engine crawlers, and RSS aggregators.
     */
    public function index(): Response
    {
        $siteName = config('app.name', 'Extremis News');
        $siteUrl = url('/');
        $feedUrl = route('feed');

        $latestNews = News::where('status', 1)
            ->where('is_approved', 1)
            ->with(['category', 'author'])
            ->orderBy('created_at', 'DESC')
            ->take(50)
            ->get();

        $lastBuildDate = $latestNews->first()
            ? ($latestNews->first()->updated_at ?? $latestNews->first()->created_at ?? now())->toRfc822String()
            : now()->toRfc822String();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" '
            . 'xmlns:content="http://purl.org/rss/1.0/modules/content/" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:atom="http://www.w3.org/2005/Atom" '
            . 'xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title><![CDATA[" . $siteName . " - Worldwide Verified News & Analysis]]></title>\n";
        $xml .= "    <link>" . $siteUrl . "</link>\n";
        $xml .= "    <description><![CDATA[Breaking international news, investigative analysis, technology breakthroughs, and economic developments.]]></description>\n";
        $xml .= "    <language>en-US</language>\n";
        $xml .= "    <lastBuildDate>" . $lastBuildDate . "</lastBuildDate>\n";
        $xml .= '    <atom:link href="' . $feedUrl . '" rel="self" type="application/rss+xml" />' . "\n";
        $xml .= "    <generator>Extremis News Automated Publisher</generator>\n";

        foreach ($latestNews as $post) {
            $postUrl = route('news-details', $post->slug);
            $pubDate = ($post->created_at ?? now())->toRfc822String();
            $categoryName = $post->category?->name ?? 'Global News';
            $authorName = $post->author?->name ?? 'Editorial Desk';
            $description = $post->meta_description ?: Str::limit(strip_tags($post->content), 200);

            $xml .= "    <item>\n";
            $xml .= "      <title><![CDATA[" . $post->title . "]]></title>\n";
            $xml .= "      <link>" . $postUrl . "</link>\n";
            $xml .= '      <guid isPermaLink="true">' . $postUrl . "</guid>\n";
            $xml .= "      <pubDate>" . $pubDate . "</pubDate>\n";
            $xml .= "      <dc:creator><![CDATA[" . $authorName . "]]></dc:creator>\n";
            $xml .= "      <category><![CDATA[" . $categoryName . "]]></category>\n";
            $xml .= "      <description><![CDATA[" . $description . "]]></description>\n";

            if (!empty($post->image)) {
                $imageUrl = asset($post->image);
                $xml .= '      <media:content url="' . htmlspecialchars($imageUrl) . '" medium="image" />' . "\n";
                $xml .= '      <enclosure url="' . htmlspecialchars($imageUrl) . '" length="150000" type="image/png" />' . "\n";
            }

            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= "</rss>";

        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
