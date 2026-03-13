<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use ACSEO\PrestashopMigrationPlugin\Importer\ImporterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PrestashopMigrationCheckCommand extends Command
{
    private EntityManagerInterface $entityManager;

    /** @var iterable<ImporterInterface> */
    private iterable $importers;

    private array $entityMapping = [
        'locale' => [
            'sylius_entity' => 'App\Entity\Locale\Locale',
            'label' => 'Locales/Languages',
            'command' => 'prestashop:migration:locale',
        ],
        'currency' => [
            'sylius_entity' => 'App\Entity\Currency\Currency',
            'label' => 'Currencies',
            'command' => 'prestashop:migration:currency',
        ],
        'country' => [
            'sylius_entity' => 'App\Entity\Addressing\Country',
            'label' => 'Countries',
            'command' => 'prestashop:migration:country',
        ],
        'zone' => [
            'sylius_entity' => 'App\Entity\Addressing\Zone',
            'label' => 'Zones',
            'command' => 'prestashop:migration:zone',
        ],
        'tax_category' => [
            'sylius_entity' => 'App\Entity\Taxation\TaxCategory',
            'label' => 'Tax Categories',
            'command' => 'prestashop:migration:tax_category',
        ],
        'tax_rate' => [
            'sylius_entity' => 'App\Entity\Taxation\TaxRate',
            'label' => 'Tax Rates',
            'command' => 'prestashop:migration:tax_rate',
        ],
        'taxon' => [
            'sylius_entity' => 'App\Entity\Taxonomy\Taxon',
            'label' => 'Taxons/Categories',
            'command' => 'prestashop:migration:taxon',
        ],
        'product_option' => [
            'sylius_entity' => 'App\Entity\Product\ProductOption',
            'label' => 'Product Options',
            'command' => 'prestashop:migration:product_option',
        ],
        'product_option_value' => [
            'sylius_entity' => 'App\Entity\Product\ProductOptionValue',
            'label' => 'Product Option Values',
            'command' => 'prestashop:migration:product_option_value',
        ],
        'product' => [
            'sylius_entity' => 'App\Entity\Product\Product',
            'label' => 'Products',
            'command' => 'prestashop:migration:product',
        ],
        'product_variant' => [
            'sylius_entity' => 'App\Entity\Product\ProductVariant',
            'label' => 'Product Variants',
            'command' => 'prestashop:migration:product_variant',
        ],
        'customer' => [
            'sylius_entity' => 'App\Entity\Customer\Customer',
            'label' => 'Customers',
            'command' => 'prestashop:migration:customer',
        ],
        'address' => [
            'sylius_entity' => 'App\Entity\Addressing\Address',
            'label' => 'Addresses',
            'command' => 'prestashop:migration:address',
        ],
        'admin_user' => [
            'sylius_entity' => 'App\Entity\User\AdminUser',
            'label' => 'Admin Users',
            'command' => 'prestashop:migration:admin_user',
        ],
    ];

    public function __construct(EntityManagerInterface $entityManager, iterable $importers)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->importers = $importers;
    }

    protected function configure(): void
    {
        $this
            ->setName('prestashop:migration:check')
            ->setDescription('Check migration status between PrestaShop and Sylius');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PrestaShop to Sylius Migration Status Check');

        $rows = [];
        $totalPrestashop = 0;
        $totalSylius = 0;
        $totalMigrated = 0;
        $hasIssues = false;

        // Build importer map by name
        $importerMap = [];
        foreach ($this->importers as $importer) {
            $importerMap[$importer->getName()] = $importer;
        }

        foreach ($this->entityMapping as $key => $config) {
            try {
                // Get importer for this entity type
                $importerName = 'prestashop.importer.' . $key;

                if (!isset($importerMap[$importerName])) {
                    $rows[] = [
                        $config['label'],
                        '<comment>N/A</comment>',
                        '<comment>N/A</comment>',
                        '<comment>N/A</comment>',
                        '<comment>N/A</comment>',
                        '-',
                    ];
                    continue;
                }

                $importer = $importerMap[$importerName];

                // Count PrestaShop entities using the collector
                $prestashopCount = $importer->size();
                $totalPrestashop += $prestashopCount;

                // Count Sylius entities
                $syliusCount = $this->countSyliusEntities($config['sylius_entity']);
                $totalSylius += $syliusCount;

                // Count migrated entities (those with prestashop_id)
                $migratedCount = $this->countMigratedEntities($config['sylius_entity']);
                $totalMigrated += $migratedCount;

                // Determine status
                $status = $this->getStatus($prestashopCount, $migratedCount);
                $isOk = str_contains($status, 'OK') || str_contains($status, 'N/A');

                if (!$isOk) {
                    $hasIssues = true;
                }

                // Add command to run if not OK
                $commandToRun = $isOk ? '-' : sprintf('<comment>php bin/console %s</comment>', $config['command']);

                $rows[] = [
                    $config['label'],
                    $prestashopCount,
                    $syliusCount,
                    $migratedCount,
                    $status,
                    $commandToRun,
                ];

            } catch (\Exception $e) {
                $rows[] = [
                    $config['label'],
                    '<error>ERROR</error>',
                    '<error>ERROR</error>',
                    '<error>ERROR</error>',
                    '<error>ERROR</error>',
                    sprintf('<comment>php bin/console %s</comment>', $config['command']),
                ];
                $hasIssues = true;

                if ($output->isVerbose()) {
                    $io->writeln(sprintf('<comment>Error checking %s: %s</comment>', $config['label'], $e->getMessage()));
                }
            }
        }

        // Display table
        $table = new Table($output);
        $table->setHeaders([
            'Entity Type',
            'PrestaShop',
            'Sylius Total',
            'Migrated',
            'Status',
            'Command to Run',
        ]);
        $table->setRows($rows);

        // Add footer with totals
        $table->addRow([
            '<info>TOTAL</info>',
            "<info>$totalPrestashop</info>",
            "<info>$totalSylius</info>",
            "<info>$totalMigrated</info>",
            '',
            $hasIssues ? '<comment>php bin/console prestashop:migration:all</comment>' : '-'
        ]);

        $table->render();

        $io->newLine();

        // Summary
        if (!$hasIssues && $totalPrestashop === $totalMigrated) {
            $io->success(sprintf(
                'Migration is complete! All %d entities from PrestaShop have been migrated to Sylius.',
                $totalPrestashop
            ));
        } elseif ($totalMigrated === 0) {
            $io->warning('No migration has been performed yet.');
            $io->newLine();
            $io->section('Recommended Action');
            $io->writeln('  Run the complete migration:');
            $io->writeln('  <comment>php bin/console prestashop:migration:all</comment>');
        } else {
            $io->warning(sprintf(
                'Migration is incomplete. %d/%d entities have been migrated (%d%%).',
                $totalMigrated,
                $totalPrestashop,
                $totalPrestashop > 0 ? round(($totalMigrated / $totalPrestashop) * 100) : 0
            ));

            // Collect missing commands
            $missingCommands = [];
            foreach ($rows as $row) {
                if ($row[4] !== '<info>OK</info>' && $row[4] !== '<fg=gray>N/A</>' && $row[5] !== '-') {
                    $commandText = strip_tags($row[5]);
                    if (!in_array($commandText, $missingCommands)) {
                        $missingCommands[] = $commandText;
                    }
                }
            }

            if (!empty($missingCommands)) {
                $io->newLine();
                $io->section('Recommended Actions');
                $io->writeln('  Run the following commands to complete the migration:');
                $io->newLine();
                foreach ($missingCommands as $cmd) {
                    $io->writeln('  <comment>' . $cmd . '</comment>');
                }
                $io->newLine();
                $io->writeln('  Or run the complete migration:');
                $io->writeln('  <comment>php bin/console prestashop:migration:all</comment>');
            }
        }

        // Legend
        $io->section('Status Legend');
        $io->writeln('  <info>OK</info>        - All PrestaShop entities migrated');
        $io->writeln('  <comment>PARTIAL</comment>  - Some entities migrated, some missing');
        $io->writeln('  <error>MISSING</error>   - No entities migrated');
        $io->writeln('  <fg=cyan>EXTRA</>    - More Sylius entities than PrestaShop (manual additions)');

        return $hasIssues ? Command::FAILURE : Command::SUCCESS;
    }

    private function countSyliusEntities(string $entityClass): int
    {
        try {
            $repository = $this->entityManager->getRepository($entityClass);
            return (int) $repository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function countMigratedEntities(string $entityClass): int
    {
        try {
            $repository = $this->entityManager->getRepository($entityClass);
            return (int) $repository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.prestashopId IS NOT NULL')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getStatus(int $prestashopCount, int $migratedCount): string
    {
        if ($prestashopCount === 0 && $migratedCount === 0) {
            return '<fg=gray>N/A</>';
        }

        if ($migratedCount === 0) {
            return '<error>MISSING</error>';
        }

        if ($migratedCount >= $prestashopCount) {
            return '<info>OK</info>';
        }

        if ($migratedCount > 0 && $migratedCount < $prestashopCount) {
            return '<comment>PARTIAL</comment>';
        }

        return '<fg=cyan>EXTRA</>';
    }
}
