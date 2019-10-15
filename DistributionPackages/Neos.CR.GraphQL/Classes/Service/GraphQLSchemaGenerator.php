<?php

declare(strict_types=1);

namespace Neos\CR\GraphQL\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope(type="singleton")
 */
class GraphQLSchemaGenerator
{
    /**
     * @Flow\Inject
     *
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    protected $types = [];

    protected $nodeTypeProperties = [
        'identifier' => 'ID!',
        'path' => 'String!',
        'parentPath' => 'String!',
        'name' => 'String!',
        'workspace' => 'Workspace!',
        'context' => 'Context!'
    ];

    /**
     * Resets the current type configuration
     */
    public function reset(): void
    {
        $this->types = [];
    }

    /**
     * Returns the graphql schema type name for a given NodeType
     */
    public function getTypeNameFromNodeType(NodeType $nodeType): string
    {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $nodeType->getName());
    }

    /**
     * Generate the Schema and returns the sdl
     */
    public function generate(): string
    {
        foreach ($this->getAllNodeTypes() as $nodeType) {
            $this->addNodeTypeProperties($nodeType);
        }

        $types = $this->generateTypeDefinition();
        return $types;
    }

    /**
     * @return NodeType[]
     */
    public function getAllNodeTypes(): array
    {
        $allNodeTypes = $this->nodeTypeManager->getNodeTypes(false);
        $nodeTypes = [];

        foreach ($allNodeTypes as $nodeType) {
            /** @var NodeType $nodeType */
            // todo filter more ore less?!
            if ($nodeType->getName() !== 'unstructured') {
                $nodeTypes[] = $nodeType;
            }
        }
        return $nodeTypes;
    }

    /**
     * Register all properties from a given NodeType to the SchemaGenerator.
     * The Type will only be registered if it has properties configured at all
     */
    private function addNodeTypeProperties(NodeType $nodeType): void
    {
        if (count($nodeType->getProperties()) === 0) {
            return;
        }

        $type = [];
        foreach ($nodeType->getProperties() as $name => $property) {
            // todo Filter some types ?!
            $type[$name] = $nodeType->getPropertyType($name);
        }

        $name = $this->getTypeNameFromNodeType($nodeType);
        $this->types[$name] = $type;
    }

    private function generateTypeDefinition(): string
    {
        $output = '';

        // Add all Property Types
        foreach ($this->types as $typeName => $type) {
            $output .= sprintf('type %sProperties {', $typeName);

            foreach ($this->types[$typeName] as $k => $v) {
                $output .= sprintf('
    %s: %s', $k, $this->mapType($v));
            }

            $output .= '
}
';
        }

        // Add all node types
        foreach ($this->types as $typeName => $type) {
            $output .= sprintf('type %s implements Node {', $typeName);

            foreach ($this->nodeTypeProperties as $k => $v) {
                $output .= sprintf('
    %s: %s', $k, $this->mapType($v));
            }

            $output .= sprintf('
    properties: %sProperties!
}

', $typeName);
        }

        return $output;
    }

    private function mapType(string $rawType): string
    {
        // todo read mapping from Configuration
        switch ($rawType) {
            case 'string':
                return 'String';
            default:
                return $rawType;
        }
    }
}
