<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Order;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Order\OrderModel;
use ACSEO\PrestashopMigrationPlugin\Repository\Order\OrderRepository;
use ACSEO\PrestashopMigrationPlugin\Resolver\OrderStateResolver;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class OrderResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    private RepositoryInterface $customerRepository;

    private RepositoryInterface $addressRepository;

    private RepositoryInterface $currencyRepository;

    private RepositoryInterface $channelRepository;

    private RepositoryInterface $productRepository;

    private RepositoryInterface $productVariantRepository;

    private RepositoryInterface $localeRepository;

    private RepositoryInterface $orderItemRepository;

    /** @var OrderRepository $orderRepository */
    private OrderRepository $orderRepository;

    private FactoryInterface $orderItemFactory;

    private FactoryInterface $orderItemUnitFactory;

    private OrderStateResolver $orderStateResolver;

    public function __construct(
        ResourceTransformerInterface $transformer,
        RepositoryInterface          $customerRepository,
        RepositoryInterface          $addressRepository,
        RepositoryInterface          $currencyRepository,
        RepositoryInterface          $channelRepository,
        RepositoryInterface          $productRepository,
        RepositoryInterface          $productVariantRepository,
        RepositoryInterface          $localeRepository,
        RepositoryInterface          $orderItemRepository,
        OrderRepository              $orderRepository,
        FactoryInterface             $orderItemFactory,
        FactoryInterface             $orderItemUnitFactory,
        OrderStateResolver           $orderStateResolver,
    ) {
        $this->transformer = $transformer;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->currencyRepository = $currencyRepository;
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->localeRepository = $localeRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemUnitFactory = $orderItemUnitFactory;
        $this->orderStateResolver = $orderStateResolver;
    }

    /**
     * @param OrderModel $model
     */
    public function transform(ModelInterface $model): ResourceInterface
    {
        /** @var OrderInterface $order */
        $order = $this->transformer->transform($model);

        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->findOneBy(['prestashopId' => $model->customerId]);

        /** @var AddressInterface|null $shippingAddress */
        $shippingAddress = $this->addressRepository->findOneBy(['prestashopId' => $model->deliveryAddressId]);

        /** @var AddressInterface|null $billingAddress */
        $billingAddress = $this->addressRepository->findOneBy(['prestashopId' => $model->invoiceAddressId]);

        $currency = $this->currencyRepository->findOneBy(['prestashopId' => $model->currencyId]);
        $channel = $this->channelRepository->findOneBy(['prestashopId' => $model->shopId]);
        $locale = $this->localeRepository->findOneBy(['prestashopId' => $model->langId]);

        if (null !== $customer) {
            $order->setCustomer($customer);
        }

        if (null !== $shippingAddress) {
            $order->setShippingAddress(clone $shippingAddress);
        }

        if (null !== $billingAddress) {
            $order->setBillingAddress(clone $billingAddress);
        }

        if (null !== $currency) {
            $order->setCurrencyCode($currency->getCode());
        }

        if (null !== $channel) {
            $order->setChannel($channel);
        }

        if (null !== $locale) {
            $order->setLocaleCode($locale->getCode());
        }

        // Set order states based on PrestaShop current_state
        $order->setState($this->orderStateResolver->resolveOrderState($model->currentState));
        $order->setCheckoutState($this->orderStateResolver->resolveCheckoutState($model->currentState));
        $order->setPaymentState($this->orderStateResolver->resolvePaymentState($model->currentState));
        $order->setShippingState($this->orderStateResolver->resolveShippingState($model->currentState));

        $order->setNumber($model->reference ?? (string) $model->id);
        $order->setNotes(null);

        // Import basic items
        $items = $this->orderRepository->getOrderItems($model->id);

        foreach ($items as $itemRow) {
            $orderDetailId = (int) $itemRow['id_order_detail'];

            // Check if OrderItem already exists
            /** @var OrderItemInterface|null $orderItem */
            $orderItem = $this->orderItemRepository->findOneBy(['prestashopId' => $orderDetailId]);

            if (null === $orderItem) {
                $orderItem = $this->orderItemFactory->createNew();
            } else {
                // Remove existing units to avoid duplicates
                foreach ($orderItem->getUnits() as $unit) {
                    $orderItem->removeUnit($unit);
                }
            }

            $productId = (int) $itemRow['product_id'];
            $productAttributeId = (int) ($itemRow['product_attribute_id'] ?? 0);

            /** @var ProductVariantInterface|null $variant */
            $variant = null;

            if ($productAttributeId > 0) {
                $variant = $this->productVariantRepository->findOneBy(['prestashopId' => $productAttributeId]);
            }

            if (null === $variant) {
                $product = $this->productRepository->findOneBy(['prestashopId' => $productId]);
                if ($product instanceof \Sylius\Component\Core\Model\ProductInterface) {
                    $variant = $product->getVariants()->first() ?: null;
                }
            }

            if (null === $variant) {
                continue;
            }

            $orderItem->setVariant($variant);
            $orderItem->setUnitPrice((int) round($itemRow['unit_price_tax_incl'] * 100));
            $orderItem->setPrestashopId($orderDetailId);

            // Add order item units for the quantity
            $quantity = (int) $itemRow['product_quantity'];
            for ($i = 0; $i < $quantity; $i++) {
                $orderItemUnit = $this->orderItemUnitFactory->createForItem($orderItem);
                $orderItem->addUnit($orderItemUnit);
            }

            $order->addItem($orderItem);
        }

        return $order;
    }
}

