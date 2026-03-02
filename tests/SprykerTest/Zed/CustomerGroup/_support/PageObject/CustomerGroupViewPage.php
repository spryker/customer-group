<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\PageObject;

class CustomerGroupViewPage
{
    public static function buildUrl(int $idCustomerGroup): string
    {
        return '/customer-group/view?id-customer-group=' . $idCustomerGroup;
    }
}
