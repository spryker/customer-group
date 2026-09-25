<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Writer;

use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;

interface CustomerGroupWriterInterface
{
    public function createCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer;

    public function updateCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer;
}
