<?php

declare(strict_types=1);

namespace App\Monolog\Handler;

use App\Monolog\Processor\CommandProcessor;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * This class dumps all logs to stderr.
 * For commands, logs are dumped only if the `-vvv` flag is given.
 * If APP_DEBUG is true, all logs are dumped.
 * If APP_DEBUG is false, only INFO, ERROR and CRITICAL logs are dumped.
 */
class DockerHandler extends AbstractProcessingHandler
{
    private CommandProcessor $commandProcessor;
    private StreamHandler $stderrHandler;
    private NormalizerFormatter $logsNormalizer;
    private JsonFormatter $logsFormater;
    private bool $hideDeprecated;

    public function __construct(CommandProcessor $commandProcessor, bool $isDebug)
    {
        $level = $isDebug ? Logger::DEBUG : Logger::INFO;
        $this->hideDeprecated = !$isDebug;
        $this->commandProcessor = $commandProcessor;
        $this->stderrHandler = new StreamHandler('php://stderr', $level, true, null, true);
        $this->logsNormalizer = new NormalizerFormatter('c');
        $this->logsFormater = new JsonFormatter();
        $this->logsFormater->includeStacktraces(false); // Not kibana compliant

        parent::__construct($level);
    }

    private function isCommandWithoutVerbose(): bool
    {
        return $this->isCommand() && (null === $this->commandProcessor->getInput() || !$this->commandProcessor->getInput()->hasParameterOption(['-vvv']));
    }

    private function isCommand(): bool
    {
        return \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true);
    }

    private function isDeprecatedLog(array $record): bool
    {
        return 0 === \strpos($record['message'] ?? '', 'User Deprecated');
    }

    protected function write(array $record): void
    {
        if ($record['level'] < Logger::CRITICAL && $this->isCommandWithoutVerbose()) {
            // No log for commands without -vvv
            return;
        }

        if ($this->hideDeprecated && $this->isDeprecatedLog($record)) {
            return;
        }

        if (!is_string($record['formatted'])) {
            // TODO Create a custom formatted doing NormalizerFormatter::format and JsonFormatter::format
            $record['formatted'] = $this->logsFormater->format($record['formatted']);
        }

        $this->stderrHandler->write($record);
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->logsNormalizer;
    }
}
