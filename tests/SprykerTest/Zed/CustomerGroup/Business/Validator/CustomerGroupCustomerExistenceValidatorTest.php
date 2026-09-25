<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidator;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Validator
 * @group CustomerGroupCustomerExistenceValidatorTest
 * Add your own group annotations below this line
 */
class CustomerGroupCustomerExistenceValidatorTest extends Unit
{
    /**
     * @var string
     */
    protected const CUSTOMER_REFERENCE_KNOWN = 'customer--1';

    /**
     * @var string
     */
    protected const CUSTOMER_REFERENCE_UNKNOWN = 'customer--404';

    public function testReadsTheRepositoryOnceForTheReferencesOfTheWholeCollection(): void
    {
        // Arrange
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->expects($this->once())
            ->method('getCustomerIdsIndexedByCustomerReference')
            ->with([static::CUSTOMER_REFERENCE_KNOWN, static::CUSTOMER_REFERENCE_UNKNOWN])
            ->willReturn([static::CUSTOMER_REFERENCE_KNOWN => 1]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupCustomerExistenceValidator($customerGroupRepository))
            ->validateCollection([
                $this->createCustomerGroupWithAssignment([static::CUSTOMER_REFERENCE_KNOWN]),
                $this->createCustomerGroupWithAssignment([
                    static::CUSTOMER_REFERENCE_KNOWN,
                    static::CUSTOMER_REFERENCE_UNKNOWN,
                ]),
            ]);

        // Assert
        $this->assertTrue($customerGroupResponseTransfers[0]->getIsSuccessful());
        $this->assertFalse($customerGroupResponseTransfers[1]->getIsSuccessful());
    }

    public function testReportsEveryUnknownCustomerReference(): void
    {
        // Arrange
        $customerGroupRepository = $this->createRepository([]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupCustomerExistenceValidator($customerGroupRepository))
            ->validateCollection([
                $this->createCustomerGroupWithAssignment([
                    static::CUSTOMER_REFERENCE_KNOWN,
                    static::CUSTOMER_REFERENCE_UNKNOWN,
                ]),
            ]);

        // Assert
        $this->assertSame(
            [static::CUSTOMER_REFERENCE_KNOWN, static::CUSTOMER_REFERENCE_UNKNOWN],
            $this->getEntityIdentifiers($customerGroupResponseTransfers[0]),
        );
    }

    public function testIgnoresCustomerGroupsThatCarryNoAssignmentChange(): void
    {
        // Arrange
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->expects($this->once())
            ->method('getCustomerIdsIndexedByCustomerReference')
            ->with([])
            ->willReturn([]);

        // Act
        $customerGroupResponseTransfers = (new CustomerGroupCustomerExistenceValidator($customerGroupRepository))
            ->validateCollection([
                (new CustomerGroupTransfer())->setCustomerReferences([static::CUSTOMER_REFERENCE_UNKNOWN]),
            ]);

        // Assert
        $this->assertTrue($customerGroupResponseTransfers[0]->getIsSuccessful());
    }

    /**
     * @param array<int, string> $customerReferences
     */
    protected function createCustomerGroupWithAssignment(array $customerReferences): CustomerGroupTransfer
    {
        return (new CustomerGroupTransfer())
            ->setHasAssignmentChange(true)
            ->setCustomerReferences($customerReferences);
    }

    /**
     * @param array<string, int> $customerIdsIndexedByCustomerReference
     */
    protected function createRepository(array $customerIdsIndexedByCustomerReference): CustomerGroupRepositoryInterface
    {
        $customerGroupRepository = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepository->method('getCustomerIdsIndexedByCustomerReference')
            ->willReturn($customerIdsIndexedByCustomerReference);

        return $customerGroupRepository;
    }

    /**
     * @return array<int, string>
     */
    protected function getEntityIdentifiers(
        CustomerGroupResponseTransfer $customerGroupResponseTransfer
    ): array {
        $entityIdentifiers = [];

        foreach ($customerGroupResponseTransfer->getErrors() as $errorTransfer) {
            $entityIdentifiers[] = (string)$errorTransfer->getEntityIdentifier();
        }

        return $entityIdentifiers;
    }
}
