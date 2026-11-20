<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence\Propel\QueryBuilder;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;

interface CustomerGroupQueryBuilderInterface
{
    public function applyCustomerGroupConditions(
        SpyCustomerGroupQuery $customerGroupQuery,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): SpyCustomerGroupQuery;

    /**
     * @param array<int, string> $names
     */
    public function applyCaseInsensitiveNameFilter(SpyCustomerGroupQuery $customerGroupQuery, array $names): SpyCustomerGroupQuery;

    public function addQueryExpanderJoins(
        SpyCustomerGroupQuery $customerGroupQuery,
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): SpyCustomerGroupQuery;
}
