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
 * Designates the return type of factory creator methods.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Returns {

  protected string $type;

  public static function extractReturnsType(Reflector $reflected): ?string {
    $returnsAttributes = $reflected->getAttributes(__CLASS__);
    if ($returnsAttributes) {
      $returnsAttribute = $returnsAttributes[0]->newInstance();
      return $returnsAttribute->getType();
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
