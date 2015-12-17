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

/**
 * Collects meta-information for a single dependency of an injectable method.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class InjectionParameter {

  protected $name;
  protected $interface;
  protected $alias;
  protected $isAssisted = false;
  protected $isOptional = false;
  protected $default;
  protected $provides;

  public function __construct($name) {
    $this->name = $name;
  }

  public function name() {
    return $this->name;
  }

  public function setIsAssisted($isAssisted) {
    $this->isAssisted = $isAssisted;
  }

  public function isAssisted() {
    return $this->isAssisted;
  }

  public function setInterface($interface) {
    $this->interface = $interface;
  }

  public function setAlias($alias) {
    $this->alias = $alias;
  }

  public function isOptional() {
    return $this->isOptional;
  }

  public function setDefaultValue($default) {
    $this->default = $default;
    $this->isOptional = true;
  }

  public function defaultValue() {
    return $this->default;
  }

  public function setProvides($provides) {
    $this->provides = $provides;
  }

  public function isProvider() {
    return !!$this->provides;
  }

  public function provides() {
    return $this->provides;
  }

  public function type() {
    if ($this->alias !== null) return $this->alias;
    if ($this->interface !== null) return $this->interface;
    return $this->name;
  }

}
