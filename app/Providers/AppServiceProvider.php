<?php

namespace App\Providers;

use App\Http\Services\SuggestionEngineServiceImpV3;
use App\Http\Services\SuggestionEngineServiceV2;
use App\Http\Services\SuggestionEngineServiceImpV2;
use App\Http\Services\SuggestionEngineServiceV3;
use Illuminate\Support\ServiceProvider;
use App\Libraries\Http\ExternalApi;
use App\Libraries\Http\ExternalApiImpl;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use App\Http\Services\SuggestionEngineService;
use App\Http\Services\SuggestionEngineServiceImp;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        //Bind External API
        $this->app->bind(ExternalApi::class, ExternalApiImpl::class);
        $this->app->bind(Client::class, function () {
            return ClientBuilder::create()
                ->setSSLVerification(false)
                ->setHosts(config('es.hosts'))
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
