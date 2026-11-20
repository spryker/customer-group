<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Controller;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;
use SprykerTest\Zed\CustomerGroup\PageObject\CustomerGroupListPage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Controller
 * @group CustomerGroupListFilterFormCest
 * Add your own group annotations below this line
 */
class CustomerGroupListFilterFormCest
{
    protected const string SELECTOR_FILTER_FORM = '[data-qa="customer-group-filter-form"]';

    public function filterFormIsHiddenWhenNoAssignmentFilterPluginIsWired(CustomerGroupCommunicationTester $i): void
    {
        $i->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER, []);

        $i->amOnPage(CustomerGroupListPage::URL);

        $i->seeResponseCodeIs(200);
        $i->dontSeeElement(static::SELECTOR_FILTER_FORM);
    }

    public function filterFormIsShownWhenAnAssignmentFilterPluginIsWired(CustomerGroupCommunicationTester $i): void
    {
        $i->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER,
            [$this->createCustomerGroupAssignmentFilterPlugin()],
        );

        $i->amOnPage(CustomerGroupListPage::URL);

        $i->seeResponseCodeIs(200);
        $i->seeElement(static::SELECTOR_FILTER_FORM);
    }

    protected function createCustomerGroupAssignmentFilterPlugin(): CustomerGroupAssignmentFilterPluginInterface
    {
        return new class implements CustomerGroupAssignmentFilterPluginInterface {
            public function getAssignmentType(): string
            {
                return 'test';
            }

            public function getLabel(): string
            {
                return 'Test';
            }

            public function getSuggestUrl(): string
            {
                return '/customer-group/test-suggest';
            }

            public function findAssignedEntityName(int $idAssignedEntity): ?string
            {
                return null;
            }

            public function expandCustomerGroupConditions(
                int $idAssignedEntity,
                CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
            ): CustomerGroupConditionsTransfer {
                return $customerGroupConditionsTransfer;
            }
        };
    }
}
