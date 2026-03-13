<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PrestashopMigrationPlugin extends Bundle
{
    use SyliusPluginTrait;
}
