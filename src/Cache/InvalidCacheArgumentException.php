<?php

declare(strict_types=1);

namespace Skionline\MerlinxGetter\Cache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

final class InvalidCacheArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
}
