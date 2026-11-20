<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Controller;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Controller
 * @group CustomerGroupSuggestCest
 * Add your own group annotations below this line
 */
class CustomerGroupSuggestCest
{
    protected const string URL_SUGGEST = '/customer-group/suggest';

    public function suggestRespondsWithTheMatchingGroupNamesInSelect2Format(CustomerGroupCommunicationTester $i): void
    {
        // Arrange
        $customerGroupTransfer = $i->haveCustomerGroup([CustomerGroupTransfer::NAME => uniqid('suggest')]);

        // Act
        $i->amOnPage(sprintf('%s?term=%s', static::URL_SUGGEST, $customerGroupTransfer->getNameOrFail()));

        // Assert
        $i->seeResponseCodeIs(200);
        $i->assertSame(
            ['results' => [['id' => $customerGroupTransfer->getNameOrFail(), 'text' => $customerGroupTransfer->getNameOrFail()]]],
            json_decode($i->getClient()->getResponse()->getContent(), true),
        );
    }
}
