<?php

namespace App\Jobs\Concerns;

use Illuminate\Queue\Middleware\RateLimited;

/**
 * Apply SMTP rate limiting so we don't exceed provider limits (e.g. Hostinger 451).
 * Uses the 'smtp' limiter from AppServiceProvider; excess jobs are released for 60s.
 */
trait RateLimitsOutboundMail
{
    public function middleware(): array
    {
        return [
            (new RateLimited('smtp'))->releaseAfter(60),
        ];
    }
}
