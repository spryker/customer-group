<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Controller;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;
use SprykerTest\Zed\CustomerGroup\PageObject\CustomerGroupViewPage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Controller
 * @group CustomerGroupViewCest
 * Add your own group annotations below this line
 */
class CustomerGroupViewCest
{
    public function breadcrumbIsVisible(CustomerGroupCommunicationTester $i): void
    {
        $customerGroupTransfer = $i->haveCustomerGroup();
        $i->amOnPage(CustomerGroupViewPage::buildUrl($customerGroupTransfer->getIdCustomerGroupOrFail()));

        $i->seeBreadcrumbNavigation('Customers / Customer Groups / View Customer Group');
    }

    public function generalInformationShowsTheNameAndTheDescription(CustomerGroupCommunicationTester $i): void
    {
        $customerGroupTransfer = $i->haveCustomerGroup([
            CustomerGroupTransfer::DESCRIPTION => 'Customers with negotiated terms',
        ]);
        $i->amOnPage(CustomerGroupViewPage::buildUrl($customerGroupTransfer->getIdCustomerGroupOrFail()));

        $i->see('General Information', CustomerGroupViewPage::SELECTOR_PANEL_TITLE);
        $i->see($customerGroupTransfer->getNameOrFail(), CustomerGroupViewPage::SELECTOR_NAME);
        $i->see('Customers with negotiated terms', CustomerGroupViewPage::SELECTOR_DESCRIPTION);
    }

    public function blocksAreRenderedInTheOrderTheyAreAddedByDefault(CustomerGroupCommunicationTester $i): void
    {
        $customerGroupTransfer = $i->haveCustomerGroup();
        $i->amOnPage(CustomerGroupViewPage::buildUrl($customerGroupTransfer->getIdCustomerGroupOrFail()));

        $i->seeElement(CustomerGroupViewPage::XPATH_CUSTOMERS_BLOCK_AFTER_GENERAL_INFORMATION_BLOCK);
    }

    public function customerTableIsShownInItsOwnPanel(CustomerGroupCommunicationTester $i): void
    {
        $customerGroupTransfer = $i->haveCustomerGroup();
        $i->amOnPage(CustomerGroupViewPage::buildUrl($customerGroupTransfer->getIdCustomerGroupOrFail()));

        $i->see('Customers', CustomerGroupViewPage::SELECTOR_PANEL_TITLE);
    }
}
