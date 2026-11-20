<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Reader;

use ArrayObject;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

class CustomerGroupCollectionReader implements CustomerGroupCollectionReaderInterface
{
    protected static ?string $cachedCustomerKey = null;

    protected static ?CustomerGroupCollectionTransfer $cachedCustomerGroupCollectionTransfer = null;

    /**
     * @param \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface $customerGroupRepository
     * @param list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupProviderPluginInterface> $customerGroupProviderPlugins
     */
    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository,
        protected array $customerGroupProviderPlugins
    ) {
    }

    public function getCustomerGroupCollectionByCustomer(CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer
    {
        $idCustomer = $customerTransfer->getIdCustomerOrFail();
        $customerKey = sprintf('%d:%s', $idCustomer, $customerTransfer->getCompanyUserTransfer()?->getIdCompanyUser());

        if (static::$cachedCustomerKey !== $customerKey || static::$cachedCustomerGroupCollectionTransfer === null) {
            static::$cachedCustomerGroupCollectionTransfer = $this->resolveCustomerGroupCollection($idCustomer, $customerTransfer);
            static::$cachedCustomerKey = $customerKey;
        }

        return (new CustomerGroupCollectionTransfer())->fromArray(static::$cachedCustomerGroupCollectionTransfer->toArray(), true);
    }

    protected function resolveCustomerGroupCollection(int $idCustomer, CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer
    {
        $customerGroupCollectionTransfer = $this->customerGroupRepository->getCustomerGroupCollectionByIdCustomer($idCustomer);

        foreach ($this->customerGroupProviderPlugins as $customerGroupProviderPlugin) {
            foreach ($customerGroupProviderPlugin->provideCustomerGroups($customerTransfer)->getGroups() as $customerGroupTransfer) {
                $customerGroupCollectionTransfer->addGroup($customerGroupTransfer);
            }
        }

        return $this->removeDuplicateCustomerGroups($customerGroupCollectionTransfer);
    }

    protected function removeDuplicateCustomerGroups(CustomerGroupCollectionTransfer $customerGroupCollectionTransfer): CustomerGroupCollectionTransfer
    {
        $customerGroupTransfersById = [];

        foreach ($customerGroupCollectionTransfer->getGroups() as $customerGroupTransfer) {
            $customerGroupTransfersById[$customerGroupTransfer->getIdCustomerGroupOrFail()] ??= $customerGroupTransfer;
        }

        return $customerGroupCollectionTransfer->setGroups(new ArrayObject(array_values($customerGroupTransfersById)));
    }
}
