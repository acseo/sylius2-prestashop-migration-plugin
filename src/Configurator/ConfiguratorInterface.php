<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Configurator;

interface ConfiguratorInterface
{
    public function execute(): void;

    public function getName(): string;

    public static function getDefaultPriority(): int;
}
