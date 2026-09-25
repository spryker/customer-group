<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;

interface CustomerGroupRepositoryInterface
{
    /**
     * @module Customer
     *
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionTransfer
     */
    public function getCustomerGroupCollectionByIdCustomer(int $idCustomer): CustomerGroupCollectionTransfer;

    public function getCustomerGroupCollection(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): CustomerGroupCollectionTransfer;

    /**
     * @module Customer
     */
    public function getCustomerCollectionByCustomerGroupCriteria(
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): CustomerCollectionTransfer;

    /**
     * @module Customer
     *
     * @param array<int, int> $customerGroupIds
     *
     * @return array<int, array<int, int>>
     */
    public function getCustomerIdsGroupedByIdCustomerGroup(array $customerGroupIds): array;

    /**
     * @module Customer
     *
     * @param array<int, string> $customerReferences
     *
     * @return array<string, int>
     */
    public function getCustomerIdsIndexedByCustomerReference(array $customerReferences): array;

    /**
     * @param array<int, string> $names
     *
     * @return array<string, array<int, int>>
     */
    public function getCustomerGroupIdsGroupedByLowercasedName(array $names): array;
}
