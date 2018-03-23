<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Meta;

use Octahedron\Pulp\Meta\Annotation\Inject;
use Octahedron\Pulp\Meta\Annotation\Named;
use Octahedron\Pulp\Meta\Annotation\Provides;
use Octahedron\Pulp\Meta\Annotation\Assisted;
use Doctrine\Common\Annotations\Reader;

/**
 * Collects meta-information for the class to determine injectable dependencies.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class InjectionMetaClass {

  protected $injectableProperties = [];
  protected $injectableMethods = [];
  protected $class;
  protected $annotationReader;

  public function __construct($className, Reader $annotationReader) {
    $this->class = $className;
    $this->annotationReader = $annotationReader;
    $reflectedClass = new \ReflectionClass($className);
    $propertyTypes = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE;
    $this->getInjectableProperties($reflectedClass->getProperties($propertyTypes), $reflectedClass->getDefaultProperties());
    $this->getInjectableMethods($reflectedClass->getMethods());
  }

  protected function getInjectableProperties($classProperties, $defaults) {
    foreach ($classProperties as $reflectedProperty) {
      $inject = $this->annotationReader->getPropertyAnnotation($reflectedProperty, Inject::CLASS);
      if ($inject) {
        $name = $reflectedProperty->getName();
        $namedProperty= $this->extractNamedProperty($this->annotationReader, $reflectedProperty);
        $providerProperty= $this->extractProviderProperty($this->annotationReader, $reflectedProperty);
        $propertyMeta = new InjectionParameter($name, $reflectedProperty);
        if ($namedProperty) $propertyMeta->setAlias($namedProperty);
        else if ($providerProperty) $propertyMeta->setProvides($providerProperty);
        else if ($inject->value) $propertyMeta->setInterface($inject->value);
        // can't distinguish between actual null assignment and PHP default so it will always have a default
        // TODO: can this be improved?
        $propertyMeta->setDefaultValue($defaults[$name]);
        $this->injectableProperties[$name] = $propertyMeta;
      }
    }
  }

  protected function extractNamedProperty(Reader $annotationReader, \ReflectionProperty $reflectedProperty) {
    $namedPropertyAnnotation = $annotationReader->getPropertyAnnotation($reflectedProperty, Named::CLASS);
    if ($namedPropertyAnnotation) return $namedPropertyAnnotation->value;
  }

  protected function extractProviderProperty(Reader $annotationReader, \ReflectionProperty $reflectedProperty) {
    $providerPropertyAnnotation = $annotationReader->getPropertyAnnotation($reflectedProperty, Provides::CLASS);
    if ($providerPropertyAnnotation) return $providerPropertyAnnotation->value;
  }

  protected function getInjectableMethods($classMethods) {
    foreach ($classMethods as $reflectedMethod) {
      $inject = $this->annotationReader->getMethodAnnotation($reflectedMethod, Inject::CLASS);
      if ($inject) {
        $parameters = [];
        $namedParameters = $this->extractNamedParameters($this->annotationReader, $reflectedMethod);
        $assistedParameters = $this->extractAssistedParameters($this->annotationReader, $reflectedMethod);
        $providerParameters = $this->extractProviderParameters($this->annotationReader, $reflectedMethod);
        foreach ($reflectedMethod->getParameters() as $parameter) {
          $name = $parameter->getName();
          $injectionParameter = new InjectionParameter($name, $parameter);
          if (isset($assistedParameters[$name])) {
            if ($reflectedMethod->getName() !== '__construct') throw new \Exception('Assisted injection not possible for setters');
            $injectionParameter->setIsAssisted(true);
          }
          else if (isset($namedParameters[$name])) $injectionParameter->setAlias($namedParameters[$name]);
          else if (isset($providerParameters[$name])) $injectionParameter->setProvides($providerParameters[$name]);
          else if ($class = $this->getParameterClassName($parameter)) $injectionParameter->setInterface($class);
          if ($parameter->isDefaultValueAvailable()) $injectionParameter->setDefaultValue($parameter->getDefaultValue());
          $parameters[] = $injectionParameter;
        }
        if ($parameters) $this->injectableMethods[$reflectedMethod->getName()] = $parameters;
      }
    }
  }

  // returns the parameter's class name without throwing an exception if the class doesn't exist
  protected function getParameterClassName(\ReflectionParameter $param) {
    preg_match('/> ([^ ]+) /', $param->__toString(), $matches);
    return (!in_array($matches[1], ['$' . $param->getName(), 'array'], true)) ? $matches[1] : null;
  }

  protected function extractNamedParameters(Reader $annotationReader, \ReflectionMethod $reflectedMethod) {
    $namedParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, Named::CLASS);
    return ($namedParametersAnnotations) ? (array)$namedParametersAnnotations->value : [];
  }

  protected function extractProviderParameters(Reader $annotationReader, \ReflectionMethod $reflectedMethod) {
    $providerParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, Provides::CLASS);
    return ($providerParametersAnnotations) ? (array)$providerParametersAnnotations->value : [];
  }

  protected function extractAssistedParameters(Reader $annotationReader, \ReflectionMethod $reflectedMethod) {
    $assistedParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, Assisted::CLASS);
    $assistedParameters = [];
    if ($assistedParametersAnnotations) {
      foreach ((array)$assistedParametersAnnotations->value as $assistedParameter) $assistedParameters[$assistedParameter] = true;
    }
    return $assistedParameters;
  }

  public function hasInjectableConstructor() {
    return isset($this->injectableMethods['__construct']);
  }

  public function injectableConstructor() {
    if (!$this->hasInjectableConstructor()) throw new \Exception('No injectable constructor found');
    return $this->injectableMethods['__construct'];
  }

  public function injectableSetters() {
    return array_diff_key($this->injectableMethods, array_flip(['__construct']));
  }

  public function injectableProperties() {
    return $this->injectableProperties;
  }

}
