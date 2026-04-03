<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

return [
    AppServiceProvider::class,
    TelescopeServiceProvider::class,
    LaravelServiceProvider::class,
];
