<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\GenericUser;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $api_token = $request->header('Api-Token');
            if ($api_token) {
                $token = app('db')->table('apitokens')->where([
                    'token' => $api_token,
                    'valid' => true
                ])->get();
                if($token->count() == 1)
                    return new GenericUser(['id' => 1, 'name' => 'Test User']);
                else
                    return null;
            }
            else
                return null;
        });
    }
}
