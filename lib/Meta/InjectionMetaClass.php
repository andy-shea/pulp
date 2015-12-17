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

use Doctrine\Common\Annotations\Reader;

/**
 * Collects meta-information for the class to determine injectable dependencies.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class InjectionMetaClass {

  protected $injectableMethods = [];
  protected $class;
  protected $annotationReader;

  public function __construct($className, Reader $annotationReader) {
    $this->class = $className;
    $this->annotationReader = $annotationReader;
    $reflectedClass = new \ReflectionClass($className);
    $this->getInjectableMethods($reflectedClass->getMethods());
  }

  protected function getInjectableMethods($classMethods) {
    foreach ($classMethods as $reflectedMethod) {
      $inject = $this->annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Inject');
      if ($inject) {
        $parameters = [];
        $namedParameters = $this->extractNamedParameters($this->annotationReader, $reflectedMethod);
        $assistedParameters = $this->extractAssistedParameters($this->annotationReader, $reflectedMethod);
        $providerParameters = $this->extractProviderParameters($this->annotationReader, $reflectedMethod);
        foreach ($reflectedMethod->getParameters() as $parameter) {
          $injectionParameter = new InjectionParameter($parameter->getName());
          if (isset($assistedParameters[$parameter->getName()])) {
            if ($reflectedMethod->getName() !== '__construct') throw new \Exception('Assisted injection not possible for setters');
            $injectionParameter->setIsAssisted(true);
          }
          else if (isset($namedParameters[$parameter->getName()])) $injectionParameter->setAlias($namedParameters[$parameter->getName()]);
          else if (isset($providerParameters[$parameter->getName()])) $injectionParameter->setProvides($providerParameters[$parameter->getName()]);
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
    $namedParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Named');
    return ($namedParametersAnnotations) ? (array)$namedParametersAnnotations->value : [];
  }

  protected function extractProviderParameters(Reader $annotationReader, \ReflectionMethod $reflectedMethod) {
    $providerParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Provides');
    return ($providerParametersAnnotations) ? (array)$providerParametersAnnotations->value : [];
  }

  protected function extractAssistedParameters(Reader $annotationReader, \ReflectionMethod $reflectedMethod) {
    $assistedParametersAnnotations = $annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Assisted');
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

}
