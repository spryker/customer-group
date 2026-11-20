<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use SprykerTest\Zed\CustomerGroup\CustomerGroupBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Facade
 * @group GetCustomerGroupCollectionByCustomerConditionsTest
 * Add your own group annotations below this line
 */
class GetCustomerGroupCollectionByCustomerConditionsTest extends Unit
{
    protected const string CUSTOMER_REFERENCE_UNKNOWN = 'unknown-customer-reference';

    protected CustomerGroupBusinessTester $tester;

    public function testReturnsOnlyTheGroupsOfTheGivenCustomerIds(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $assignedCustomerGroupTransfer = $this->haveCustomerGroupWithCustomers([$customerTransfer]);
        $this->haveCustomerGroupWithCustomers([$this->tester->haveCustomer()]);

        // Act
        $customerGroupCollectionTransfer = $this->getCustomerGroupCollection(
            (new CustomerGroupConditionsTransfer())->addIdCustomer($customerTransfer->getIdCustomerOrFail()),
        );

        // Assert
        $this->assertSame([$assignedCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->extractIds($customerGroupCollectionTransfer));
    }

    public function testReturnsOnlyTheGroupsOfTheGivenCustomerReferences(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $assignedCustomerGroupTransfer = $this->haveCustomerGroupWithCustomers([$customerTransfer]);
        $this->haveCustomerGroupWithCustomers([$this->tester->haveCustomer()]);

        // Act
        $customerGroupCollectionTransfer = $this->getCustomerGroupCollection(
            (new CustomerGroupConditionsTransfer())->addCustomerReference($customerTransfer->getCustomerReferenceOrFail()),
        );

        // Assert
        $this->assertSame([$assignedCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->extractIds($customerGroupCollectionTransfer));
    }

    public function testReturnsAGroupOnceWhenSeveralOfTheGivenCustomersAreAssignedToIt(): void
    {
        // Arrange
        $firstCustomerTransfer = $this->tester->haveCustomer();
        $secondCustomerTransfer = $this->tester->haveCustomer();
        $sharedCustomerGroupTransfer = $this->haveCustomerGroupWithCustomers([$firstCustomerTransfer, $secondCustomerTransfer]);

        // Act
        $customerGroupCollectionTransfer = $this->getCustomerGroupCollection(
            (new CustomerGroupConditionsTransfer())
                ->addIdCustomer($firstCustomerTransfer->getIdCustomerOrFail())
                ->addIdCustomer($secondCustomerTransfer->getIdCustomerOrFail()),
        );

        // Assert
        $this->assertSame([$sharedCustomerGroupTransfer->getIdCustomerGroupOrFail()], $this->extractIds($customerGroupCollectionTransfer));
        $this->assertSame(1, $customerGroupCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testWithIdsAndReferencesReturnsTheGroupThatHasACustomerOfEachEvenWhenTheyAreDifferentCustomers(): void
    {
        // Arrange
        $customerByIdTransfer = $this->tester->haveCustomer();
        $customerByReferenceTransfer = $this->tester->haveCustomer();
        $customerGroupWithBothTransfer = $this->haveCustomerGroupWithCustomers([$customerByIdTransfer, $customerByReferenceTransfer]);
        $this->haveCustomerGroupWithCustomers([$customerByIdTransfer]);
        $this->haveCustomerGroupWithCustomers([$customerByReferenceTransfer]);

        // Act
        $customerGroupCollectionTransfer = $this->getCustomerGroupCollection(
            (new CustomerGroupConditionsTransfer())
                ->addIdCustomer($customerByIdTransfer->getIdCustomerOrFail())
                ->addCustomerReference($customerByReferenceTransfer->getCustomerReferenceOrFail()),
        );

        // Assert
        $this->assertSame([$customerGroupWithBothTransfer->getIdCustomerGroupOrFail()], $this->extractIds($customerGroupCollectionTransfer));
    }

    public function testReturnsNoGroupForAnUnknownCustomerReference(): void
    {
        // Arrange
        $this->haveCustomerGroupWithCustomers([$this->tester->haveCustomer()]);

        // Act
        $customerGroupCollectionTransfer = $this->getCustomerGroupCollection(
            (new CustomerGroupConditionsTransfer())->addCustomerReference(static::CUSTOMER_REFERENCE_UNKNOWN),
        );

        // Assert
        $this->assertSame([], $this->extractIds($customerGroupCollectionTransfer));
    }

    protected function getCustomerGroupCollection(CustomerGroupConditionsTransfer $customerGroupConditionsTransfer): CustomerGroupCollectionTransfer
    {
        return $this->tester->getLocator()->customerGroup()->facade()->getCustomerGroupCollection(
            (new CustomerGroupCriteriaTransfer())
                ->setCustomerGroupConditions($customerGroupConditionsTransfer)
                ->setPagination((new PaginationTransfer())->setPage(1)->setMaxPerPage(10)),
        );
    }

    /**
     * @param list<\Generated\Shared\Transfer\CustomerTransfer> $customerTransfers
     */
    protected function haveCustomerGroupWithCustomers(array $customerTransfers): CustomerGroupTransfer
    {
        return $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::CUSTOMER_ASSIGNMENT => [
                CustomerGroupToCustomerAssignmentTransfer::IDS_CUSTOMER_TO_ASSIGN => array_map(
                    static fn (CustomerTransfer $customerTransfer): int => $customerTransfer->getIdCustomerOrFail(),
                    $customerTransfers,
                ),
            ],
        ]);
    }

    /**
     * @return list<int>
     */
    protected function extractIds(CustomerGroupCollectionTransfer $customerGroupCollectionTransfer): array
    {
        $customerGroupIds = [];

        foreach ($customerGroupCollectionTransfer->getGroups() as $customerGroupTransfer) {
            $customerGroupIds[] = $customerGroupTransfer->getIdCustomerGroupOrFail();
        }

        return $customerGroupIds;
    }
}
