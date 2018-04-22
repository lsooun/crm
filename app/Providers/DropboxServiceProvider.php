<?php namespace App\Providers;


use Dropbox\Client as DropboxClient;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Dropbox\DropboxAdapter;
use League\Flysystem\Filesystem;
use Storage;

class DropboxServiceProvider extends ServiceProvider
{


    public function boot()
    {
        Storage::extend('dropbox', function ($app, $config) {

            $client = new DropboxClient(
                $config['token'], $config['secret']
            );

            return new Filesystem(new DropboxAdapter($client));
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}