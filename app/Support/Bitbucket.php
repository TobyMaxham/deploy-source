<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * @author Tobias Maxham <git2020@maxham.de>
 */
class Bitbucket
{
    const ENDPOINT = 'https://api.bitbucket.org/2.0/repositories';

    public static function getCommits(): Collection
    {
        $currentPage = Deployer::currentPage();

        return Cache::get('commits'.$currentPage, self::fetchCommits($currentPage));
    }

    private static function fetchCommits($page): Closure
    {
        return function () use ($page) {
            $commits = collect();
            $response = Http::acceptJson()
                ->withBasicAuth(config('deployment.repository.auth_username'), config('deployment.repository.auth_password'))
                ->get(self::getPath($page))->object();

            if (! isset($response->values)) {
                return $commits;
            }

            $commits = collect($response->values);
            Cache::put('commits'.$page, $commits, config('deployment.cache.commits'));

            return $commits;
        };
    }

    private static function getPath($page): string
    {
        $username = config('deployment.repository.username');
        $repo = config('deployment.repository.reponame');
        $branch = config('deployment.repository.base_branch');
        $pagelen = config('deployment.repository.pagelen');

        return Bitbucket::ENDPOINT."/$username/$repo/commits/?include=$branch&pagelen=$pagelen&page=$page";
    }
}
