<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Shared\Kernel\Transfer\Exception\NullValueException;
use Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface;
use Spryker\Zed\CustomerGroup\Business\Reader\CustomerGroupCollectionReader;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupProviderPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Business
 * @group Facade
 * @group GetCustomerGroupCollectionByCustomerTest
 * Add your own group annotations below this line
 */
class GetCustomerGroupCollectionByCustomerTest extends Unit
{
    protected const int ID_CUSTOMER = 999999901;

    protected const int ID_SECOND_CUSTOMER = 999999903;

    protected const int ID_THIRD_CUSTOMER = 999999904;

    protected const int ID_FOURTH_CUSTOMER = 999999905;

    protected const int ID_OTHER_CUSTOMER_GROUP = 999999906;

    protected const string CHANGED_CUSTOMER_GROUP_NAME = 'Changed by the caller';

    protected const int ID_COMPANY_USER = 999999907;

    protected const int ID_OTHER_COMPANY_USER = 999999908;

    protected const string CUSTOMER_GROUP_NAME = 'Cached group';

    protected CustomerGroupBusinessTester $tester;

    public function testReturnsTheDirectGroupsOfTheCustomerWhenNoProviderPluginIsRegistered(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $assignedCustomerGroupTransfer = $this->haveCustomerGroupWithCustomer($customerTransfer);
        $this->tester->haveCustomerGroup();
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_PROVIDER, []);

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollectionByCustomer($customerTransfer);

        // Assert
        $this->assertSame(
            [$assignedCustomerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->getCustomerGroupIds($customerGroupCollectionTransfer),
        );
    }

    public function testAddsTheGroupsOfTheProviderPluginsAndPassesTheCustomerToThem(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $directCustomerGroupTransfer = $this->haveCustomerGroupWithCustomer($customerTransfer);
        $providedCustomerGroupTransfer = $this->tester->haveCustomerGroup();

        $customerGroupProviderPluginMock = $this->createMock(CustomerGroupProviderPluginInterface::class);
        $customerGroupProviderPluginMock->expects($this->once())
            ->method('provideCustomerGroups')
            ->with($this->callback(fn (CustomerTransfer $passedCustomerTransfer): bool => $passedCustomerTransfer->getIdCustomer() === $customerTransfer->getIdCustomer()))
            ->willReturn((new CustomerGroupCollectionTransfer())->addGroup($providedCustomerGroupTransfer));
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_PROVIDER,
            [$customerGroupProviderPluginMock],
        );

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollectionByCustomer($customerTransfer);

        // Assert
        $this->assertSame(
            [$directCustomerGroupTransfer->getIdCustomerGroupOrFail(), $providedCustomerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->getCustomerGroupIds($customerGroupCollectionTransfer),
        );
    }

    public function testReturnsAGroupOnceWhenItIsBothDirectAndProvidedByProviderPlugins(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $directCustomerGroupTransfer = $this->haveCustomerGroupWithCustomer($customerTransfer);
        $providedCustomerGroupTransfer = $this->tester->haveCustomerGroup();
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_PROVIDER, [
            $this->createProviderPluginReturningGroups([$directCustomerGroupTransfer, $providedCustomerGroupTransfer]),
            $this->createProviderPluginReturningGroups([$providedCustomerGroupTransfer]),
        ]);

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollectionByCustomer($customerTransfer);

        // Assert
        $this->assertSame(
            [$directCustomerGroupTransfer->getIdCustomerGroupOrFail(), $providedCustomerGroupTransfer->getIdCustomerGroupOrFail()],
            $this->getCustomerGroupIds($customerGroupCollectionTransfer),
        );
    }

    public function testResolvesTheGroupsOnceAndReturnsAnIndependentCollectionWhileTheSameCustomerIsEvaluated(): void
    {
        // Arrange
        $customerGroupCollectionReader = $this->createCustomerGroupCollectionReaderWithGroupPerCustomerAndCompanyUser(1);
        $customerTransfer = (new CustomerTransfer())->setIdCustomer(static::ID_CUSTOMER);

        // Act
        $firstCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($customerTransfer);
        $firstCustomerGroupCollectionTransfer->getGroups()->offsetGet(0)->setName(static::CHANGED_CUSTOMER_GROUP_NAME);
        $firstCustomerGroupCollectionTransfer->addGroup((new CustomerGroupTransfer())->setIdCustomerGroup(static::ID_OTHER_CUSTOMER_GROUP));
        $secondCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($customerTransfer);

        // Assert
        $this->assertNotSame($firstCustomerGroupCollectionTransfer, $secondCustomerGroupCollectionTransfer);
        $this->assertSame([static::ID_CUSTOMER], $this->getCustomerGroupIds($secondCustomerGroupCollectionTransfer));
        $this->assertSame(static::CUSTOMER_GROUP_NAME, $secondCustomerGroupCollectionTransfer->getGroups()->offsetGet(0)->getName());
    }

    public function testResolvesTheGroupsAgainWhenTheCustomerShopsAsAnotherCompanyUser(): void
    {
        // Arrange
        $customerGroupCollectionReader = $this->createCustomerGroupCollectionReaderWithGroupPerCustomerAndCompanyUser(2);
        $customerTransfer = (new CustomerTransfer())
            ->setIdCustomer(static::ID_SECOND_CUSTOMER)
            ->setCompanyUserTransfer((new CompanyUserTransfer())->setIdCompanyUser(static::ID_COMPANY_USER));
        $otherCompanyUserCustomerTransfer = (clone $customerTransfer)
            ->setCompanyUserTransfer((new CompanyUserTransfer())->setIdCompanyUser(static::ID_OTHER_COMPANY_USER));

        // Act
        $customerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($customerTransfer);
        $otherCompanyUserCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($otherCompanyUserCustomerTransfer);

        // Assert
        $this->assertSame([static::ID_SECOND_CUSTOMER, static::ID_COMPANY_USER], $this->getCustomerGroupIds($customerGroupCollectionTransfer));
        $this->assertSame(
            [static::ID_SECOND_CUSTOMER, static::ID_OTHER_COMPANY_USER],
            $this->getCustomerGroupIds($otherCompanyUserCustomerGroupCollectionTransfer),
        );
    }

    public function testResolvesTheGroupsOfACustomerAgainAfterAnotherCustomerWasEvaluated(): void
    {
        // Arrange
        $customerGroupCollectionReader = $this->createCustomerGroupCollectionReaderWithGroupPerCustomerAndCompanyUser(3);
        $customerTransfer = (new CustomerTransfer())->setIdCustomer(static::ID_THIRD_CUSTOMER);
        $otherCustomerTransfer = (new CustomerTransfer())->setIdCustomer(static::ID_FOURTH_CUSTOMER);

        // Act
        $firstCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($customerTransfer);
        $otherCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($otherCustomerTransfer);
        $secondCustomerGroupCollectionTransfer = $customerGroupCollectionReader->getCustomerGroupCollectionByCustomer($customerTransfer);

        // Assert
        $this->assertSame([static::ID_THIRD_CUSTOMER], $this->getCustomerGroupIds($firstCustomerGroupCollectionTransfer));
        $this->assertSame([static::ID_FOURTH_CUSTOMER], $this->getCustomerGroupIds($otherCustomerGroupCollectionTransfer));
        $this->assertSame([static::ID_THIRD_CUSTOMER], $this->getCustomerGroupIds($secondCustomerGroupCollectionTransfer));
    }

    public function testThrowsWhenTheCustomerHasNoId(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_PROVIDER, []);

        // Expect
        $this->expectException(NullValueException::class);

        // Act
        $this->getFacade()->getCustomerGroupCollectionByCustomer(new CustomerTransfer());
    }

    protected function getFacade(): CustomerGroupFacadeInterface
    {
        /** @var \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface $customerGroupFacade */
        $customerGroupFacade = $this->tester->getFacade();

        return $customerGroupFacade;
    }

    protected function createCustomerGroupCollectionReaderWithGroupPerCustomerAndCompanyUser(int $expectedResolveCount): CustomerGroupCollectionReader
    {
        $customerGroupRepositoryMock = $this->createMock(CustomerGroupRepositoryInterface::class);
        $customerGroupRepositoryMock->expects($this->exactly($expectedResolveCount))
            ->method('getCustomerGroupCollectionByIdCustomer')
            ->willReturnCallback(fn (int $idCustomer): CustomerGroupCollectionTransfer => (new CustomerGroupCollectionTransfer())
                ->addGroup((new CustomerGroupTransfer())->setIdCustomerGroup($idCustomer)->setName(static::CUSTOMER_GROUP_NAME)));
        $customerGroupProviderPluginMock = $this->createMock(CustomerGroupProviderPluginInterface::class);
        $customerGroupProviderPluginMock->expects($this->exactly($expectedResolveCount))
            ->method('provideCustomerGroups')
            ->willReturnCallback(function (CustomerTransfer $customerTransfer): CustomerGroupCollectionTransfer {
                $idCompanyUser = $customerTransfer->getCompanyUserTransfer()?->getIdCompanyUser();

                if ($idCompanyUser === null) {
                    return new CustomerGroupCollectionTransfer();
                }

                return (new CustomerGroupCollectionTransfer())->addGroup((new CustomerGroupTransfer())->setIdCustomerGroup($idCompanyUser));
            });

        return new CustomerGroupCollectionReader($customerGroupRepositoryMock, [$customerGroupProviderPluginMock]);
    }

    protected function haveCustomerGroupWithCustomer(CustomerTransfer $customerTransfer): CustomerGroupTransfer
    {
        return $this->tester->haveCustomerGroup([
            CustomerGroupTransfer::CUSTOMER_ASSIGNMENT => [
                CustomerGroupToCustomerAssignmentTransfer::IDS_CUSTOMER_TO_ASSIGN => [$customerTransfer->getIdCustomerOrFail()],
            ],
        ]);
    }

    /**
     * @param list<\Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     */
    protected function createProviderPluginReturningGroups(array $customerGroupTransfers): CustomerGroupProviderPluginInterface
    {
        $customerGroupCollectionTransfer = new CustomerGroupCollectionTransfer();

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $customerGroupCollectionTransfer->addGroup($customerGroupTransfer);
        }

        $customerGroupProviderPluginMock = $this->createMock(CustomerGroupProviderPluginInterface::class);
        $customerGroupProviderPluginMock->method('provideCustomerGroups')->willReturn($customerGroupCollectionTransfer);

        return $customerGroupProviderPluginMock;
    }

    /**
     * @return list<int>
     */
    protected function getCustomerGroupIds(CustomerGroupCollectionTransfer $customerGroupCollectionTransfer): array
    {
        $customerGroupIds = [];

        foreach ($customerGroupCollectionTransfer->getGroups() as $customerGroupTransfer) {
            $customerGroupIds[] = $customerGroupTransfer->getIdCustomerGroupOrFail();
        }

        return $customerGroupIds;
    }
}
