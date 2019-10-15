<?php

declare(strict_types=1);

namespace Neos\CR\GraphQL\Resolver;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use t3n\GraphQL\ResolverInterface;

class NodeResolver implements ResolverInterface
{
    public function __resolveType(NodeInterface $node)
    {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $node->getNodeType()->getName());
    }
}
