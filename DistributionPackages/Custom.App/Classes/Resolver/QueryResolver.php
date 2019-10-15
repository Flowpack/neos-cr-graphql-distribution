<?php

declare(strict_types=1);

namespace Custom\App\Resolver;

use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\Flow\Annotations as Flow;
use t3n\GraphQL\ResolverInterface;

class QueryResolver implements ResolverInterface
{
    /**
     * @Flow\Inject
     *
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     *
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     *
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject
     *
     * @var ContextFactory
     */
    protected $contextFactory;

    public function nodeByPath($_, array $args): ?NodeInterface
    {
        $workspace = $this->workspaceRepository->findByIdentifier('live');
        $nodeData = $this->nodeDataRepository->findOneByPath($args['path'], $workspace);

        if ($nodeData === null) {
            return null;
        }

        $context = $this->contextFactory->create([]);

        // get context from context class
        return $this->nodeFactory->createFromNodeData($nodeData, $context);
    }
}
