<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Command;

use ACSEO\PrestashopMigrationPlugin\Importer\ImporterInterface;
use ACSEO\PrestashopMigrationPlugin\Validator\Violation;
use Sylius\Component\Core\Formatter\StringInflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResourceCommand extends Command
{
    private string $name;

    private ImporterInterface $importer;

    public function __construct(string $name, ImporterInterface $importer)
    {
        parent::__construct();

        $this->name = ucfirst(StringInflector::nameToCamelCase($name));
        $this->importer = $importer;
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate the migration without writing to database');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('DRY RUN MODE - No data will be written to the database');
        }

        $io->title(sprintf('Start migration of "%s"', $this->name));

        $progressBar = new ProgressBar($output, $this->importer->size());
        $progressBar->setFormat('%percent:3s%% [%bar%] %elapsed:6s%/%estimated:-6s%');

        $persisted = 0;
        $skipped = 0;

        $this->importer->import(function (int $step, array $violations, bool $isDryRun) use ($progressBar, $io, &$persisted, &$skipped) {

            $violationCount = 0;
            array_walk_recursive($violations,
                function (Violation $violation) use ($io, &$violationCount) {
                    $io->warning([
                        sprintf('%s %s not import', $this->name, $violation->getEntityId()),
                        sprintf('Reason : %s', $violation->getMessage())
                    ]);
                    $violationCount++;
                }
            );

            $persisted += ($step - $violationCount);
            $skipped += $violationCount;

            $progressBar->advance($step);
        }, $dryRun);

        $progressBar->finish();

        $io->newLine(2);

        if ($dryRun) {
            $io->success(sprintf('[DRY RUN] Migration simulated successfully - %d entities would be created/updated, %d skipped', $persisted, $skipped));
        } else {
            $io->success(sprintf('Migration successful - %d entities created/updated, %d skipped', $persisted, $skipped));
        }

        $io->writeln('---------------------------------------------------------------------------');

        return Command::SUCCESS;
    }
}
