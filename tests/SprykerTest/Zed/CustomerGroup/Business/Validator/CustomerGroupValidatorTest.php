<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidatorInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidatorInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupValidator;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Validator
 * @group CustomerGroupValidatorTest
 * Add your own group annotations below this line
 */
class CustomerGroupValidatorTest extends Unit
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_NAME_TAKEN = 'message.customer_group.validation.name_taken';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_CUSTOMER_NOT_FOUND = 'message.customer_group.validation.customer_not_found';

    public function testReturnsEveryCustomerGroupWhenEveryValidatorAccepts(): void
    {
        // Arrange
        $customerGroupTransfers = [
            (new CustomerGroupTransfer())->setName('Wholesale'),
            (new CustomerGroupTransfer())->setName('Retail'),
        ];

        $customerGroupValidator = new CustomerGroupValidator(
            $this->createNameUniquenessValidator([]),
            $this->createCustomerExistenceValidator([]),
        );

        // Act
        $customerGroupCollectionResponseTransfer = $customerGroupValidator->validateCollection($customerGroupTransfers);

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            $customerGroupTransfers,
            iterator_to_array($customerGroupCollectionResponseTransfer->getCustomerGroups()),
        );
    }

    public function testRunsEveryValidatorEvenAfterOneFails(): void
    {
        // Arrange
        $customerGroupValidator = new CustomerGroupValidator(
            $this->createNameUniquenessValidator([0 => static::GLOSSARY_KEY_NAME_TAKEN]),
            $this->createCustomerExistenceValidator([0 => static::GLOSSARY_KEY_CUSTOMER_NOT_FOUND]),
        );

        // Act
        $customerGroupCollectionResponseTransfer = $customerGroupValidator->validateCollection(
            [new CustomerGroupTransfer()],
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getCustomerGroups());
        $this->assertSame(
            [static::GLOSSARY_KEY_NAME_TAKEN, static::GLOSSARY_KEY_CUSTOMER_NOT_FOUND],
            $this->getErrorMessages($customerGroupCollectionResponseTransfer),
            'One request must report every problem it has, not one per round trip.',
        );
    }

    public function testKeepsTheValidCustomerGroupsOfACollectionThatAlsoCarriesInvalidOnes(): void
    {
        // Arrange
        $rejectedCustomerGroupTransfer = (new CustomerGroupTransfer())->setName('Wholesale');
        $acceptedCustomerGroupTransfer = (new CustomerGroupTransfer())->setName('Retail');

        $customerGroupValidator = new CustomerGroupValidator(
            $this->createNameUniquenessValidator([0 => static::GLOSSARY_KEY_NAME_TAKEN]),
            $this->createCustomerExistenceValidator([]),
        );

        // Act
        $customerGroupCollectionResponseTransfer = $customerGroupValidator->validateCollection(
            [$rejectedCustomerGroupTransfer, $acceptedCustomerGroupTransfer],
        );

        // Assert
        $this->assertSame(
            [$acceptedCustomerGroupTransfer],
            iterator_to_array($customerGroupCollectionResponseTransfer->getCustomerGroups()),
        );
        $this->assertSame(
            [static::GLOSSARY_KEY_NAME_TAKEN],
            $this->getErrorMessages($customerGroupCollectionResponseTransfer),
        );
    }

    /**
     * Each validator reads the database in its own collection call, so a larger collection must not cost more calls.
     */
    public function testCallsEveryValidatorExactlyOnceForTheWholeCollection(): void
    {
        // Arrange
        $customerGroupNameUniquenessValidator = $this->createNameUniquenessValidator([]);
        $customerGroupNameUniquenessValidator->expects($this->once())->method('validateCollection');

        $customerGroupCustomerExistenceValidator = $this->createCustomerExistenceValidator([]);
        $customerGroupCustomerExistenceValidator->expects($this->once())->method('validateCollection');

        $customerGroupValidator = new CustomerGroupValidator(
            $customerGroupNameUniquenessValidator,
            $customerGroupCustomerExistenceValidator,
        );

        // Act
        $customerGroupValidator->validateCollection([
            (new CustomerGroupTransfer())->setName('Wholesale'),
            (new CustomerGroupTransfer())->setName('Retail'),
            (new CustomerGroupTransfer())->setName('Partner'),
        ]);
    }

    /**
     * @param array<int, string> $glossaryKeysIndexedByCollectionKey
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidatorInterface
     */
    protected function createNameUniquenessValidator(
        array $glossaryKeysIndexedByCollectionKey
    ): CustomerGroupNameUniquenessValidatorInterface {
        $customerGroupNameUniquenessValidator = $this->createMock(CustomerGroupNameUniquenessValidatorInterface::class);
        $customerGroupNameUniquenessValidator->method('validateCollection')->willReturnCallback(
            fn (array $customerGroupTransfers): array => $this->createResponses(
                $customerGroupTransfers,
                $glossaryKeysIndexedByCollectionKey,
            ),
        );

        return $customerGroupNameUniquenessValidator;
    }

    /**
     * @param array<int, string> $glossaryKeysIndexedByCollectionKey
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidatorInterface
     */
    protected function createCustomerExistenceValidator(
        array $glossaryKeysIndexedByCollectionKey
    ): CustomerGroupCustomerExistenceValidatorInterface {
        $customerGroupCustomerExistenceValidator = $this->createMock(CustomerGroupCustomerExistenceValidatorInterface::class);
        $customerGroupCustomerExistenceValidator->method('validateCollection')->willReturnCallback(
            fn (array $customerGroupTransfers): array => $this->createResponses(
                $customerGroupTransfers,
                $glossaryKeysIndexedByCollectionKey,
            ),
        );

        return $customerGroupCustomerExistenceValidator;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     * @param array<int, string> $glossaryKeysIndexedByCollectionKey
     *
     * @return array<int, \Generated\Shared\Transfer\CustomerGroupResponseTransfer>
     */
    protected function createResponses(array $customerGroupTransfers, array $glossaryKeysIndexedByCollectionKey): array
    {
        $customerGroupResponseTransfers = [];

        foreach ($customerGroupTransfers as $key => $customerGroupTransfer) {
            $customerGroupResponseTransfers[$key] = $this->createResponse(
                $customerGroupTransfer,
                $glossaryKeysIndexedByCollectionKey[$key] ?? null,
            );
        }

        return $customerGroupResponseTransfers;
    }

    protected function createResponse(
        CustomerGroupTransfer $customerGroupTransfer,
        ?string $glossaryKey
    ): CustomerGroupResponseTransfer {
        $customerGroupResponseTransfer = (new CustomerGroupResponseTransfer())
            ->setIsSuccessful(true)
            ->setCustomerGroup($customerGroupTransfer);

        if ($glossaryKey === null) {
            return $customerGroupResponseTransfer;
        }

        return $customerGroupResponseTransfer
            ->setIsSuccessful(false)
            ->addError((new ErrorTransfer())->setMessage($glossaryKey));
    }

    /**
     * @return array<int, string>
     */
    protected function getErrorMessages(
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): array {
        $messages = [];

        foreach ($customerGroupCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $messages[] = (string)$errorTransfer->getMessage();
        }

        return $messages;
    }
}
