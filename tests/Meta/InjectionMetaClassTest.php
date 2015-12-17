<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Octahedron\Pulp\Test\Meta;

use Octahedron\Pulp\Meta\InjectionMetaClass;
use Octahedron\Pulp\Meta\Annotation\Inject;
use Octahedron\Pulp\Meta\Annotation\Assisted;
use Octahedron\Pulp\Meta\Annotation\Named;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

class InjectionMetaClassTest extends \PHPUnit_Framework_TestCase {

  protected $annotationReader;

  public function setup() {
    AnnotationRegistry::registerLoader(function($class) {
      $file = __DIR__ . '/../../lib/' . str_replace('\\', '/', substr($class, strlen('Octahedron\\Pulp\\'))) . '.php';
      if (file_exists($file)) return !!include $file;
    });
    $this->annotationReader = new AnnotationReader();
  }

  public function testMethodInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestMethodInjectClass', $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(1, count($setters['testMethod']));
    $this->assertEquals('Octahedron\Pulp\Test\Meta\TestParameter', $setters['testMethod'][0]->type());
    $this->assertFalse($setters['testMethod'][0]->isOptional());
    $this->assertFalse($setters['testMethod'][0]->isAssisted());
    $this->assertFalse($injectionMetaClass->hasInjectableConstructor());
  }

  public function testConstructorInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestConstructorInjectClass', $this->annotationReader);
    $this->assertTrue($injectionMetaClass->hasInjectableConstructor());
    $this->assertEquals(0, count($injectionMetaClass->injectableSetters()));
    $this->assertEquals(1, count($injectionMetaClass->injectableConstructor()));
  }

  public function testMultipleParametersInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestMultipleParametersInjectClass', $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertEquals(1, count($setters));
    $this->assertEquals(2, count($setters['testMethod']));
    $this->assertEquals('param', $setters['testMethod'][0]->name());
    $this->assertEquals('paramTwo', $setters['testMethod'][1]->name());
  }

  public function testOptionalInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestOptionalInjectClass', $this->annotationReader);
    $setters = $injectionMetaClass->injectableSetters();
    $this->assertTrue($setters['testMethod'][0]->isOptional());
  }

  public function testAssistedInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestAssistedInjectClass', $this->annotationReader);
    $constructor = $injectionMetaClass->injectableConstructor();
    $this->assertTrue($constructor[0]->isAssisted());
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Assisted injection not possible for setters
   */
  public function testAssistedInjectionErrorOnSetter() {
    new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestAssistedSetterInjectClass', $this->annotationReader);
  }

  public function testNamedInjection() {
    $injectionMetaClass = new InjectionMetaClass('Octahedron\Pulp\Test\Meta\TestNamedInjectClass', $this->annotationReader);
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
