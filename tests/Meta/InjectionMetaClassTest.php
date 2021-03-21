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
use Pulp\Meta\Annotation\Inject;
use Pulp\Meta\Annotation\Assisted;
use Pulp\Meta\Annotation\Named;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class InjectionMetaClassTest extends TestCase {

  protected $annotationReader;

  public function setup(): void {
    AnnotationRegistry::registerLoader(function($class) {
      $file = __DIR__ . '/../../lib/' . str_replace('\\', '/', substr($class, strlen('Pulp\\'))) . '.php';
      if (file_exists($file)) return !!include $file;
    });
    $this->annotationReader = new AnnotationReader();
  }

  public function testMethodInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestMethodInjectClass::class, $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(1, count($setters['testMethod']));
    $this->assertEquals(TestParameter::class, $setters['testMethod'][0]->type());
    $this->assertFalse($setters['testMethod'][0]->isOptional());
    $this->assertFalse($setters['testMethod'][0]->isAssisted());
    $this->assertFalse($injectionMetaClass->hasInjectableConstructor());
  }

  public function testConstructorInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestConstructorInjectClass::class, $this->annotationReader);
    $this->assertTrue($injectionMetaClass->hasInjectableConstructor());
    $this->assertEquals(0, count($injectionMetaClass->injectableSetters()));
    $this->assertEquals(1, count($injectionMetaClass->injectableConstructor()));
  }

  public function testMultipleParametersInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestMultipleParametersInjectClass::class, $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(2, count($setters['testMethod']));
    $this->assertEquals('param', $setters['testMethod'][0]->name());
    $this->assertEquals('paramTwo', $setters['testMethod'][1]->name());
  }

  public function testOptionalInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestOptionalInjectClass::class, $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertTrue($setters['testMethod'][0]->isOptional());
  }

  public function testAssistedInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestAssistedInjectClass::class, $this->annotationReader);
    $constructor = $injectionMetaClass->injectableConstructor();
    $this->assertTrue($constructor[0]->isAssisted());
  }

  public function testAssistedInjectionErrorOnSetter() {
    $this->expectException(\Exception::class, 'Assisted injection not possible for setters');
    new InjectionMetaClass(TestAssistedSetterInjectClass::class, $this->annotationReader);
  }

  public function testNamedInjection() {
    $injectionMetaClass = new InjectionMetaClass(TestNamedInjectClass::class, $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals('TestProvider', $setters['testMethod'][0]->type());
  }

}

class TestParameter {}
class TestParameterTwo {}

class TestMethodInjectClass {

  /** @Inject */
  public function testMethod(TestParameter $param) {}

}

class TestConstructorInjectClass {

  /** @Inject */
  public function __construct(TestParameter $param) {}

}

class TestMultipleParametersInjectClass {

  /** @Inject */
  public function testMethod(TestParameter $param, TestParameterTwo $paramTwo) {}

}

class TestOptionalInjectClass {

  /** @Inject */
  public function testMethod(TestParameter $param = null) {}

}

class TestAssistedInjectClass {

  /**
   * @Inject
   * @Assisted("param")
   */
  public function __construct(TestParameter $param) {}

}

class TestAssistedSetterInjectClass {

  /**
   * @Inject
   * @Assisted("param")
   */
  public function testMethod(TestParameter $param) {}

}

class TestNamedInjectClass {

  /**
   * @Inject
   * @Named({"param" = "TestProvider"})
   */
  public function testMethod(TestParameter $param) {}

}
