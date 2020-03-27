<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class CommandProcessor implements ProcessorInterface
{
    private ?Command $command = null;
    private ?InputInterface $input = null;

    public function setCurrentCommand(?Command $command, ?InputInterface $input): void
    {
        $this->command = $command;
        $this->input = $input;
    }

    public function getCurrentCommand(): ?Command
    {
        return $this->command;
    }

    public function getInput(): ?InputInterface
    {
        return $this->input;
    }

    public function unsetCurrentCommand(): void
    {
        $this->command = null;
    }

    public function __invoke(array $record): array
    {
        if (null === $this->command || null === $this->input) {
            return $record;
        }

        $record['extra'] += [
            'command_name' => $this->command->getName(),
            'command_arguments' => \json_encode($this->input->getArguments()),
        ];

        return $record;
    }
}
