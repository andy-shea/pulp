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

/**
 * An interface for a provider class.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
interface Provider {

  function get(): mixed;

}
