<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacade;
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
 * @group CustomerGroupFacadeTest
 * Add your own group annotations below this line
 */
class CustomerGroupFacadeTest extends Unit
{
    use LocatorHelperTrait;

    public function testGetValid(): void
    {
        $customerGroupEntity = $this->createCustomerGroup();

        $customerEntity = $this->createCustomer();

        $customerGroupToCustomerEntity = new SpyCustomerGroupToCustomer();
        $customerGroupToCustomerEntity->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup());
        $customerGroupToCustomerEntity->setFkCustomer($customerEntity->getIdCustomer());
        $customerGroupToCustomerEntity->save();

        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup());

        $resultTransfer = $customerGroupFacade->get($customerGroupTransfer);
        $this->assertSame($customerGroupEntity->getName(), $resultTransfer->getName());

        $customers = $resultTransfer->getCustomers();
        foreach ($customers as $customer) {
            $this->assertSame($customerEntity->getIdCustomer(), $customer->getFkCustomer());
        }
    }

    public function testFindCustomerGroupByIdCustomerShouldReturnGroupTransferWhenValidIdGiven(): void
    {
        $customerGroupEntity = $this->createCustomerGroup();

        $customerEntity = $this->createCustomer();

        $customerGroupToCustomerEntity = new SpyCustomerGroupToCustomer();
        $customerGroupToCustomerEntity->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup());
        $customerGroupToCustomerEntity->setFkCustomer($customerEntity->getIdCustomer());
        $customerGroupToCustomerEntity->save();

        $customerGroupFacade = $this->createCustomerGroupFacade();
        $customerGroupTransfer = $customerGroupFacade->findCustomerGroupByIdCustomer($customerEntity->getIdCustomer());

        $this->assertNotEmpty($customerGroupTransfer);
        $this->assertSame($customerGroupEntity->getName(), $customerGroupTransfer->getName());
    }

    protected function assignCustomerToGroup(int $idCustomer, int $idGroup): int
    {
        return (new SpyCustomerGroupToCustomer())
            ->setFkCustomer($idCustomer)
            ->setFkCustomerGroup($idGroup)
            ->save();
    }

    public function testFindCustomerGroupsByIdCustomer(): void
    {
        $customerEntity = $this->createCustomer();

        $customerGroupFirstEntity = $this->createCustomerGroup();
        $customerGroupSecondEntity = $this->createCustomerGroup();

        $customerGroupToCustomerEntities = [];

        $customerGroupToCustomerEntities[] = $this->assignCustomerToGroup(
            $customerEntity->getIdCustomer(),
            $customerGroupFirstEntity->getIdCustomerGroup(),
        );

        $customerGroupToCustomerEntities[] = $this->assignCustomerToGroup(
            $customerEntity->getIdCustomer(),
            $customerGroupSecondEntity->getIdCustomerGroup(),
        );

        $customerGroupCollectionTransfer = $this->getCustomerGroupFacade()
            ->getCustomerGroupCollectionByIdCustomer($customerEntity->getIdCustomer());

        $this->assertCount(count($customerGroupToCustomerEntities), $customerGroupCollectionTransfer->getGroups());
    }

    public function testAddValid(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerEntityOne = $this->createCustomer();
        $customerEntityTwo = $this->createCustomer('two@second.de', 'Second', 'Two', 'two');

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setName('Foo');
        $customerGroupTransfer->setDescription('Descr');

        $customerGroupToCustomerTransfer = new CustomerGroupToCustomerTransfer();
        $customerGroupToCustomerTransfer->setFkCustomer($customerEntityOne->getIdCustomer());
        $customerGroupTransfer->addCustomer($customerGroupToCustomerTransfer);

        $customerGroupToCustomerTransfer = new CustomerGroupToCustomerTransfer();
        $customerGroupToCustomerTransfer->setFkCustomer($customerEntityTwo->getIdCustomer());
        $customerGroupTransfer->addCustomer($customerGroupToCustomerTransfer);

        $resultTransfer = $customerGroupFacade->add($customerGroupTransfer);
        $this->assertNotEmpty($resultTransfer->getIdCustomerGroup());
    }

    public function testUpdateValid(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroup = [
            'name' => 'Test' . time(),
            'description' => 'Test' . time(),
        ];

        $customerGroupEntity = new SpyCustomerGroup();
        $customerGroupEntity->fromArray($customerGroup);
        $customerGroupEntity->save();

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->fromArray($customerGroupEntity->toArray(), true);

        $customerGroupTransfer->setName('Foo');
        $customerGroupTransfer->setDescription('Descr');

        $customerGroupFacade->update($customerGroupTransfer);

        $customerGroupQuery = SpyCustomerGroupQuery::create();
        $updatedCustomerGroupEntity = $customerGroupQuery->filterByIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())->findOne();

        $this->assertSame('Foo', $updatedCustomerGroupEntity->getName());
        $this->assertSame('Descr', $updatedCustomerGroupEntity->getDescription());
    }

    /**
     * We remove one customer and add another one.
     *
     * @return void
     */
    public function testUpdateCustomersValid(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroup = [
            'name' => 'Test' . time(),
        ];

        $customerGroupEntity = new SpyCustomerGroup();
        $customerGroupEntity->fromArray($customerGroup);
        $customerGroupEntity->save();

        $customerEntityOne = $this->createCustomer();

        $customerGroupToCustomerEntity = new SpyCustomerGroupToCustomer();
        $customerGroupToCustomerEntity->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup());
        $customerGroupToCustomerEntity->setFkCustomer($customerEntityOne->getIdCustomer());
        $customerGroupToCustomerEntity->save();

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->fromArray($customerGroupEntity->toArray(), true);

        $customerEntityTwo = $this->createCustomer('two@second.de', 'Second', 'Two', 'two');
        $customerGroupTransfer->setCustomerAssignment(
            (new CustomerGroupToCustomerAssignmentTransfer())
                ->addIdCustomerToAssign($customerEntityTwo->getIdCustomer())
                ->addIdCustomerToDeAssign($customerEntityOne->getIdCustomer()),
        );

        $customerGroupTransfer->setName('Foo');
        $customerGroupTransfer->setDescription('Descr');

        $customerGroupFacade->update($customerGroupTransfer);

        $customerGroupToCustomerQuery = SpyCustomerGroupToCustomerQuery::create();
        $customerGroupToCustomerArray = $customerGroupToCustomerQuery->filterByFkCustomerGroup($customerGroupEntity->getIdCustomerGroup())->find()->toArray();

        $this->assertCount(1, $customerGroupToCustomerArray);
        $this->assertSame($customerEntityTwo->getIdCustomer(), $customerGroupToCustomerArray[0]['FkCustomer']);
    }

    public function testDeleteValid(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroupEntity = $this->createCustomerGroup();

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup());

        $customerGroupFacade->delete($customerGroupTransfer);

        $customerGroupQuery = SpyCustomerGroupQuery::create();
        $customerGroupEntity = $customerGroupQuery->filterByIdCustomerGroup($customerGroupEntity->getIdCustomerGroup())->findOne();

        $this->assertNull($customerGroupEntity);
    }

    public function testRemoveCustomersFromGroupValid(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroupEntity = $this->createCustomerGroup();

        $customerEntity = $this->createCustomer();

        $customerGroupToCustomerEntity = new SpyCustomerGroupToCustomer();
        $customerGroupToCustomerEntity->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup());
        $customerGroupToCustomerEntity->setFkCustomer($customerEntity->getIdCustomer());
        $customerGroupToCustomerEntity->save();

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup());
        $customerGroupTransfer->setCustomerAssignment(
            (new CustomerGroupToCustomerAssignmentTransfer())
                ->addIdCustomerToDeAssign($customerEntity->getIdCustomer()),
        );

        $customerGroupFacade->removeCustomersFromGroup($customerGroupTransfer);

        $customerGroupToCustomerQuery = SpyCustomerGroupToCustomerQuery::create();
        $customerEntity = $customerGroupToCustomerQuery
            ->filterByFkCustomerGroup($customerGroupEntity->getIdCustomerGroup())
            ->filterByFkCustomer($customerEntity->getIdCustomer())
            ->findOne();

        $this->assertNull($customerEntity);
    }

    public function testRemoveCustomerFromAllGroups(): void
    {
        $customerGroupFacade = $this->createCustomerGroupFacade();

        $customerGroupEntity1 = $this->createCustomerGroup();
        $customerGroupEntity2 = $this->createCustomerGroup();

        $customerEntity = $this->createCustomer();

        $this->createCustomerToGroup($customerEntity->getIdCustomer(), $customerGroupEntity1->getIdCustomerGroup());
        $this->createCustomerToGroup($customerEntity->getIdCustomer(), $customerGroupEntity2->getIdCustomerGroup());

        $customerTransfer = new CustomerTransfer();
        $customerTransfer->setIdCustomer($customerEntity->getIdCustomer());

        $customerGroupFacade->removeCustomerFromAllGroups($customerTransfer);

        $customerGroupTransfer = $customerGroupFacade->findCustomerGroupByIdCustomer($customerEntity->getIdCustomer());

        $this->assertNull($customerGroupTransfer);
    }

    protected function createCustomer(
        string $email = 'one@first.de',
        string $lastName = 'First',
        string $firstName = 'One',
        string $reference = 'one'
    ): SpyCustomer {
        $customerEntity = new SpyCustomer();
        $customerEntity->setFirstName($firstName);
        $customerEntity->setFirstName($lastName);
        $customerEntity->setCustomerReference($reference);
        $customerEntity->setEmail($email);

        $customerEntity->save();

        return $customerEntity;
    }

    protected function createCustomerGroup(): SpyCustomerGroup
    {
        $customerGroupEntity = (new SpyCustomerGroup())
            ->setName('Test' . uniqid(true));

        $customerGroupEntity->save();

        return $customerGroupEntity;
    }

    protected function createCustomerGroupFacade(): CustomerGroupFacade
    {
        return new CustomerGroupFacade();
    }

    protected function createCustomerToGroup(int $idCustomer, int $idCustomerGroup): SpyCustomerGroupToCustomer
    {
        $customerGroupToCustomerEntity = (new SpyCustomerGroupToCustomer())
            ->setFkCustomerGroup($idCustomerGroup)
            ->setFkCustomer($idCustomer);

        $customerGroupToCustomerEntity->save();

        return $customerGroupToCustomerEntity;
    }

    protected function getCustomerGroupFacade(): CustomerGroupFacadeInterface
    {
        return $this->getLocator()->customerGroup()->facade();
    }
}
