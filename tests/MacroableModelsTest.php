<?php

namespace Javoscript\MacroableModels\Tests;

use Javoscript\MacroableModels\MacroableModels;
use Javoscript\MacroableModels\Tests\Models\DummyModel;
use Javoscript\MacroableModels\Tests\Models\AnotherDummy;


class MacroableModelsTest extends TestCase
{
    protected $mm;
    protected $model;
    protected $anotherModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->mm = new MacroableModels();
        $this->model = new DummyModel();
        $this->anotherModel = new AnotherDummy();
    }

    public function testNoMacrosAreRegisteredUponClassCreation()
    {
        $this->assertEmpty($this->mm->getAllMacros());
    }

    // Call a function on a dummy model without registering the macro first throws a BadMethodCallException exception
    public function testBadMethodCallExceptionIsThrownIfNoMacroAdded()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->model->exampleMacro();
    }

    // Call a function on a dummy model after registering the macro first doesn't throw a BadMethod exception
    public function testNoBadMethodCallExceptionThrownAfterAddingTheMacro()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return true; });
        $this->model->exampleMacro();

        $this->assertTrue(true);
    }

    // Call a function on a dummy model after registering the macro first and then removing it throws a BadMethod exception
    public function testBadMethodCallExceptionAfterRemovingTheMacro()
    {
        $this->expectException(\BadMethodCallException::class);


        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return true; });
        $this->mm->removeMacro(DummyModel::class, 'exampleMacro');
    }

    // Redeclaring the macro (same name) for the given model replaces the existing one
    public function testRedeclaringTheMacroForAGivenModelReplacesTheExistingOne()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertTrue($this->model->exampleMacro() == 1);

        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 2; });
        $this->assertTrue($this->model->exampleMacro() == 2);
    }

    // modelHasMacro function returns true after adding the macro for a model
    public function testModelHasMacroReturnsTrueAfterAddingTheMacroForTheModel()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertTrue($this->mm->modelHasMacro(DummyModel::class, 'exampleMacro'));

        $this->assertFalse($this->mm->modelHasMacro(AnotherDummy::class, 'exampleMacro'));
    }

    // modelHasMacro function returns false before adding the macro for a model
    public function testModelHasMacroReturnsFalseBeforeAddingTheMacroForTheModel()
    {
        $this->assertFalse($this->mm->modelHasMacro(DummyModel::class, 'exampleMacro'));
    }

    // modelHasMacro function returns false after removing the macro for a model
    public function testModelHasMacroReturnsFalseAfterRemovingTheMacroForTheModel()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertTrue($this->mm->modelHasMacro(DummyModel::class, 'exampleMacro'));

        $this->mm->removeMacro(DummyModel::class, 'exampleMacro');
        $this->assertFalse($this->mm->modelHasMacro(DummyModel::class, 'exampleMacro'));
    }

    // modelsThatImplement function returns no models before registering a macro with that function name
    public function testModelsThatImplementFunctionReturnsNoModelsBeforeRegisteringAMacro()
    {
        $this->assertEmpty($this->mm->modelsThatImplement('exampleMacro'));
    }

    // modelsThatImplement function returns a model after registering a macro with that function name
    public function testModelsThatImplementFunctionReturnsAModelAfterRegisteringTheMacroForTheModel()
    {
        $this->assertEmpty($this->mm->modelsThatImplement('exampleMacro'));

        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertContains(DummyModel::class, $this->mm->modelsThatImplement('exampleMacro'));
    }

    // TODO: modelsThatImplement function returns multiple models after registering a macro with that function name for multiple models
    public function testModelsThatImplementFunctionReturnsMultipleModelsAfterRegisteringTheMacroForTheModels()
    {
        $this->assertEmpty($this->mm->modelsThatImplement('exampleMacro'));

        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->mm->addMacro(AnotherDummy::class, 'exampleMacro', function() { return 1; });

        $this->assertContains(DummyModel::class, $this->mm->modelsThatImplement('exampleMacro'));
        $this->assertContains(AnotherDummy::class, $this->mm->modelsThatImplement('exampleMacro'));

        $this->assertCount(2, $this->mm->modelsThatImplement('exampleMacro'));
    }

    //  modelsThatImplement function returns no models after removing the macro for the models that were added
    public function testModelsThatImplementFunctionReturnsNoModelsAfterRemovingTheMacroForTheModels()
    {
        $this->assertEmpty($this->mm->modelsThatImplement('exampleMacro'));
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });

        $this->assertCount(1, $this->mm->modelsThatImplement('exampleMacro'));

        $this->mm->removeMacro(DummyModel::class, 'exampleMacro');
        $this->assertEmpty($this->mm->modelsThatImplement('exampleMacro'));
    }

    // macrosForModel returns empty before registering any macro for the given model
    public function testMacrosForModelReturnsEmptyBeforeRegisteringAnyMacroForTheModel()
    {
        $this->assertEmpty($this->mm->macrosForModel(DummyModel::class));
        $this->assertEmpty($this->mm->macrosForModel(AnotherDummy::class));
    }

    // macrosForModel returns the added macro for the given model after adding it
    public function testMacrosForModelReturnsTheMacroAfterRegisteringItForTheModel()
    {
        $this->assertEmpty($this->mm->macrosForModel(DummyModel::class));
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertArrayHasKey('exampleMacro', $this->mm->macrosForModel(DummyModel::class));
    }

    // macrosForModel returns multiple macros for the given model after adding multiple macros
    public function testMacrosForModelReturnsMultipleMacrosAfterRegisteringThemForTheModel()
    {
        $this->assertEmpty($this->mm->macrosForModel(DummyModel::class));

        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->mm->addMacro(DummyModel::class, 'anotherMacro', function() { return 2; });

        $this->assertArrayHasKey('exampleMacro', $this->mm->macrosForModel(DummyModel::class));
        $this->assertArrayHasKey('anotherMacro', $this->mm->macrosForModel(DummyModel::class));

        $this->assertCount(2, $this->mm->macrosForModel(DummyModel::class));
    }

    // macrosForModel returns empty after removing all macros for the given model
    public function testMacrosForModelReturnsEmptyAfterRemovingAllMacrosForTheModel()
    {
        $this->assertEmpty($this->mm->macrosForModel(DummyModel::class));

        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->mm->addMacro(DummyModel::class, 'anotherMacro', function() { return 2; });
        $this->assertCount(2, $this->mm->macrosForModel(DummyModel::class));

        $this->mm->removeMacro(DummyModel::class, 'anotherMacro');
        $this->assertCount(1, $this->mm->macrosForModel(DummyModel::class));

        $this->mm->removeMacro(DummyModel::class, 'exampleMacro');
        $this->assertEmpty($this->mm->macrosForModel(DummyModel::class));
    }

    // a registered macro is correctly called after registration
    public function testARegisteredMacroReturnsTheExpectedResult()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function() { return 1; });
        $this->assertEquals($this->model->exampleMacro(), 1);
    }

    // a registered macro with a paramater returns the expected result
    public function testARegisteredMacroWithAParamaterReturnsTheExpectedResult()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function($a) { return 1 + $a; });
        $this->assertEquals($this->model->exampleMacro(2), 3);
    }

    // a registered macro with multiple parameters returns the expected result
    public function testARegisteredMacroWithMultipleParamatersReturnsTheExpectedResult()
    {
        $this->mm->addMacro(DummyModel::class, 'exampleMacro', function($name, $surname) { return "Hello, {$name} {$surname}"; });
        $this->assertEquals($this->model->exampleMacro('James', 'Bond'), "Hello, James Bond");
    }
}
