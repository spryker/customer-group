<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Validator;

interface CustomerGroupNameUniquenessValidatorInterface
{
    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\CustomerGroupResponseTransfer>
     */
    public function validateCollection(array $customerGroupTransfers): array;
}
