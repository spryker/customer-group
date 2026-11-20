<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Table;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Table
 * @group CustomerGroupTableFilterTest
 * Add your own group annotations below this line
 */
class CustomerGroupTableFilterTest extends Unit
{
    protected const string ASSIGNMENT_TYPE_CUSTOMER = 'customer';

    protected const string ASSIGNMENT_TYPE_UNKNOWN = 'unknown';

    protected const int COLUMN_INDEX_ID_CUSTOMER_GROUP = 0;

    protected const string KEY_DATA = 'data';

    protected const string KEY_RECORDS_TOTAL = 'recordsTotal';

    protected const string KEY_RECORDS_FILTERED = 'recordsFiltered';

    protected CustomerGroupCommunicationTester $tester;

    public function testFilteredTableShowsOnlyTheGroupsOfTheChosenCustomer(): void
    {
        // Arrange
        $idCustomer = $this->tester->haveCustomer()->getIdCustomerOrFail();
        $assignedCustomerGroupTransfer = $this->haveCustomerGroupWithCustomer($idCustomer);
        $this->tester->haveCustomerGroup();

        // Act
        $customerGroupIds = $this->fetchFilteredCustomerGroupIds(static::ASSIGNMENT_TYPE_CUSTOMER, $idCustomer);

        // Assert
        $this->assertSame([$assignedCustomerGroupTransfer->getIdCustomerGroupOrFail()], $customerGroupIds);
    }

    public function testFilteredTableIsEmptyForACustomerWithoutGroups(): void
    {
        // Arrange
        $idCustomer = $this->tester->haveCustomer()->getIdCustomerOrFail();
        $this->tester->haveCustomerGroup();

        // Act
        $customerGroupIds = $this->fetchFilteredCustomerGroupIds(static::ASSIGNMENT_TYPE_CUSTOMER, $idCustomer);

        // Assert
        $this->assertSame([], $customerGroupIds);
    }

    public function testFilterWithAnUnknownAssignmentTypeLeavesTheTableUnfiltered(): void
    {
        // Arrange
        $idCustomer = $this->tester->haveCustomer()->getIdCustomerOrFail();
        $this->haveCustomerGroupWithCustomer($idCustomer);
        $this->tester->haveCustomerGroup();

        // Act
        $tableData = $this->fetchFilteredTableData(static::ASSIGNMENT_TYPE_UNKNOWN, $idCustomer);

        // Assert
        $this->assertSame($tableData[static::KEY_RECORDS_TOTAL], $tableData[static::KEY_RECORDS_FILTERED]);
    }

    /**
     * @return list<int>
     */
    protected function fetchFilteredCustomerGroupIds(string $assignmentType, int $idAssignedEntity): array
    {
        $rows = $this->fetchFilteredTableData($assignmentType, $idAssignedEntity)[static::KEY_DATA];

        return array_map('intval', array_column($rows, static::COLUMN_INDEX_ID_CUSTOMER_GROUP));
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchFilteredTableData(string $assignmentType, int $idAssignedEntity): array
    {
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER,
            [$this->createCustomerAssignmentFilterPlugin()],
        );
        $this->tester->setTableRequest();

        $customerGroupCriteriaTransfer = $this->getCommunicationFactory()
            ->createCustomerGroupAssignmentFilterResolver()
            ->createCustomerGroupCriteria(
                (new CustomerGroupTableFilterTransfer())->setAssignmentType($assignmentType)->setIdAssignedEntity($idAssignedEntity),
            );
        $customerGroupTable = $this->getCommunicationFactory()->createCustomerGroupTable();
        $customerGroupTable->applyCriteria($customerGroupCriteriaTransfer);

        return $customerGroupTable->fetchData();
    }

    protected function haveCustomerGroupWithCustomer(int $idCustomer): CustomerGroupTransfer
    {
        return $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::CUSTOMER_ASSIGNMENT => [
                CustomerGroupToCustomerAssignmentTransfer::IDS_CUSTOMER_TO_ASSIGN => [$idCustomer],
            ],
        ]);
    }

    protected function createCustomerAssignmentFilterPlugin(): CustomerGroupAssignmentFilterPluginInterface
    {
        return new class (static::ASSIGNMENT_TYPE_CUSTOMER) implements CustomerGroupAssignmentFilterPluginInterface {
            public function __construct(protected string $assignmentType)
            {
            }

            public function getAssignmentType(): string
            {
                return $this->assignmentType;
            }

            public function getLabel(): string
            {
                return 'Customer';
            }

            public function getSuggestUrl(): string
            {
                return '/customer/suggest';
            }

            public function findAssignedEntityName(int $idAssignedEntity): ?string
            {
                return null;
            }

            public function expandCustomerGroupConditions(
                int $idAssignedEntity,
                CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
            ): CustomerGroupConditionsTransfer {
                return $customerGroupConditionsTransfer->addIdCustomer($idAssignedEntity);
            }
        };
    }

    protected function getCommunicationFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
