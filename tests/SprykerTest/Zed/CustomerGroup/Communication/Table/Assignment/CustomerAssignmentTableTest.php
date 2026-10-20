<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Table\Assignment;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AbstractAssignmentTable;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Table
 * @group Assignment
 * @group CustomerAssignmentTableTest
 * Add your own group annotations below this line
 */
class CustomerAssignmentTableTest extends Unit
{
    protected const int COLUMN_INDEX_SELECT_CHECKBOX = 4;

    protected const string CHECKBOX_VALUE_TEMPLATE = 'value="%d"';

    protected const string CHECKBOX_CHECKED_ATTRIBUTE = 'checked';

    protected CustomerGroupCommunicationTester $tester;

    public function testCustomerTablesShowTheSelectionCheckboxInTheLastColumn(): void
    {
        // Arrange
        $this->tester->setTableRequest();
        $availableCustomerTable = $this->getCommunicationFactory()->createAvailableCustomerTable();
        $assignedCustomerTable = $this->getCommunicationFactory()->createAssignedCustomerTable();

        // Act
        $availableCustomerTable->fetchData();
        $assignedCustomerTable->fetchData();

        // Assert
        $this->assertSame(AbstractAssignmentTable::COL_SELECT_CHECKBOX, array_key_last($availableCustomerTable->getConfiguration()->getHeader()));
        $this->assertSame(AbstractAssignmentTable::COL_SELECT_CHECKBOX, array_key_last($assignedCustomerTable->getConfiguration()->getHeader()));
    }

    public function testAssignedCustomersAreNotSelectedForDeAssignmentWhenTheTableLoads(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $customerGroupTransfer = $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::CUSTOMER_ASSIGNMENT => [
                CustomerGroupToCustomerAssignmentTransfer::IDS_CUSTOMER_TO_ASSIGN => [$customerTransfer->getIdCustomerOrFail()],
            ],
        ]);
        $this->tester->setTableRequest();

        // Act
        $rows = $this->getCommunicationFactory()
            ->createAssignedCustomerTable($customerGroupTransfer->getIdCustomerGroupOrFail())
            ->fetchData()['data'];

        // Assert
        $this->assertCount(1, $rows);
        $this->assertStringContainsString(
            sprintf(static::CHECKBOX_VALUE_TEMPLATE, $customerTransfer->getIdCustomerOrFail()),
            $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX],
        );
        $this->assertStringNotContainsString(static::CHECKBOX_CHECKED_ATTRIBUTE, $rows[0][static::COLUMN_INDEX_SELECT_CHECKBOX]);
    }

    protected function getCommunicationFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
