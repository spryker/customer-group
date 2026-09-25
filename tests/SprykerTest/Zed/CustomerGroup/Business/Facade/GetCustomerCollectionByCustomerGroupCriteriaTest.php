<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupCustomerConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Facade
 * @group GetCustomerCollectionByCustomerGroupCriteriaTest
 * Add your own group annotations below this line
 */
class GetCustomerCollectionByCustomerGroupCriteriaTest extends Unit
{
    use LocatorHelperTrait;

    protected string $prefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefix = uniqid('cgc-', false);
    }

    public function testReturnsOnlyTheCustomersAssignedToTheGroup(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $otherCustomerGroupEntity = $this->haveCustomerGroup();

        $memberEntity = $this->haveCustomer('anna', 'Anna', 'Almond');
        $outsiderEntity = $this->haveCustomer('bob', 'Bob', 'Brooks');

        $this->assign($customerGroupEntity, $memberEntity);
        $this->assign($otherCustomerGroupEntity, $outsiderEntity);

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria($customerGroupEntity),
        );

        // Assert
        $this->assertSame(
            [$memberEntity->getCustomerReference()],
            $this->extractReferences($customerCollectionTransfer->getCustomers()),
        );
    }

    public function testReturnsAnEmptyCollectionForAGroupWithoutMembers(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria($customerGroupEntity),
        );

        // Assert
        $this->assertCount(0, $customerCollectionTransfer->getCustomers());
    }

    public function testNarrowsToASingleCustomerReference(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $firstEntity = $this->haveCustomer('anna', 'Anna', 'Almond');
        $secondEntity = $this->haveCustomer('bob', 'Bob', 'Brooks');

        $this->assign($customerGroupEntity, $firstEntity);
        $this->assign($customerGroupEntity, $secondEntity);

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria(
                $customerGroupEntity,
                (new CustomerGroupCustomerConditionsTransfer())
                    ->setCustomerGroupIds([$customerGroupEntity->getIdCustomerGroup()])
                    ->addCustomerReference($secondEntity->getCustomerReference()),
            ),
        );

        // Assert
        $this->assertSame(
            [$secondEntity->getCustomerReference()],
            $this->extractReferences($customerCollectionTransfer->getCustomers()),
            'This narrowed lookup is what gates the member DELETE with a 404.',
        );
    }

    /**
     * @dataProvider provideSortFields
     */
    public function testSortsTheMembers(string $field, bool $isAscending, bool $isFirstExpectedFirst): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $firstEntity = $this->haveCustomer('anna', 'Anna', 'Almond');
        $secondEntity = $this->haveCustomer('bob', 'Bob', 'Brooks');

        $this->assign($customerGroupEntity, $firstEntity);
        $this->assign($customerGroupEntity, $secondEntity);

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria($customerGroupEntity)
                ->addSort((new SortTransfer())->setField($field)->setIsAscending($isAscending)),
        );

        // Assert
        $expectedReferences = [$firstEntity->getCustomerReference(), $secondEntity->getCustomerReference()];

        $this->assertSame(
            $isFirstExpectedFirst ? $expectedReferences : array_reverse($expectedReferences),
            $this->extractReferences($customerCollectionTransfer->getCustomers()),
        );
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public function provideSortFields(): array
    {
        return [
            'email ascending' => ['email', true, true],
            'email descending' => ['email', false, false],
            'firstName ascending' => ['firstName', true, true],
            'firstName descending' => ['firstName', false, false],
            'lastName ascending' => ['lastName', true, true],
            'lastName descending' => ['lastName', false, false],
        ];
    }

    public function testReportsTheTotalCountAlongsideAPageOfMembers(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $this->assign($customerGroupEntity, $this->haveCustomer('anna', 'Anna', 'Almond'));
        $this->assign($customerGroupEntity, $this->haveCustomer('bob', 'Bob', 'Brooks'));
        $this->assign($customerGroupEntity, $this->haveCustomer('cleo', 'Cleo', 'Carter'));

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria($customerGroupEntity)
                ->setPagination((new PaginationTransfer())->setPage(1)->setMaxPerPage(2)),
        );

        // Assert
        $this->assertCount(2, $customerCollectionTransfer->getCustomers());
        $this->assertSame(3, $customerCollectionTransfer->getPaginationOrFail()->getNbResults());
    }

    public function testReturnsIdentificationDataOnly(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerEntity = $this->haveCustomer('anna', 'Anna', 'Almond');
        $this->assign($customerGroupEntity, $customerEntity);

        // Act
        $customerCollectionTransfer = $this->getFacade()->getCustomerCollectionByCustomerGroupCriteria(
            $this->createCriteria($customerGroupEntity),
        );

        // Assert
        $customerTransfer = $customerCollectionTransfer->getCustomers()->offsetGet(0);
        $this->assertSame($customerEntity->getIdCustomer(), $customerTransfer->getIdCustomer());
        $this->assertSame($customerEntity->getEmail(), $customerTransfer->getEmail());
        $this->assertSame('Anna', $customerTransfer->getFirstName());
        $this->assertSame('Almond', $customerTransfer->getLastName());
    }

    protected function createCriteria(
        SpyCustomerGroup $customerGroupEntity,
        ?CustomerGroupCustomerConditionsTransfer $customerGroupCustomerConditionsTransfer = null
    ): CustomerGroupCustomerCriteriaTransfer {
        return (new CustomerGroupCustomerCriteriaTransfer())->setCustomerGroupCustomerConditions(
            $customerGroupCustomerConditionsTransfer
                ?? (new CustomerGroupCustomerConditionsTransfer())
                    ->setCustomerGroupIds([$customerGroupEntity->getIdCustomerGroup()]),
        );
    }

    protected function haveCustomerGroup(): SpyCustomerGroup
    {
        $customerGroupEntity = (new SpyCustomerGroup())->setName(uniqid($this->prefix, true));
        $customerGroupEntity->save();

        return $customerGroupEntity;
    }

    protected function haveCustomer(string $slug, string $firstName, string $lastName): SpyCustomer
    {
        $customerEntity = (new SpyCustomer())
            ->setEmail(sprintf('%s-%s@example.com', $slug, $this->prefix))
            ->setCustomerReference(sprintf('%s-%s', $this->prefix, $slug))
            ->setFirstName($firstName)
            ->setLastName($lastName);

        $customerEntity->save();

        return $customerEntity;
    }

    protected function assign(SpyCustomerGroup $customerGroupEntity, SpyCustomer $customerEntity): void
    {
        (new SpyCustomerGroupToCustomer())
            ->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup())
            ->setFkCustomer($customerEntity->getIdCustomer())
            ->save();
    }

    /**
     * @param iterable<\Generated\Shared\Transfer\CustomerTransfer> $customerTransfers
     *
     * @return array<int, string>
     */
    protected function extractReferences(iterable $customerTransfers): array
    {
        $customerReferences = [];

        foreach ($customerTransfers as $customerTransfer) {
            $customerReferences[] = (string)$customerTransfer->getCustomerReference();
        }

        return $customerReferences;
    }

    protected function getFacade(): CustomerGroupFacadeInterface
    {
        return $this->getLocator()->customerGroup()->facade();
    }
}
