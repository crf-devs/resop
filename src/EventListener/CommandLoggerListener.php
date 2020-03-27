<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Monolog\Processor\CommandProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CommandLoggerListener implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private CommandProcessor $commandProcessor;

    public function __construct(LoggerInterface $logger, CommandProcessor $commandProcessor)
    {
        $this->logger = $logger;
        $this->commandProcessor = $commandProcessor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 255],
            ConsoleEvents::ERROR => ['onError', -127],
            ConsoleEvents::TERMINATE => ['onTerminate', -129],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        if (null === $command = $event->getCommand()) {
            return;
        }

        $this->commandProcessor->setCurrentCommand($command, $event->getInput());
        $this->logger->info('Command started');
    }

    public function onError(ConsoleErrorEvent $event): void
    {
        $this->commandProcessor->setCurrentCommand($event->getCommand(), $event->getInput());
        $this->logger->error('Command exception', ['exception' => $event->getError()]);
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $this->commandProcessor->unsetCurrentCommand();
        $exitCode = $event->getExitCode();

        if (0 === $exitCode) {
            return;
        }

        $this->logger->error('Command exited with error', ['code' => $exitCode]);
    }
}
