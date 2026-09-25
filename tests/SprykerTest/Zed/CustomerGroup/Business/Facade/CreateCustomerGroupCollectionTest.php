<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidator;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidator;
use SprykerTest\Zed\CustomerGroup\CustomerGroupBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Facade
 * @group CreateCustomerGroupCollectionTest
 * Add your own group annotations below this line
 */
class CreateCustomerGroupCollectionTest extends Unit
{
    protected CustomerGroupBusinessTester $tester;

    protected string $prefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefix = uniqid('cgw-', false);
    }

    public function testPersistsTheGroupAndReturnsItWithAGeneratedUuid(): void
    {
        // Arrange
        $name = $this->prefix . '-alpha';

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([(new CustomerGroupTransfer())->setName($name)]),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getCustomerGroups());

        $customerGroupTransfer = $customerGroupCollectionResponseTransfer->getCustomerGroups()->offsetGet(0);
        $this->assertNotNull($customerGroupTransfer->getIdCustomerGroup());
        $this->assertNotNull($customerGroupTransfer->getUuid(), 'The uuid behaviour stamps the row on insert.');
        $this->assertSame(1, SpyCustomerGroupQuery::create()->filterByName($name)->count());
    }

    public function testAssignsTheCustomersNamedByReference(): void
    {
        // Arrange
        $customerEntity = $this->haveCustomer('anna');

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())
                    ->setName($this->prefix . '-alpha')
                    ->setHasAssignmentChange(true)
                    ->setCustomerReferences([$customerEntity->getCustomerReference()]),
            ]),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            [$customerEntity->getIdCustomer()],
            $this->getAssignedCustomerIds(
                $customerGroupCollectionResponseTransfer->getCustomerGroups()->offsetGet(0)->getIdCustomerGroupOrFail(),
            ),
        );
    }

    public function testRejectsANameAnotherGroupAlreadyUsesRegardlessOfCase(): void
    {
        // Arrange
        $name = $this->prefix . '-alpha';
        $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([(new CustomerGroupTransfer())->setName($name)]),
        );

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([(new CustomerGroupTransfer())->setName(mb_strtoupper($name))]),
        );

        // Assert
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            CustomerGroupNameUniquenessValidator::GLOSSARY_KEY_ERROR_NAME_TAKEN,
            $customerGroupCollectionResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
        $this->assertSame(1, SpyCustomerGroupQuery::create()->filterByName($name)->count());
    }

    public function testReportsEveryUnknownCustomerReferenceAndPersistsNothing(): void
    {
        // Arrange
        $name = $this->prefix . '-alpha';

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())
                    ->setName($name)
                    ->setHasAssignmentChange(true)
                    ->setCustomerReferences(['no-such-reference-1', 'no-such-reference-2']),
            ]),
        );

        // Assert
        $this->assertCount(
            2,
            $customerGroupCollectionResponseTransfer->getErrors(),
            'One error per offending reference, so a client fixes them in one round trip.',
        );
        $this->assertSame(
            CustomerGroupCustomerExistenceValidator::GLOSSARY_KEY_ERROR_CUSTOMER_NOT_FOUND,
            $customerGroupCollectionResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
        $this->assertSame(
            'no-such-reference-1',
            $customerGroupCollectionResponseTransfer->getErrors()->offsetGet(0)->getEntityIdentifier(),
        );
        $this->assertSame(0, SpyCustomerGroupQuery::create()->filterByName($name)->count());
    }

    public function testATransactionalRequestPersistsNothingWhenOneItemIsInvalid(): void
    {
        // Arrange
        $validName = $this->prefix . '-valid';

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())->setName($validName),
                (new CustomerGroupTransfer())
                    ->setName($this->prefix . '-invalid')
                    ->setHasAssignmentChange(true)
                    ->setCustomerReferences(['no-such-reference']),
            ]),
        );

        // Assert
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getCustomerGroups());
        $this->assertSame(
            0,
            SpyCustomerGroupQuery::create()->filterByName($validName)->count(),
            'All-or-nothing: the valid sibling must not survive a rejected batch.',
        );
    }

    public function testANonTransactionalRequestPersistsTheValidItemsAndSkipsTheInvalidOnes(): void
    {
        // Arrange
        $validName = $this->prefix . '-valid';
        $invalidName = $this->prefix . '-invalid';

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->createCustomerGroupCollection(
            $this->createRequest(
                [
                    (new CustomerGroupTransfer())->setName($validName),
                    (new CustomerGroupTransfer())
                        ->setName($invalidName)
                        ->setHasAssignmentChange(true)
                        ->setCustomerReferences(['no-such-reference']),
                ],
                false,
            ),
        );

        // Assert
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getCustomerGroups());
        $this->assertSame(1, SpyCustomerGroupQuery::create()->filterByName($validName)->count());
        $this->assertSame(0, SpyCustomerGroupQuery::create()->filterByName($invalidName)->count());
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     */
    protected function createRequest(
        array $customerGroupTransfers,
        bool $isTransactional = true
    ): CustomerGroupCollectionRequestTransfer {
        $customerGroupCollectionRequestTransfer = (new CustomerGroupCollectionRequestTransfer())
            ->setIsTransactional($isTransactional);

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $customerGroupCollectionRequestTransfer->addCustomerGroup($customerGroupTransfer);
        }

        return $customerGroupCollectionRequestTransfer;
    }

    protected function haveCustomer(string $slug): SpyCustomer
    {
        $customerEntity = (new SpyCustomer())
            ->setEmail(sprintf('%s-%s@example.com', $slug, $this->prefix))
            ->setCustomerReference(sprintf('%s-%s', $this->prefix, $slug))
            ->setFirstName('Anna')
            ->setLastName('Almond');

        $customerEntity->save();

        return $customerEntity;
    }

    /**
     * @return array<int, int>
     */
    protected function getAssignedCustomerIds(int $idCustomerGroup): array
    {
        $customerIds = SpyCustomerGroupToCustomerQuery::create()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->select(['FkCustomer'])
            ->find()
            ->toArray();

        return array_map('intval', $customerIds);
    }

    protected function getFacade(): CustomerGroupFacadeInterface
    {
        return $this->tester->getFacade();
    }
}
