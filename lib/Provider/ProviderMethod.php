<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Provider;

use Pulp\Module;

/**
 * An implementation of a `Provider` used by Pulp to automatically inject a
 * provider which will return a dependency created by a provider method defined
 * in a `Module`.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 * @todo this will need to be able to support provider methods with parameters
 */
class ProviderMethod implements Provider {

  protected Module $module;
  protected string $methodName;

  public function __construct(Module $module, string $methodName) {
    $this->module = $module;
    $this->methodName = $methodName;
  }

  public function get(): mixed {
    return call_user_func_array(array($this->module, $this->methodName), []);
  }

}
