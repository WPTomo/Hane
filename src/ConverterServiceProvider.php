<?php

namespace Wptomo\Hane;

use Illuminate\Support\ServiceProvider;
use Wptomo\Hane\Console\ConverterMakeCommand;

class ConverterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConverterMakeCommand::class,
            ]);
        }
    }
}
