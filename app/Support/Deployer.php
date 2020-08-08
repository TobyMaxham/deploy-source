<?php

namespace App\Support;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @author Tobias Maxham <git2020@maxham.de>
 */
class Deployer
{
    public static function getSources(): Collection
    {
        return self::getArrayFromConfig('deployment.sources');
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
        $output = exec($script);

        Log::channel(config('deployment.logging_channel'))
            ->info("Executed Script ... $script ... returned: $output");

        return true;
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
}
