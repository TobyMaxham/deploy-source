<?php

return [
    'sources' => env('DEPLOY_SOURCES', 'null:Select Environment'),
    'app_logo' => env('DEPLOY_LOGO', 'https://via.placeholder.com/72x72'),
    'remote_version_url' => env('DEPLOY_REMOTE_VERSION_URL'),
    'cache' => [ // in seconds
        'remote_version' => env('DEPLOY_CACHE_REMOTE_VERSION', 100),
        'commits' => env('DEPLOY_CACHE_COMMITS', 30),
    ],
    'user_domain' => env('DEPLOY_ALLOWED_USERS'),
    'logging_channel' => env('DEPLOY_LOGGING_CHANNEL', 'scripts'),
    'deploy_limitation' => env('DEPLOY_LIMITATION', 'mon,tue,wed,thu,fri,sat,sun'),
    'repository' => [
        'auth_username' => env('DEPLOY_BITBUCKET_AUTH_USERNAME'),
        'auth_password' => env('DEPLOY_BITBUCKET_AUTH_PASSWORD'),
        'username' => env('DEPLOY_BITBUCKET_USERNAME'),
        'reponame' => env('DEPLOY_BITBUCKET_REPONAME'),
        'base_branch' => env('DEPLOY_BITBUCKET_BASEBRANCH'),
        'pagelen' => env('DEPLOY_BITBUCKET_PAGELEN'),
    ],
    'scripts' => env('DEPLOY_SCRIPTS'),
    'refresh_page' => env('DEPLOY_REFRESH_PAGE', 600),
];
