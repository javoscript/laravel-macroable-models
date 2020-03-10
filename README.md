# Laravel macroable models
A package for adding methods to Laravel models on the fly 🕊

The package offers developers an easy way of programmatically adding methods to Laravel Eloquent [models](https://laravel.com/docs/6.x/eloquent#defining-models). Behind the scenes, it makes use of Laravel's own macroable trait. For more details, check the post where I explain how I did it in my [blog](https://javoscript.com/blog/post/adding-macros-to-laravel-eloquent-models).

## Installation
Just install the package with `composer`
> composer require javoscript/laravel-macroable-models

**(Only necessary for Laravel <5.5, or if you want to be explicit)** - Add the Service Provider to the `providers` array in the `config/app.php` file
```php
// config/app.php

$providers = [
    // ...
    \Javoscript\MacroableModels\MacroableModelsServiceProvider::class,
    // ...
];

```

## Usage example
The package provides a [Facade](https://laravel.com/docs/6.x/facades) to facilitate access to it's functionality. Alternatively, you can access it through the `app('macroable-models')` helper.

For obvious reasons, macros should be added to the model before other parts of the system make use of it. Because of this, the `boot` method of [Service Providers](https://laravel.com/docs/6.x/providers) is a good place to start adding macros.

For example, adding a method to the `\App\User` model in `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php

// ...

use \Javoscript\MacroableModels\Facades\MacroableModels;
use \App\User;

// ...

public function boot()
{

    MacroableModels::addMacro(User::class, 'sayHi', function() {
        return 'Hi!';
    });

}

```

After adding the macro to the `User` model, now every instance of this Eloquent model will have the `sayHi()` method available.
We can quickly verify this within `artisan tinker`:

```
php artisan tinker

>>> \App\User::first()->sayHi()
=> "Hi!"
```

### In a dedicated `MacrosServiceProvider` file
If you want to keep multiple macro definitions together, then adding a Service Provider for this purpose might be a good idea.

You can generate a new Service Provider with `artisan`:
```
php artisan make:provider MacrosServiceProvider
```

Then, you should add it to the `providers` array in the `config/app.php` file.
```php
// config/app.php

$providers = [
    // ...
    App\Providers\MacrosServiceProvider::class,
    // ...
];

```

Then, in the `boot` method of this new Service Provider, you can centralize macro definitions:
```php
// app/Providers/MacrosServiceProvider.php

// ...

use \Javoscript\MacroableModels\Facades\MacroableModels;
use \App\User;

// ...

public function boot()
{
    
    MacroableModels::addMacro(User::class, 'sayHi', function() {
        return 'Hi!';
    });
    
    MacroableModels::addMacro(User::class, 'sayBye', function() {
        return 'Bye bye';
    });
    
}
```

## Available methods
The following examples will use the `\App\User` model so that you can try the examples on a fresh Laravel application. Any class that extends the `Illuminate\Database\Eloquent\Model` class can be extended with these macros.

### `addMacro(Model::class, 'macroName', function() {}) : void`
The most important method of this package, and the one you will most likely be using the most.
Add a macro with the name `macroName` to the model `Model::class`.

After the macro has been added, you can call the method on the model as you normally would.
```php

MacroableModels::addMacro(\App\User::class, 'sayHi', function() { return "Hi!"; });

\App\User::first()->sayHi();

```

#### With parameters
The defined macro function can receive any number and type of parameters.

```php

MacroableModels::addMacro(\App\User::class, 'say', function(string $something) { return $something; });

$user = \App\User::first();
$user->say("Hello world!");

```

#### Context binding... the correct `$this`
On the macro function you have access to the `$this` object, which references the instance of the model that is executing the function.

```php

MacroableModels::addMacro(\App\User::class, 'getId', function() { return $this->id; });

\App\User::first()->getId();
// 1

```

#### Adding relationships
You can define relationship functions too!

```php

MacroableModels::addMacro(\App\User::class, 'posts', function() {
    return $this->hasMany(App\Post::class);
});

```

Beware! You won't be able to use Laravel's magic relationship attributes.
```php

$user = App\User::first();

$user->posts;
// null
// This will always return null, as the posts attribute wasn't defined

$user->posts()->get()
// This will correctly return the posts Eloquent collection

```

#### Overriding existing macro
If you add a macro with the same name of an existing one, it replaces it.

```php

MacroableModels::addMacro(\App\User::class, 'greet', function() { return "Hi!"; });
\App\User::first()->greet();
// "Hi!"

MacroableModels::addMacro(\App\User::class, 'greet', function() { return "Hello human"; });
\App\User::first()->greet();
// "Hello human"


```

#### Model's methods precedence
If you add a macro with the same name of an existing method from the model, the latter will take precedence. You won't be able to override it with this package.

```php

class Dog extends Illuminate\Database\Eloquent\Model
{
    public function bark()
    {
        return "Woof!";
    }
}

MacroableModels::addMacro(Dog::class, 'bark', function() { return "Miauuu!"; });

$dog = new Dog;
$dog->bark();
// "Woof!"

```

### `removeMacro(Model::class, 'macroName') : boolean`
The opposite of `addMacro`, this method removes a previously added macro from the specified model. It returns `true` if a macro with that name was previously registered on the model and it removed it correctly - and `false` otherwise.

```php

MacroableModels::removeMacro(\App\User::class, 'salute');
// false

MacroableModels::addMacro(\App\User::class, 'salute', function() { return "Hello!"; });

MacroableModels::removeMacro(\App\User::class, 'salute');
// true

```

## Additional goodies
Because, why not? 🤷‍♂

### `getAllMacros() : Array`
Returns all registered macros, grouped by name.
```php

MacroableModels::addMacro(\App\User::class, 'hi', function() { return "Hi!"; })

MacroableModels::addMacro(\App\Dog::class, 'hi', function() { return "Woof!"; })

MacroableModels::addMacro(\App\User::class, 'bye', function() { return "Bye bye"; })

MacroableModels::getAllMacros()

/*
   [
     "hi" => [
       "App\User" => Closure() {#3362 …2},
       "App\Dog" => Closure() {#3376 …2},
     ],
     "bye" => [
       "App\User" => Closure() {#3366 …2},
     ],
   ]
*/

```

### `modelHasMacro(Model::class, 'macroName') : boolean`
Simple: if the model has the macro, it returns `true` - else, it returns `false`.
```php

MacroableModels::modelHasMacro(\App\User::class, 'salute');
// false

MacroableModels::addMacro(\App\User::class, 'salute', function() { return "Hi!"; });
MacroableModels::modelHasMacro(\App\User::class, 'salute');
// true
```

### `modelsThatImplement('macroName') : Array`
Given a macro name, it returns an array with the classes of the models to which it was added.
```php

MacroableModels::addMacro(\App\User::class, 'hi', function() { return "Hi!"; });
MacroableModels::addMacro(\App\Dog::class, 'hi', function() { return "Woof!"; });

MacroableModels::modelsThatImplement('hi');
/*
   [
      "App\User",
      "App\Dog",
   ]
*/

```

### `macrosForModel(Model::class) : Array`
Given the model class, it returns an array with all the macros that were added to it, detailing the defined parameters for each.
```php

MacroableModels::addMacro(\App\User::class, 'say', function(String $something) { return $something; });
MacroableModels::addMacro(\App\User::class, 'sum', function(Integer $a, Integer $b) { return $a + $b; });

MacroableModels::macrosForModel(\App\User::class);
/*
   [
     "say" => [
       "name" => "say",
       "parameters" => [
         ReflectionParameter {#3385
           +name: "something",
           position: 0,
           typeHint: "string",
         },
       ],
     ],
     "sum" => [
       "name" => "sum",
       "parameters" => [
         ReflectionParameter {#3357
           +name: "a",
           position: 0,
           typeHint: "Integer",
         },
         ReflectionParameter {#3360
           +name: "b",
           position: 1,
           typeHint: "Integer",
         },
       ],
     ],
   ]
*/

```

## Related packages
There are some related packages out there, from which some inspiration was taken.

* imanghafoori1/eloquent-relativity: [github](https://github.com/imanghafoori1/eloquent-relativity)
* spatie/laravel-collection-macros: [github](https://github.com/spatie/laravel-collection-macros)
* spatie/macroable: [github](https://github.com/spatie/macroable)
