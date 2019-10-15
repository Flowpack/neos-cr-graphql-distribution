<?php

declare(strict_types=1);

namespace Neos\CR\GraphQL\Command;

use Neos\CR\GraphQL\Service\GraphQLSchemaGenerator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class SchemaGeneratorCommandController extends CommandController
{
    /**
     * @Flow\Inject
     *
     * @var GraphQLSchemaGenerator
     */
    protected $schemaGenerator;

    public function generateCommand(): void
    {
        // reset current state
        $this->schemaGenerator->reset();
        $output = $this->schemaGenerator->generate();

        if (! is_dir(FLOW_PATH_DATA . 'SchemaGenerator')) {
            mkdir(FLOW_PATH_DATA . 'SchemaGenerator');
        }

        file_put_contents(FLOW_PATH_DATA . 'SchemaGenerator/types.graphql', $output);
    }
}
