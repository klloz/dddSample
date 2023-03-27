<?php

namespace App\Providers;

use Domains\Accounts\Models\SystemUserBuilder;
use Domains\Common\Models\Account\SystemUserBuilderContract;
use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(SystemUserBuilderContract::class, function() {
            return new SystemUserBuilder();
        });
    }
}
