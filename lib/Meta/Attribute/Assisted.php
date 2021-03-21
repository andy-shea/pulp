<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Meta\Attribute;

use Attribute;
use Reflector;

/**
 * Designates a parameter that will not be injected.
 * Used by Pulp in the creation of `FactoryProvider`s.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Assisted {

  public static function isAssisted(Reflector $reflector): bool {
    $assistedAttributes = $reflector->getAttributes(__CLASS__);
    if ($assistedAttributes) {
      // validate attribute
      $assistedAttributes[0]->newInstance();
      return true;
    }
    return false;
  }

}
