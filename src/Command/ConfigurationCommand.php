<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Command;

use ACSEO\PrestashopMigrationPlugin\Configurator\ConfiguratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigurationCommand extends Command
{
    /** @var ConfiguratorInterface[] */
    private array $configurators;

    public function __construct(iterable $configurators)
    {
        $this->configurators = $configurators instanceof \Traversable ? iterator_to_array($configurators) : $configurators;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate the configuration without executing');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('DRY RUN MODE - Configuration will be simulated only');
        }

        if ($input->getOption('no-interaction') === false && !$dryRun) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Are you sure you want to automatically configure the store ? This can have serious repercussions on a production site. (N/y) ', false);

            if (!$helper->ask($input, $output, $question)) {
                $output->write('<error>Configuration abort !</error>');

                return Command::FAILURE;
            }
        }

        foreach ($this->configurators as $configurator) {

            $io->title(sprintf('Start configuration of "%s"', $configurator->getName()));

            if ($dryRun) {
                $io->writeln(sprintf('[DRY RUN] Would execute configuration: %s', $configurator->getName()));
                $io->success('[DRY RUN] Configuration simulated');
            } else {
                $configurator->execute();
                $io->success('Configuration successful');
            }

            $io->writeln('---------------------------------------------------------------------------');
        }
        return Command::SUCCESS;
    }
}
