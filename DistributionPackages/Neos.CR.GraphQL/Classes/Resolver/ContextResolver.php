<?php

declare(strict_types=1);

namespace Neos\CR\GraphQL\Resolver;

use Neos\ContentRepository\Domain\Service\Context;
use t3n\GraphQL\ResolverInterface;

class ContextResolver implements ResolverInterface
{
    public function dimensions(Context $context)
    {
        return [];
    }
}
