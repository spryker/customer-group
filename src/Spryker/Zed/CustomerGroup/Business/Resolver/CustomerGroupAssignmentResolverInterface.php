<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Resolver;

interface CustomerGroupAssignmentResolverInterface
{
    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\CustomerGroupTransfer>
     */
    public function resolveCollection(array $customerGroupTransfers): array;
}
