<?php

namespace App\Http\Controllers;

use App\Support\Deployer;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeployRequest;

/**
 * @author Tobias Maxham <git@maxham.de>
 */
class DeployController
{
    public function index(): View
    {
        $currentPage = Deployer::currentPage();
        $commits = Deployer::getCommits();
        $prodCommit = Deployer::getRemoteVersion();
        $sources = Deployer::getSources();

        return view('deploy', compact('commits', 'currentPage', 'sources', 'prodCommit'));
    }

    public function deploy(DeployRequest  $request)
    {
        $bool = Deployer::deploy($request->get('environment'));
        if ($bool) {
            return redirect()->route('index')->withInput()->with('success', __('Deploy Job executed'));
        }

        return redirect()->route('index')->withErrors([
            'deploy' => __('Deploy Job failed'),
        ]);
    }

    public function deployFromBitbucket(Request $request)
    {
        $webSecret = $request->get('secret');
        $confSecret = config('deployment.webhook_secret');

        if(empty($webSecret) || empty($confSecret) || $webSecret !== $confSecret) {
            return response()->json(['error' => 'Invalid secret'], 403);
        }

        return Deployer::deployFromBitbucket($request);
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'icons' => [
                [
                    'src' => 'images/icon-192x192.png',
                    'sizes' => '192x192',
                ],
            ],
            'start_url' => './index.html?homescreen=1',
            'display' => 'standalone',
        ]);
    }
}
