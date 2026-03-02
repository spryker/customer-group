<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\PageObject;

class CustomerGroupEditPage
{
    public static function buildUrl(int $idCustomerGroup): string
    {
        return '/customer-group/edit?id-customer-group=' . $idCustomerGroup;
    }
}
