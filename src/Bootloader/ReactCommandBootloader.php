<?php

declare(strict_types=1);

namespace Chiron\React\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\React\Command\ReactServerCommand;

final class ReactCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(ReactServerCommand::getDefaultName(), ReactServerCommand::class);
    }
}
