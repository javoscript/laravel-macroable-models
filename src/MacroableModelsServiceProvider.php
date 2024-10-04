<?php

namespace Javoscript\MacroableModels;

use Illuminate\Support\ServiceProvider;

class MacroableModelsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->scoped('macroable-models', function () {
            return new MacroableModels();
        });
    }
}
