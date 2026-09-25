<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Zed\CustomerGroup\Persistence\Fixtures;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepository;

class CustomerGroupUuidGuardProbe extends CustomerGroupRepository
{
    public function exposeApplyCustomerGroupConditions(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): SpyCustomerGroupQuery {
        return $this->applyCustomerGroupConditions(
            $this->getFactory()->createCustomerGroupQuery(),
            $customerGroupCriteriaTransfer,
        );
    }
}
