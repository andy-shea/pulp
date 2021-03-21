<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Meta;

use Pulp\Meta\Attribute\Inject;
use Pulp\Meta\Attribute\Named;
use Pulp\Meta\Attribute\Provides;
use Pulp\Meta\Attribute\Assisted;
use ReflectionClass;
use ReflectionProperty;
use Exception;

/**
 * Collects meta-information for the class to determine injectable dependencies.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InjectionMetaClass {

  protected $injectableProperties = [];
  protected $injectableMethods = [];
  protected $class;

  public function __construct($className) {
    $this->class = $className;
    $reflectedClass = new ReflectionClass($className);
    $this->getInjectableProperties($reflectedClass);
    $this->getInjectableMethods($reflectedClass->getMethods());
  }

  protected function getInjectableProperties(ReflectionClass $reflectedClass) {
    $propertyTypes = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
    $classProperties = $reflectedClass->getProperties($propertyTypes);
    $defaults = $reflectedClass->getDefaultProperties();
    foreach ($classProperties as $reflectedProperty) {
      if (Inject::shouldInject($reflectedProperty)) {
        $name = $reflectedProperty->getName();
        $namedType = Named::extractNamedType($reflectedProperty);
        $providesType = Provides::extractProvidesType($reflectedProperty);
        $injectionProperty = new InjectionParameter($name, $reflectedProperty);
        if ($namedType) $injectionProperty->setAlias($namedType);
        else if ($providesType) $injectionProperty->setProvides($providesType);
        else if ($type = $reflectedProperty->getType()) $injectionProperty->setInterface($type->getName());
        if ($reflectedProperty->hasDefaultValue()) {
          $injectionProperty->setDefaultValue($reflectedProperty->getDefaultValue());
        }
        $this->injectableProperties[$name] = $injectionProperty;
      }
    }
  }

  protected function getInjectableMethods($classMethods) {
    foreach ($classMethods as $reflectedMethod) {
      if (Inject::shouldInject($reflectedMethod)) {
        $parameters = [];
        foreach ($reflectedMethod->getParameters() as $reflectedParameter) { 
          $name = $reflectedParameter->getName();
          $namedType = Named::extractNamedType($reflectedParameter);
          $providesType = Provides::extractProvidesType($reflectedParameter);
          $injectionParameter = new InjectionParameter($name, $reflectedParameter);
          if (Assisted::isAssisted($reflectedParameter)) {
            if ($reflectedMethod->getName() !== '__construct') throw new Exception('Assisted injection not possible for setters');
            $injectionParameter->setIsAssisted(true);
          }
          else if ($namedType) $injectionParameter->setAlias($namedType);
          else if ($providesType) $injectionParameter->setProvides($providesType);
          else if ($type = $reflectedParameter->getType()) $injectionParameter->setInterface($type->getName());
          if ($reflectedParameter->isDefaultValueAvailable()) {
            $injectionParameter->setDefaultValue($reflectedParameter->getDefaultValue());
          }
          $parameters[] = $injectionParameter;
        }
        if ($parameters) $this->injectableMethods[$reflectedMethod->getName()] = $parameters;
      }
    }
  }

  public function hasInjectableConstructor() {
    return isset($this->injectableMethods['__construct']);
  }

  public function injectableConstructor() {
    if (!$this->hasInjectableConstructor()) throw new Exception('No injectable constructor found');
    return $this->injectableMethods['__construct'];
  }

  public function injectableSetters() {
    return array_diff_key($this->injectableMethods, array_flip(['__construct']));
  }

  public function injectableProperties() {
    return $this->injectableProperties;
  }

}
