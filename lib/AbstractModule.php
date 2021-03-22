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
use Pulp\Binding\Binding;
use Assisted\FactoryProvider;

/**
 * A support class for `Module`s to ease implementation by reducing repetition.
 * Mirrors Binder functions for a more readable configuration.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
abstract class AbstractModule implements Module {

  protected Binder $binder;

  public function setBinder(Binder $binder): void {
    $this->binder = $binder;
  }

  protected function bind(string $interface): Binding {
    return $this->binder->bind($interface);
  }

  protected function install(FactoryProvider $factoryProvider): void {
    $this->binder->installFactoryProvider($factoryProvider);
  }

}
