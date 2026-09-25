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
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
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
 * @group UpdateCustomerGroupCollectionTest
 * Add your own group annotations below this line
 */
class UpdateCustomerGroupCollectionTest extends Unit
{
    protected CustomerGroupBusinessTester $tester;

    protected string $prefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefix = uniqid('cgu-', false);
    }

    public function testRenamesTheGroupWithoutChangingItsUuid(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $newName = $this->prefix . '-renamed';

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())
                    ->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())
                    ->setName($newName)
                    ->setDescription($customerGroupEntity->getDescription()),
            ]),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());

        $reloadedEntity = SpyCustomerGroupQuery::create()->findPk($customerGroupEntity->getIdCustomerGroup());
        $this->assertSame($newName, $reloadedEntity->getName());
        $this->assertSame($customerGroupEntity->getUuid(), $reloadedEntity->getUuid());
    }

    public function testAcceptsTheGroupsOwnNameUnchanged(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())
                    ->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())
                    ->setName($customerGroupEntity->getName()),
            ]),
        );

        // Assert
        $this->assertCount(
            0,
            $customerGroupCollectionResponseTransfer->getErrors(),
            'Uniqueness must ignore the group being updated, or no group could ever be edited.',
        );
    }

    public function testRejectsRenamingToANameAnotherGroupUses(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $otherCustomerGroupEntity = $this->haveCustomerGroup('beta');

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                (new CustomerGroupTransfer())
                    ->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())
                    ->setName($otherCustomerGroupEntity->getName()),
            ]),
        );

        // Assert
        $this->assertCount(1, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            CustomerGroupNameUniquenessValidator::GLOSSARY_KEY_ERROR_NAME_TAKEN,
            $customerGroupCollectionResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
    }

    public function testAnAdditiveAssignmentKeepsTheMembersItDidNotMention(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $assignedCustomerEntity = $this->haveCustomer('anna');
        $addedCustomerEntity = $this->haveCustomer('bob');
        $this->assign($customerGroupEntity, $assignedCustomerEntity);

        // Act
        $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                $this->createUpdateTransfer($customerGroupEntity)
                    ->setHasAssignmentChange(true)
                    ->setCustomerReferences([$addedCustomerEntity->getCustomerReference()]),
            ]),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$assignedCustomerEntity->getIdCustomer(), $addedCustomerEntity->getIdCustomer()],
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            'Without isAssignmentReplacement the request only adds.',
        );
    }

    public function testAReplacementAssignmentRemovesTheMembersItDidNotMention(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $keptCustomerEntity = $this->haveCustomer('anna');
        $droppedCustomerEntity = $this->haveCustomer('bob');
        $this->assign($customerGroupEntity, $keptCustomerEntity);
        $this->assign($customerGroupEntity, $droppedCustomerEntity);

        // Act
        $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                $this->createUpdateTransfer($customerGroupEntity)
                    ->setHasAssignmentChange(true)
                    ->setIsAssignmentReplacement(true)
                    ->setCustomerReferences([$keptCustomerEntity->getCustomerReference()]),
            ]),
        );

        // Assert
        $this->assertSame(
            [$keptCustomerEntity->getIdCustomer()],
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
        );
    }

    public function testReAssigningAnExistingMemberIsANoOpRatherThanAConstraintViolation(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $customerEntity = $this->haveCustomer('anna');
        $this->assign($customerGroupEntity, $customerEntity);

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                $this->createUpdateTransfer($customerGroupEntity)
                    ->setHasAssignmentChange(true)
                    ->setCustomerReferences([$customerEntity->getCustomerReference()]),
            ]),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            [$customerEntity->getIdCustomer()],
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            'The delta drops members that are already assigned, so UNIQUE(fk_customer_group, fk_customer) holds.',
        );
    }

    public function testAReplacementByAnEmptyListClearsTheGroupWithoutDeletingIt(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $this->assign($customerGroupEntity, $this->haveCustomer('anna'));

        // Act
        $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                $this->createUpdateTransfer($customerGroupEntity)
                    ->setHasAssignmentChange(true)
                    ->setIsAssignmentReplacement(true)
                    ->setCustomerReferences([]),
            ]),
        );

        // Assert
        $this->assertSame([], $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()));
        $this->assertNotNull(
            SpyCustomerGroupQuery::create()->findPk($customerGroupEntity->getIdCustomerGroup()),
            'Clearing the assignment must leave the group itself in place.',
        );
    }

    public function testLeavesTheAssignmentUntouchedWhenNoAssignmentChangeIsFlagged(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $customerEntity = $this->haveCustomer('anna');
        $this->assign($customerGroupEntity, $customerEntity);

        // Act
        $this->getFacade()->updateCustomerGroupCollection(
            $this->createRequest([
                $this->createUpdateTransfer($customerGroupEntity)->setName($this->prefix . '-renamed'),
            ]),
        );

        // Assert
        $this->assertSame(
            [$customerEntity->getIdCustomer()],
            $this->getAssignedCustomerIds($customerGroupEntity->getIdCustomerGroup()),
            'An omitted assignment is not an empty one.',
        );
    }

    protected function createUpdateTransfer(SpyCustomerGroup $customerGroupEntity): CustomerGroupTransfer
    {
        return (new CustomerGroupTransfer())
            ->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())
            ->setName($customerGroupEntity->getName())
            ->setDescription($customerGroupEntity->getDescription());
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     */
    protected function createRequest(array $customerGroupTransfers): CustomerGroupCollectionRequestTransfer
    {
        $customerGroupCollectionRequestTransfer = (new CustomerGroupCollectionRequestTransfer())
            ->setIsTransactional(true);

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $customerGroupCollectionRequestTransfer->addCustomerGroup($customerGroupTransfer);
        }

        return $customerGroupCollectionRequestTransfer;
    }

    protected function haveCustomerGroup(string $nameSuffix): SpyCustomerGroup
    {
        $customerGroupEntity = (new SpyCustomerGroup())
            ->setName(sprintf('%s-%s', $this->prefix, $nameSuffix))
            ->setDescription('Tier one');

        $customerGroupEntity->save();

        return $customerGroupEntity;
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

    protected function assign(SpyCustomerGroup $customerGroupEntity, SpyCustomer $customerEntity): void
    {
        (new SpyCustomerGroupToCustomer())
            ->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup())
            ->setFkCustomer($customerEntity->getIdCustomer())
            ->save();
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
