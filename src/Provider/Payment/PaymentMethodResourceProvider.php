<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Payment;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Payment\PaymentMethodModel;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Behat\Transliterator\Transliterator;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class PaymentMethodResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;

    private RepositoryInterface $paymentMethodRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $paymentMethodRepository
    ) {
        $this->decorated = $decorated;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        $resource = $this->decorated->getResource($model);

        // If resource was just created (no ID yet), try to find existing by code
        // to avoid duplicates
        if ($resource->getId() === null && $model instanceof PaymentMethodModel) {
            $code = StringInflector::nameToLowercaseCode(
                Transliterator::transliterate($model->name)
            );
            $existing = $this->paymentMethodRepository->findOneBy(['code' => $code]);
            if (null !== $existing) {
                return $existing;
            }
        }

        return $resource;
    }
}
