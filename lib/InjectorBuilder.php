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

use Pulp\Binding\Binder;

/**
 * Builds an `Injector` from the binding definitions supplied by the given
 * `Module`s.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InjectorBuilder {

  protected array $modules = [];

  public function addModules(array $modules): self {
    $this->modules = array_merge($this->modules, $modules);
    return $this;
  }

  public function build(): Injector {
    $binder = new Binder();
    foreach ($this->modules as $module) $binder->install($module);
    return new Injector($binder);
  }

}
