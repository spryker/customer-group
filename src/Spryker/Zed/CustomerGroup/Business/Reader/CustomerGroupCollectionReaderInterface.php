<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Reader;

use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerGroupCollectionReaderInterface
{
    public function getCustomerGroupCollectionByCustomer(CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer;
}
