<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use ACSEO\PrestashopMigrationPlugin\Downloader\ImageDownloader;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use ACSEO\PrestashopMigrationPlugin\Repository\Product\ProductRepository;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class PrestashopMigrationImageCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private RepositoryInterface $resourceRepository;

    /**
     * @var ProductRepository $entityRepository
     */
    private EntityRepositoryInterface $entityRepository;

    private ImageDownloader $downloader;

    private FactoryInterface $productImageFactory;

    private ImageUploaderInterface $imageUploader;

    public function __construct(
        EntityManagerInterface    $entityManager,
        RepositoryInterface       $resourceRepository,
        EntityRepositoryInterface $entityRepository,
        ImageDownloader           $downloader,
        FactoryInterface          $productImageFactory,
        ImageUploaderInterface    $imageUploader
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->resourceRepository = $resourceRepository;
        $this->entityRepository = $entityRepository;
        $this->downloader = $downloader;
        $this->productImageFactory = $productImageFactory;
        $this->imageUploader = $imageUploader;
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
            $io->note('DRY RUN MODE - No images will be downloaded or written to the database');
        }

        $io->title('Start migration of product images');

        $products = $this->resourceRepository->findAll();

        $progressBar = new ProgressBar($output, count($products));

        $imageCount = 0;

        foreach ($products as $product) {
            if (!$product->getPrestashopId()) {
                continue;
            }

            $images = $this->entityRepository->getImages($product->getPrestashopId());

            if ($dryRun) {
                $imageCount += count($images);
                $io->writeln(sprintf('[DRY RUN] Would process %d images for product #%d', count($images), $product->getPrestashopId()));
            } else {
                foreach ($product->getImages() as $productImage) {
                    $product->removeImage($productImage);
                }

                foreach ($images as $image) {

                    $path = $this->downloader->download((int)$image['id_image']);

                    if (null !== $path) {
                        /** @var ProductImageInterface $productImage */
                        $productImage = $this->productImageFactory->createNew();
                        $productImage->setFile(new UploadedFile($path, basename($path)));

                        $this->imageUploader->upload($productImage);
                        $product->addImage($productImage);
                        $imageCount++;
                    }
                }

                $this->entityManager->flush();
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->newLine(2);

        if ($dryRun) {
            $io->success(sprintf('[DRY RUN] Migration simulated successfully - %d images would be processed', $imageCount));
        } else {
            $io->success(sprintf('Migration successful - %d images processed', $imageCount));
        }

        $io->writeln('---------------------------------------------------------------------------');

        return Command::SUCCESS;
    }
}
