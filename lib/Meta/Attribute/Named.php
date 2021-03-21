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
 * Allows dependency key to be overriden with the specified name.
 * Used to distinguish between variations of the same type of dependency.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Named {

  protected string $type;

  public static function extractNamedType(Reflector $reflected): ?string {
    $namedAttributes = $reflected->getAttributes(__CLASS__);
    if ($namedAttributes) {
      $namedAttribute = $namedAttributes[0]->newInstance();
      return $namedAttribute->getType();
    }
    return null;
  }

  public function __construct(string $type) {
    $this->type = $type;
  }

  public function getType(): string {
    return $this->type;
  }

}
