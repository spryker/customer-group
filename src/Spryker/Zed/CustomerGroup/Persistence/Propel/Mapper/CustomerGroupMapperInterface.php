<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Propel\Runtime\Collection\ObjectCollection;

interface CustomerGroupMapperInterface
{
    public function mapCustomerGroupEntitiesToCustomerGroupCollectionTransfer(ObjectCollection $customerGroupEntities): CustomerGroupCollectionTransfer;
}
