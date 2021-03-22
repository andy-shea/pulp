<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Scope;

use Pulp\Binding\Binding;
use Pulp\Injector;

/**
 * An interface for a scope which defines the life of a dependency.  Custom
 * defined scopes will need to implement this interface.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
interface Scope {

  function getDependency(
    Binding $binding,
    Injector $injector,
    array $assistedParams = null,
    bool $isOptional = false
  ): mixed;

}
