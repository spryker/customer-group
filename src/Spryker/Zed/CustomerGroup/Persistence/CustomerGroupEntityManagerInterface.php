<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\CustomerGroup\Persistence;

interface CustomerGroupEntityManagerInterface
{
    /**
     * Specification:
     * - Assigns the given customers to the customer group.
     * - Writes the relations in batches rather than one statement per customer.
     * - Skips customers that are already assigned, so re-running the call changes nothing.
     * - Does nothing when the customer id list is empty.
     *
     * @api
     *
     * @param array<int, int> $customerIds
     */
    public function createCustomerGroupToCustomerRelations(int $idCustomerGroup, array $customerIds): void;

    /**
     * Specification:
     * - Removes the given customers from the customer group.
     * - Deletes the relations in batches rather than one statement per customer.
     * - Silently ignores customers that are not assigned to the group.
     * - Does nothing when the customer id list is empty.
     *
     * @api
     *
     * @param array<int, int> $customerIds
     */
    public function deleteCustomerGroupToCustomerRelations(int $idCustomerGroup, array $customerIds): void;
}
