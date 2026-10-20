<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CustomerGroupBuilder;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use RuntimeException;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupPostSavePluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Facade
 * @group CustomerGroupPostSavePluginStackTest
 * Add your own group annotations below this line
 */
class CustomerGroupPostSavePluginStackTest extends Unit
{
    protected const string DESCRIPTION_UPDATED = 'Updated description';

    protected const string EXCEPTION_MESSAGE = 'Post-save plugin failed.';

    protected const string DESCRIPTION_EXPANDED = 'Description set by the post-save plugin';

    protected CustomerGroupBusinessTester $tester;

    public function testAddExecutesPostSavePluginOnceWithTheNewCustomerGroupId(): void
    {
        // Arrange
        $customerGroupPostSavePluginMock = $this->createMock(CustomerGroupPostSavePluginInterface::class);
        $customerGroupPostSavePluginMock->expects($this->once())
            ->method('postSave')
            ->with($this->callback(function (CustomerGroupTransfer $customerGroupTransfer): bool {
                return $customerGroupTransfer->getIdCustomerGroup() !== null;
            }))
            ->willReturnArgument(0);

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE,
            [$customerGroupPostSavePluginMock],
        );

        // Act
        $customerGroupTransfer = $this->getFacade()->add((new CustomerGroupBuilder())->build());

        // Assert
        $this->assertNotNull($customerGroupTransfer->getIdCustomerGroup());
    }

    public function testUpdateExecutesPostSavePluginOnceWithTheUpdatedCustomerGroup(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $idCustomerGroup = $customerGroupTransfer->getIdCustomerGroup();

        $customerGroupPostSavePluginMock = $this->createMock(CustomerGroupPostSavePluginInterface::class);
        $customerGroupPostSavePluginMock->expects($this->once())
            ->method('postSave')
            ->with($this->callback(function (CustomerGroupTransfer $customerGroupTransfer) use ($idCustomerGroup): bool {
                return $customerGroupTransfer->getIdCustomerGroup() === $idCustomerGroup
                    && $customerGroupTransfer->getDescription() === static::DESCRIPTION_UPDATED;
            }))
            ->willReturnArgument(0);

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE,
            [$customerGroupPostSavePluginMock],
        );

        // Act
        $this->getFacade()->update($customerGroupTransfer->setDescription(static::DESCRIPTION_UPDATED));

        // Assert
        $this->assertSame(
            static::DESCRIPTION_UPDATED,
            SpyCustomerGroupQuery::create()->findPk($idCustomerGroup)->getDescription(),
        );
    }

    public function testUpdateCustomerGroupCollectionReturnsTheCustomerGroupThePostSavePluginReturns(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();

        $customerGroupPostSavePluginMock = $this->createMock(CustomerGroupPostSavePluginInterface::class);
        $customerGroupPostSavePluginMock->method('postSave')
            ->willReturnCallback(function (CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer {
                return (new CustomerGroupTransfer())
                    ->fromArray($customerGroupTransfer->toArray())
                    ->setDescription(static::DESCRIPTION_EXPANDED);
            });

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE,
            [$customerGroupPostSavePluginMock],
        );

        // Act
        $customerGroupCollectionResponseTransfer = $this->getFacade()->updateCustomerGroupCollection(
            (new CustomerGroupCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCustomerGroup($customerGroupTransfer->setDescription(static::DESCRIPTION_UPDATED)),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::DESCRIPTION_EXPANDED,
            $customerGroupCollectionResponseTransfer->getCustomerGroups()->offsetGet(0)->getDescription(),
        );
    }

    /**
     * @disableTransaction
     */
    public function testAddRollsTheCustomerGroupBackWhenAPostSavePluginThrows(): void
    {
        // Arrange
        $customerGroupTransfer = (new CustomerGroupBuilder())->build();

        $customerGroupPostSavePluginMock = $this->createMock(CustomerGroupPostSavePluginInterface::class);
        $customerGroupPostSavePluginMock->method('postSave')
            ->willThrowException(new RuntimeException(static::EXCEPTION_MESSAGE));

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE,
            [$customerGroupPostSavePluginMock],
        );

        // Act
        $caughtException = null;

        try {
            $this->getFacade()->add($customerGroupTransfer);
        } catch (RuntimeException $runtimeException) {
            $caughtException = $runtimeException;
        }

        // Assert
        $this->assertNotNull($caughtException);
        $this->assertSame(static::EXCEPTION_MESSAGE, $caughtException->getMessage());
        $this->assertFalse(SpyCustomerGroupQuery::create()->filterByName($customerGroupTransfer->getName())->exists());
    }

    /**
     * @disableTransaction
     */
    public function testUpdateRollsTheCustomerGroupChangesBackWhenAPostSavePluginThrows(): void
    {
        // Arrange
        $customerGroupTransfer = $this->tester->haveCustomerGroup();
        $originalDescription = $customerGroupTransfer->getDescription();

        $customerGroupPostSavePluginMock = $this->createMock(CustomerGroupPostSavePluginInterface::class);
        $customerGroupPostSavePluginMock->method('postSave')
            ->willThrowException(new RuntimeException(static::EXCEPTION_MESSAGE));

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_POST_SAVE,
            [$customerGroupPostSavePluginMock],
        );

        // Act
        $caughtException = null;

        try {
            $this->getFacade()->update($customerGroupTransfer->setDescription(static::DESCRIPTION_UPDATED));
        } catch (RuntimeException $runtimeException) {
            $caughtException = $runtimeException;
        }

        // Assert
        $this->assertNotNull($caughtException);
        $this->assertSame(static::EXCEPTION_MESSAGE, $caughtException->getMessage());
        $this->assertNotSame(static::DESCRIPTION_UPDATED, $originalDescription);
        $this->assertSame(
            $originalDescription,
            SpyCustomerGroupQuery::create()->findPk($customerGroupTransfer->getIdCustomerGroupOrFail())->getDescription(),
        );
    }

    protected function getFacade(): CustomerGroupFacadeInterface
    {
        /** @var \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface $customerGroupFacade */
        $customerGroupFacade = $this->tester->getFacade();

        return $customerGroupFacade;
    }
}
