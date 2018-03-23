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

/**
 * Supplies `Binding` definitions to the `Injector` to build object graphs.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
interface Module {

  function configure();

}
