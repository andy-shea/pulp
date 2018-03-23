<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp;

use Doctrine\Common\Annotations\Reader;

/**
 * Builds an `Injector` from the binding definitions supplied by the given
 * `Module`s.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InjectorBuilder {

  protected $annotationReader;
  protected $modules = [];

  public function __construct(Reader $annotationReader) {
    $this->annotationReader = $annotationReader;
  }

  public function addModules(array $modules) {
    $this->modules = array_merge($this->modules, $modules);
    return $this;
  }

  public function build() {
    $binder = new Binding\Binder($this->annotationReader);
    foreach ($this->modules as $module) $binder->install($module);
    return new Injector($binder, $this->annotationReader);
  }

}
