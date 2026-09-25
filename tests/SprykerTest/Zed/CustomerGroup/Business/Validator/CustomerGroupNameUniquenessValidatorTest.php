<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidator;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Validator
 * @group CustomerGroupNameUniquenessValidatorTest
 * Add your own group annotations below this line
 */
class CustomerGroupNameUniquenessValidatorTest extends Unit
{
    /**
     * @var int
     */
    protected const ID_CUSTOMER_GROUP = 7;

    public function testReadsTheRepositoryOnceForTheWholeCollection(): void
    {
        // Arrange
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->expects($this->once())
            ->method('getCustomerGroupIdsGroupedByLowercasedName')
            ->with(['Wholesale', 'Retail', 'Partner'])
            ->willReturn([]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupNameUniquenessValidator($customerGroupRepository))
            ->validateCollection([
                (new CustomerGroupTransfer())->setName('Wholesale'),
                (new CustomerGroupTransfer())->setName('Retail'),
                (new CustomerGroupTransfer())->setName('Partner'),
            ]);

        // Assert
        $this->assertCount(3, $customerGroupResponseTransfers);
    }

    public function testRejectsANameAnotherCustomerGroupAlreadyUsesRegardlessOfCase(): void
    {
        // Arrange
        $customerGroupRepository = $this->createRepository(['wholesale' => [static::ID_CUSTOMER_GROUP]]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupNameUniquenessValidator($customerGroupRepository))
            ->validateCollection([(new CustomerGroupTransfer())->setName('WHOLESALE')]);

        // Assert
        $this->assertFalse($customerGroupResponseTransfers[0]->getIsSuccessful());
        $this->assertSame(
            CustomerGroupNameUniquenessValidator::GLOSSARY_KEY_ERROR_NAME_TAKEN,
            $customerGroupResponseTransfers[0]->getErrors()->offsetGet(0)->getMessage(),
        );
    }

    public function testAcceptsTheCustomerGroupsOwnNameOnUpdate(): void
    {
        // Arrange
        $customerGroupRepository = $this->createRepository(['wholesale' => [static::ID_CUSTOMER_GROUP]]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupNameUniquenessValidator($customerGroupRepository))
            ->validateCollection([
                (new CustomerGroupTransfer())
                    ->setIdCustomerGroup(static::ID_CUSTOMER_GROUP)
                    ->setName('Wholesale'),
            ]);

        // Assert
        $this->assertTrue($customerGroupResponseTransfers[0]->getIsSuccessful());
    }

    public function testJudgesEveryCustomerGroupOfTheCollectionSeparately(): void
    {
        // Arrange
        $customerGroupRepository = $this->createRepository(['wholesale' => [static::ID_CUSTOMER_GROUP]]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupNameUniquenessValidator($customerGroupRepository))
            ->validateCollection([
                (new CustomerGroupTransfer())->setName('Wholesale'),
                (new CustomerGroupTransfer())->setName('Retail'),
            ]);

        // Assert
        $this->assertFalse($customerGroupResponseTransfers[0]->getIsSuccessful());
        $this->assertTrue($customerGroupResponseTransfers[1]->getIsSuccessful());
    }

    public function testSkipsTheLookupForCustomerGroupsWithoutAName(): void
    {
        // Arrange
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->expects($this->once())
            ->method('getCustomerGroupIdsGroupedByLowercasedName')
            ->with([])
            ->willReturn([]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupNameUniquenessValidator($customerGroupRepository))
            ->validateCollection([new CustomerGroupTransfer()]);

        // Assert
        $this->assertTrue($customerGroupResponseTransfers[0]->getIsSuccessful());
    }

    /**
     * @param array<string, array<int, int>> $customerGroupIdsGroupedByLowercasedName
     *
     * @return \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface
     */
    protected function createRepository(array $customerGroupIdsGroupedByLowercasedName): CustomerGroupRepositoryInterface
    {
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->method('getCustomerGroupIdsGroupedByLowercasedName')
            ->willReturn($customerGroupIdsGroupedByLowercasedName);

        return $customerGroupRepository;
    }
}
