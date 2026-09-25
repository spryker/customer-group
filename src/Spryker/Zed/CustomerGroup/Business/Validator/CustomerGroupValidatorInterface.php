<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Validator;

use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;

interface CustomerGroupValidatorInterface
{
    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer
     */
    public function validateCollection(array $customerGroupTransfers): CustomerGroupCollectionResponseTransfer;
}
