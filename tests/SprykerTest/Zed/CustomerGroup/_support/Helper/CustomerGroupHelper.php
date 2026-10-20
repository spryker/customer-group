<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\CustomerGroupBuilder;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class CustomerGroupHelper extends Module
{
    use DataCleanupHelperTrait;
    use LocatorHelperTrait;

    /**
     * @param array<string, mixed> $seed
     */
    public function haveCustomerGroup(array $seed = []): CustomerGroupTransfer
    {
        $customerGroupTransfer = (new CustomerGroupBuilder($seed))->build();

        $customerGroupFacade = $this->getCustomerGroupFacade();
        $customerGroupTransfer = $customerGroupFacade->add($customerGroupTransfer);

        $this->getDataCleanupHelper()->_addCleanup(function () use ($customerGroupTransfer): void {
            $this->cleanupCustomerGroup($customerGroupTransfer->getIdCustomerGroupOrFail());
        });

        return $customerGroupTransfer;
    }

    private function getCustomerGroupFacade(): CustomerGroupFacadeInterface
    {
        return $this->getLocator()->customerGroup()->facade();
    }

    private function cleanupCustomerGroup(int $idCustomerGroup): void
    {
        $this->debug(sprintf('Deleting CustomerGroup: %d', $idCustomerGroup));

        $this->getCustomerGroupQuery()
            ->findByIdCustomerGroup($idCustomerGroup)
            ->delete();
    }

    private function getCustomerGroupQuery(): SpyCustomerGroupQuery
    {
        return SpyCustomerGroupQuery::create();
    }
}
