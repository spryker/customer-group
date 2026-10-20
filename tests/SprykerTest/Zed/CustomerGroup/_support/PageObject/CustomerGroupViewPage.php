<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\PageObject;

class CustomerGroupViewPage
{
    public const string SELECTOR_PANEL_TITLE = '.ibox-title h5';

    public const string SELECTOR_NAME = '[data-qa="customer-group-name"]';

    public const string SELECTOR_DESCRIPTION = '[data-qa="customer-group-description"]';

    public const string XPATH_CUSTOMERS_BLOCK_AFTER_GENERAL_INFORMATION_BLOCK = '//*[@data-qa="customer-group-view-block-general"]/following-sibling::*[@data-qa="customer-group-view-block-customer-assignment"]';

    public static function buildUrl(int $idCustomerGroup): string
    {
        return '/customer-group/view?id-customer-group=' . $idCustomerGroup;
    }
}
