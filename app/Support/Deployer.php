<?php

namespace App\Support;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * @author Tobias Maxham <git@maxham.de>
 */
class Deployer
{
    public static function getSources(): Collection
    {
        return self::getArrayFromConfig('deployment.sources');
    }

    public static function getWebhookSources(): Collection
    {
        return self::getArrayFromConfig('deployment.webhook_sources');
    }

    public static function getScripts(): Collection
    {
        return self::getArrayFromConfig('deployment.scripts');
    }

    private static function getArrayFromConfig($conf): Collection
    {
        $confCol = collect(explode(';', config($conf)));
        $newCol = collect();

        $confCol->each(function ($item) use (&$newCol) {
            $itemArr = explode(':', $item);
            if (2 == count($itemArr)) {
                $newCol->put($itemArr[0], $itemArr[1]);
            }
        });

        return $newCol;
    }

    public static function deploy(string $env): bool
    {
        $script = self::getScripts()->keyBy($env)->first();
        self::executeScript($script);

        return true;
    }

    private static function executeScript($script)
    {
        $output = exec($script);

        Log::channel(config('deployment.logging_channel'))
            ->info("Executed Script ... $script ... returned: $output");

        return $output;
    }

    public static function deployFromBitbucket(Request $request)
    {
        if (! ($data = $request->get('push')) || !isset($data['changes'])) {
            return response('');
        }

        $branches = collect($data['changes'])
            ->map(self::mapBitbucketHook())
            ->unique()
            ->filter();

        if (! $branches->count()) {
            return response('');
        }

        $sources = self::getWebhookSources()->filter(fn($i, $src) => $src != 'null')->filter();
        if (! $sources->count()) {
            return response('');
        }

        $branches->each(function($branch) use($sources) {
            if($script = $sources->get($branch)) {
                self::executeScript($script);
            }
        });

        return response('');
    }

    public static function currentPage(): int
    {
        if (is_numeric(request()->get('page'))) {
            return (int) request()->get('page');
        }

        return 1;
    }

    public static function getRemoteVersion(): string
    {
        return Cache::get('prodCommit', function () {
            $currentCommit = '';

            try {
                $response = Http::acceptJson()->get(config('deployment.remote_version_url'))->json();
                if (isset($response['commit'])) {
                    $currentCommit = $response['commit'];
                }
            } catch (Exception $exception) {
                // Ooops....
            }

            Cache::put('prodCommit', $currentCommit, config('deployment.cache.remote_version'));

            return $currentCommit;
        });
    }

    public static function getCommits(): Collection
    {
        return Bitbucket::getCommits();
    }

    public static function canDeploy(): bool
    {
        $conf = self::allowedDays();
        $now = Carbon::now()->format('D');
        return $conf->filter(fn($val) => strtolower($val) == strtolower($now))->count() > 0;
    }

    public static function allowedDays(): Collection
    {
        return collect(explode(',', config('deployment.deploy_limitation')));
    }

    private static function mapBitbucketHook()
    {
        return fn($item) => isset($item['new']['name']) && ! empty($item['new']['name']) ? $item['new']['name'] : null;
    }
}
