<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\User;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Customer\CustomerModel;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ShopUserResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    private FactoryInterface $userFactory;

    private RepositoryInterface $customerGroupRepository;

    public function __construct(
        ResourceTransformerInterface $transformer,
        FactoryInterface $userFactory,
        RepositoryInterface $customerGroupRepository
    ) {
        $this->transformer = $transformer;
        $this->userFactory = $userFactory;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * @param CustomerModel $model
     *
     * @return ResourceInterface
     */
    public function transform(ModelInterface $model): ResourceInterface
    {
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->transformer->transform($model);
        $shopUser = $customer->getUser();

        if (null === $shopUser) {
            $shopUser = $this->userFactory->createNew();
        }

        $shopUser->setUsername($customer->getEmail());
        $shopUser->setEnabled($model->enabled);

        $gender = match ($model->gender) {
            1 => CustomerInterface::MALE_GENDER,
            2 => CustomerInterface::FEMALE_GENDER,
            default => CustomerInterface::UNKNOWN_GENDER,
        };


        $customer->setGender($gender);
        $customer->setUser($shopUser);

        if (null !== $model->birthday && $model->birthday !== '0000-00-00') {
            $customer->setBirthday(\DateTime::createFromFormat('Y-m-d', $model->birthday));
        }

        // Set customer group (migrated from PrestaShop id_default_group)
        if ($model->defaultGroupId > 0) {
            $customerGroup = $this->customerGroupRepository->findOneBy(['prestashopId' => $model->defaultGroupId]);
            if (null !== $customerGroup) {
                $customer->setGroup($customerGroup);
            }
        }

        return $customer;
    }

}
