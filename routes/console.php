<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Apply flexible schedule frequency (supports 5-part cron expressions & friendly keywords)
 */
if (!function_exists('applyAiNewsSchedule')) {
    function applyAiNewsSchedule($event, string $frequency)
    {
        $frequency = trim($frequency);
        if (preg_match('/^(\S+\s+){4}\S+$/', $frequency)) {
            return $event->cron($frequency);
        }

        return match (strtolower($frequency)) {
            'everyminute' => $event->everyMinute(),
            'everyfiveminutes' => $event->everyFiveMinutes(),
            'everytenminutes' => $event->everyTenMinutes(),
            'everyfifteenminutes' => $event->everyFifteenMinutes(),
            'everythirtyminutes' => $event->everyThirtyMinutes(),
            'hourly' => $event->hourly(),
            'everytwohours' => $event->everyTwoHours(),
            'everythreehours' => $event->everyThreeHours(),
            'everyfourhours' => $event->everyFourHours(),
            'everysixhours' => $event->everySixHours(),
            'daily' => $event->daily(),
            'twicedaily' => $event->twiceDaily(1, 13),
            'weekly' => $event->weekly(),
            'monthly' => $event->monthly(),
            default => $event->cron($frequency ?: '0 * * * *'),
        };
    }
}

// ==============================================================================
// 1. AI Auto-Generation Schedule (Configured via AI_NEWS_GENERATE_SCHEDULE)
// ==============================================================================
if (config('ai_news.generate_enabled', false)) {
    $genSchedule = (string) config('ai_news.generate_schedule', 'hourly');
    $genCount = (int) config('ai_news.generate_count', 1);

    $generateEvent = Schedule::command("news:auto-generate --count={$genCount}")
        ->withoutOverlapping()
        ->runInBackground();

    applyAiNewsSchedule($generateEvent, $genSchedule);
}

// ==============================================================================
// 2. AI Auto-Update Schedule (Configured via AI_NEWS_UPDATE_SCHEDULE)
// ==============================================================================
if (config('ai_news.update_enabled', false)) {
    $updateSchedule = (string) config('ai_news.update_schedule', '0 */6 * * *');
    $updateLimit = (int) config('ai_news.update_limit', 2);
    $updateAge = (int) config('ai_news.update_age_hours', 24);

    $updateEvent = Schedule::command("news:auto-update --limit={$updateLimit} --age-hours={$updateAge}")
        ->withoutOverlapping()
        ->runInBackground();

    applyAiNewsSchedule($updateEvent, $updateSchedule);
}

// ==============================================================================
// 3. AI Auto-Delete / Retention Schedule (Configured via AI_NEWS_DELETE_SCHEDULE)
// ==============================================================================
if (config('ai_news.delete_enabled', false)) {
    $deleteSchedule = (string) config('ai_news.delete_schedule', '0 3 * * *');
    $deleteDays = (int) config('ai_news.delete_after_days', 30);
    $minViews = (int) config('ai_news.delete_min_views', 50);
    $deleteKeepMax = (int) config('ai_news.delete_keep_max', 500);

    $deleteEvent = Schedule::command("news:auto-delete --days={$deleteDays} --min-views={$minViews} --keep-max={$deleteKeepMax}")
        ->withoutOverlapping()
        ->runInBackground();

    applyAiNewsSchedule($deleteEvent, $deleteSchedule);
}
