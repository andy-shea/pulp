<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Scope;

/**
 * Exception that is thrown when an invalid scope is requested.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class InvalidScopeException extends \InvalidArgumentException {

  protected $message = 'Invalid scope specified';

}
