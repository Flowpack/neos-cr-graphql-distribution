<?php

declare(strict_types=1);

namespace Custom\App\Command;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\ContentSubgraph\NodePath;
use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Utility\Algorithms;

/**
 * @Flow\Scope("singleton")
 */
class FixturesCommandController extends CommandController
{
    /**
     * @Flow\Inject()
     *
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject()
     *
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject()
     *
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @Flow\Inject()
     *
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject()
     *
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    public function insertCommand(): void
    {
        $context = $this->contextFactory->create([]);

        if ($this->workspaceRepository->countByIdentifier('live') === 0) {
            $this->workspaceRepository->add(new Workspace('live'));
        }

        $workspace = $this->workspaceRepository->findByIdentifier('live');
        $allNodeTypes = $this->nodeTypeManager->getNodeTypes(false);

        $nodeTypes = array_filter($allNodeTypes, static function ($nodeType) {
            return $nodeType->getName() !== 'unstructured' && count($nodeType->getProperties()) > 0;
        });

        // Generate some generic nodes
        $this->outputLine('Creating some generic nodes');
        for ($i = 0; $i <= 50; $i++) {
            $identifier = Algorithms::generateUUID();
            $path = NodePath::fromString('/' . $identifier);

            $nodeData = new NodeData($path->__toString(), $workspace, $identifier, []);
            $nodeData->setNodeType($nodeTypes[array_rand($nodeTypes)]);
            $nodeData->setProperty('text', 'this is a text');
            $nodeData->setProperty('source', '<p>Some html</p>');

            $this->nodeDataRepository->update($nodeData);
        }
    }

    public function testCommand(): void
    {
        $resolvers = \t3n\GraphQL\Resolvers::create();
        $resolvers->withGenerator('Neos\CR\GraphQL\Resolver\NodeTypeResolverGenerator');
        $resolvers->toArray();
    }
}
