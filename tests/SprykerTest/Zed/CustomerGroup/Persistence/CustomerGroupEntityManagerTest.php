<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Zed\CustomerGroup\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\CustomerGroup\Business\Model\CustomerGroup;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupEntityManager;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupEntityManagerInterface;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupPersistenceFactory;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Persistence
 * @group CustomerGroupEntityManagerTest
 * Add your own group annotations below this line
 */
class CustomerGroupEntityManagerTest extends Unit
{
    protected const int RELATION_COUNT = 10;

    protected string $prefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefix = uniqid('cgem-', false);
    }

    public function testCreateCustomerGroupToCustomerRelationsAssignsEveryCustomer(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(static::RELATION_COUNT);

        // Act
        $this->createEntityManager()->createCustomerGroupToCustomerRelations(
            $customerGroupEntity->getIdCustomerGroup(),
            $customerIds,
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            $customerIds,
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
        );
    }

    public function testCreateCustomerGroupToCustomerRelationsSkipsCustomersAlreadyAssigned(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(3);
        $alreadyAssignedCustomerIds = array_slice($customerIds, 0, 2);
        $this->assign($customerGroupEntity->getIdCustomerGroup(), $alreadyAssignedCustomerIds);

        // Act
        $this->createEntityManager()->createCustomerGroupToCustomerRelations(
            $customerGroupEntity->getIdCustomerGroup(),
            $customerIds,
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            $customerIds,
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            'The already-assigned customers must be skipped rather than hitting the unique constraint '
            . 'on (fk_customer_group, fk_customer), and the new one must still be assigned.',
        );
    }

    public function testDeleteCustomerGroupToCustomerRelationsRemovesOnlyTheGivenCustomers(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(4);
        $this->assign($customerGroupEntity->getIdCustomerGroup(), $customerIds);

        $customerIdsToDeAssign = array_slice($customerIds, 0, 2);
        $customerIdsToKeep = array_slice($customerIds, 2);

        // Act
        $this->createEntityManager()->deleteCustomerGroupToCustomerRelations(
            $customerGroupEntity->getIdCustomerGroup(),
            $customerIdsToDeAssign,
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            $customerIdsToKeep,
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
        );
    }

    public function testDeleteCustomerGroupToCustomerRelationsIgnoresCustomersOfOtherGroups(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $otherCustomerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(2);

        $this->assign($customerGroupEntity->getIdCustomerGroup(), $customerIds);
        $this->assign($otherCustomerGroupEntity->getIdCustomerGroup(), $customerIds);

        // Act
        $this->createEntityManager()->deleteCustomerGroupToCustomerRelations(
            $customerGroupEntity->getIdCustomerGroup(),
            $customerIds,
        );

        // Assert
        $this->assertSame([], $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()));
        $this->assertEqualsCanonicalizing(
            $customerIds,
            $this->getAssignedCustomerIds($otherCustomerGroupEntity->getIdCustomerGroup()),
            'The other group keeps the same customers.',
        );
    }

    /**
     * @dataProvider provideEmptyCustomerIdListCases
     */
    public function testWritesNothingForAnEmptyCustomerIdList(string $method): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(2);
        $this->assign($customerGroupEntity->getIdCustomerGroup(), $customerIds);

        // Act
        $this->createEntityManager()->{$method}($customerGroupEntity->getIdCustomerGroup(), []);

        // Assert
        $this->assertEqualsCanonicalizing(
            $customerIds,
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            sprintf('%s() must leave the assignment alone for an empty list.', $method),
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideEmptyCustomerIdListCases(): array
    {
        return [
            'assign' => ['createCustomerGroupToCustomerRelations'],
            'de-assign' => ['deleteCustomerGroupToCustomerRelations'],
        ];
    }

    public function testCustomerGroupModelIsConstructableWithoutAnEntityManager(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup();
        $customerIds = $this->haveCustomerIds(3);
        $this->assign($customerGroupEntity->getIdCustomerGroup(), $customerIds);

        $customerIdsToDeAssign = array_slice($customerIds, 0, 2);
        $customerIdsToKeep = array_slice($customerIds, 2);

        $customerGroup = new CustomerGroup(new CustomerGroupQueryContainer());

        // Act
        $customerGroup->removeCustomersFromGroup(
            (new CustomerGroupTransfer())
                ->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())
                ->setCustomerAssignment(
                    (new CustomerGroupToCustomerAssignmentTransfer())
                        ->setIdsCustomerToAssign([])
                        ->setIdsCustomerToDeAssign($customerIdsToDeAssign),
                ),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            $customerIdsToKeep,
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            'The legacy path must still de-assign, not silently no-op on a null entity manager.',
        );
    }

    protected function createEntityManager(): CustomerGroupEntityManagerInterface
    {
        $customerGroupEntityManager = new CustomerGroupEntityManager();
        $customerGroupEntityManager->setFactory(new CustomerGroupPersistenceFactory());

        return $customerGroupEntityManager;
    }

    protected function haveCustomerGroup(): SpyCustomerGroup
    {
        $customerGroupEntity = (new SpyCustomerGroup())
            ->setName(uniqid(sprintf('%s-group-', $this->prefix), false));

        $customerGroupEntity->save();

        return $customerGroupEntity;
    }

    /**
     * @return array<int, int>
     */
    protected function haveCustomerIds(int $count): array
    {
        $customerIds = [];

        for ($i = 0; $i < $count; $i++) {
            $slug = sprintf('%s-%d', $this->prefix, $i);

            $customerEntity = (new SpyCustomer())
                ->setEmail(sprintf('%s@example.com', $slug))
                ->setCustomerReference($slug)
                ->setFirstName('Anna')
                ->setLastName('Almond');

            $customerEntity->save();

            $customerIds[] = $customerEntity->getIdCustomer();
        }

        return $customerIds;
    }

    /**
     * @param array<int, int> $customerIds
     */
    protected function assign(int $idCustomerGroup, array $customerIds): void
    {
        foreach ($customerIds as $idCustomer) {
            (new SpyCustomerGroupToCustomer())
                ->setFkCustomerGroup($idCustomerGroup)
                ->setFkCustomer($idCustomer)
                ->save();
        }
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
            ->getData();

        return array_map('intval', $customerIds);
    }
}
