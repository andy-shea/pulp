<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pulp\Test\Meta;

use Pulp\Meta\InjectionMetaClass;
use Pulp\Meta\Attribute\Inject;
use Pulp\Meta\Attribute\Assisted;
use Pulp\Meta\Attribute\Named;
use PHPUnit\Framework\TestCase;
use Exception;

class InjectionMetaClassTest extends TestCase {

  public function testMethodInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestMethodInjectClass::class);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(1, count($setters['testMethod']));
    $this->assertEquals(TestParameter::class, $setters['testMethod'][0]->type());
    $this->assertFalse($setters['testMethod'][0]->isOptional());
    $this->assertFalse($setters['testMethod'][0]->isAssisted());
    $this->assertFalse($injectionMetaClass->hasInjectableConstructor());
  }

  public function testConstructorInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestConstructorInjectClass::class);
    $this->assertTrue($injectionMetaClass->hasInjectableConstructor());
    $this->assertEquals(0, count($injectionMetaClass->injectableSetters()));
    $this->assertEquals(1, count($injectionMetaClass->injectableConstructor()));
  }

  public function testMultipleParametersInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestMultipleParametersInjectClass::class);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(2, count($setters['testMethod']));
    $this->assertEquals('param', $setters['testMethod'][0]->name());
    $this->assertEquals('paramTwo', $setters['testMethod'][1]->name());
  }

  public function testOptionalInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestOptionalInjectClass::class);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertTrue($setters['testMethod'][0]->isOptional());
  }

  public function testAssistedInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestAssistedInjectClass::class);
    $constructor = $injectionMetaClass->injectableConstructor();
    $this->assertTrue($constructor[0]->isAssisted());
  }

  public function testAssistedInjectionErrorOnSetter() {
    $this->expectException(Exception::class, 'Assisted injection not possible for setters');
    new InjectionMetaClass(TestAssistedSetterInjectClass::class);
  }

  public function testNamedInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestNamedInjectClass::class);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals('TestProvider', $setters['testMethod'][0]->type());
  }

}

class TestParameter {}
class TestParameterTwo {}

class TestMethodInjectClass {

  #[Inject]
  public function testMethod(TestParameter $param) {}

}

class TestConstructorInjectClass {

  #[Inject]
  public function __construct(TestParameter $param) {}

}

class TestMultipleParametersInjectClass {

  #[Inject]
  public function testMethod(TestParameter $param, TestParameterTwo $paramTwo) {}

}

class TestOptionalInjectClass {

  #[Inject]
  public function testMethod(TestParameter $param = null) {}

}

class TestAssistedInjectClass {

  #[Inject]
  public function __construct(#[Assisted] TestParameter $param) {}

}

class TestAssistedSetterInjectClass {

  #[Inject]
  public function testMethod(#[Assisted] TestParameter $param) {}

}

class TestNamedInjectClass {

  #[Inject]
  public function testMethod(#[Named('TestProvider')] TestParameter $param) {}

}
