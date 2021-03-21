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

use InvalidArgumentException;

/**
 * Exception that is thrown when an invalid scope is requested.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InvalidScopeException extends InvalidArgumentException {

  protected $message = 'Invalid scope specified';

}
