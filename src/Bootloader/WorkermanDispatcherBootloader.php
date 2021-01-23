<?php

declare(strict_types=1);

namespace Chiron\Workerman\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Container\FactoryInterface;
use Chiron\Workerman\WorkermanDispatcher;

final class WorkermanDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, FactoryInterface $factory): void
    {
        $application->addDispatcher($factory->build(WorkermanDispatcher::class));
    }
}
