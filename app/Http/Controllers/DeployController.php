<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeployRequest;
use App\Support\Deployer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * @author Tobias Maxham <git2020@maxham.de>
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
