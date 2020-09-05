<?php

declare(strict_types=1);

namespace Chiron\React\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Container\FactoryInterface;
use Chiron\React\ReactDispatcher;

final class ReactDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, FactoryInterface $factory): void
    {
        $application->addDispatcher($factory->build(ReactDispatcher::class));
    }
}
