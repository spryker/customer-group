<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Controller;

use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;
use SprykerTest\Zed\CustomerGroup\PageObject\CustomerGroupEditPage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Controller
 * @group CustomerGroupEditCest
 * Add your own group annotations below this line
 */
class CustomerGroupEditCest
{
    protected const int ID_NOT_EXISTING_CUSTOMER_GROUP = 999999999;

    protected const string URL_CUSTOMER_GROUP_LIST = '/customer-group';

    public function breadcrumbIsVisible(CustomerGroupCommunicationTester $i): void
    {
        $customerGroupTransfer = $i->haveCustomerGroup();
        $i->amOnPage(CustomerGroupEditPage::buildUrl($customerGroupTransfer->getIdCustomerGroup()));

        $i->seeBreadcrumbNavigation('Customers / Customer Groups / Edit Customer Group');
    }

    public function editOfNotExistingCustomerGroupRedirectsToTheList(CustomerGroupCommunicationTester $i): void
    {
        // Act
        $i->amOnPage(CustomerGroupEditPage::buildUrl(static::ID_NOT_EXISTING_CUSTOMER_GROUP));

        // Assert
        $i->seeResponseCodeIsRedirection();
        $i->assertSame(static::URL_CUSTOMER_GROUP_LIST, $i->getClient()->getInternalResponse()->getHeader('Location'));
    }
}
