<?php

namespace Javoscript\MacroableModels\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as Base;

abstract class TestCase extends Base
{
    protected function setUp(): void
    {
        parent::setUp();

        $config = require __DIR__.'/config/database.php';

        $db = new DB;
        $db->addConnection($config['sqlite']);
        $db->setAsGlobal();
        $db->bootEloquent();

        $this->migrate();
    }

    protected function migrate()
    {
        DB::schema()->dropAllTables();

        DB::schema()->create('dummies', function(Blueprint $table) {
            $table->increments('id');
        });

        DB::schema()->create('anothers', function(Blueprint $table) {
            $table->increments('id');
        });
    }
}
