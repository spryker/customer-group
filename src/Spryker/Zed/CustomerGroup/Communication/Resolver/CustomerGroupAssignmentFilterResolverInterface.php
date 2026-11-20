<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Resolver;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;

interface CustomerGroupAssignmentFilterResolverInterface
{
    public function hasAssignmentFilters(): bool;

    /**
     * @return array<string, string> Keys are the option labels, values the assignment types.
     */
    public function getAssignmentTypeChoices(): array;

    /**
     * @return array<string, string> Keys are the assignment types, values the suggest URLs.
     */
    public function getSuggestUrls(): array;

    public function findAssignedEntityName(CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer): ?string;

    public function createCustomerGroupCriteria(CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer): CustomerGroupCriteriaTransfer;
}
