<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Tabs;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Tabs
 * @group CustomerGroupFormTabsTest
 * Add your own group annotations below this line
 */
class CustomerGroupFormTabsTest extends Unit
{
    /**
     * @uses \Spryker\Zed\CustomerGroup\CustomerGroupConfig::FORM_TAB_NAME_GENERAL
     */
    protected const string TAB_NAME_GENERAL = 'general';

    /**
     * @uses \Spryker\Zed\CustomerGroup\CustomerGroupConfig::FORM_TAB_NAME_CUSTOMER_ASSIGNMENT
     */
    protected const string TAB_NAME_CUSTOMER_ASSIGNMENT = 'customer-assignment';

    protected const string TAB_NAME_EXPANDED = 'expanded-tab';

    protected const string TAB_TITLE_GENERAL = 'General Information';

    protected const string TAB_TITLE_CUSTOMER_ASSIGNMENT = 'Customers';

    protected const string TAB_TITLE_EXPANDED = 'Expanded tab';

    protected CustomerGroupCommunicationTester $tester;

    public function testCreateViewAppendsTheTabsOfTabExpanderPluginsAfterTheBuiltInTabs(): void
    {
        // Arrange
        $customerGroupFormTabExpanderPluginMock = $this->createMock(CustomerGroupFormTabExpanderPluginInterface::class);
        $customerGroupFormTabExpanderPluginMock->expects($this->once())
            ->method('expand')
            ->willReturnCallback(function (TabsViewTransfer $tabsViewTransfer): TabsViewTransfer {
                return $tabsViewTransfer->addTab((new TabItemTransfer())->setName(static::TAB_NAME_EXPANDED));
            });

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER,
            [$customerGroupFormTabExpanderPluginMock],
        );

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_NAME_GENERAL, static::TAB_NAME_CUSTOMER_ASSIGNMENT, static::TAB_NAME_EXPANDED],
            $this->getTabNames($tabsViewTransfer),
        );
    }

    public function testCreateViewBuildsOnlyTheBuiltInTabsWithoutTabExpanderPlugins(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER, []);

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_NAME_GENERAL, static::TAB_NAME_CUSTOMER_ASSIGNMENT],
            $this->getTabNames($tabsViewTransfer),
        );
    }

    public function testCreateViewRendersTheTabsInTheConfiguredOrder(): void
    {
        // Arrange
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER,
            [$this->createTabExpanderPluginStub()],
        );
        $this->tester->mockConfigMethod(
            'getFormTabOrder',
            [static::TAB_NAME_EXPANDED, static::TAB_NAME_CUSTOMER_ASSIGNMENT, static::TAB_NAME_GENERAL],
        );

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_NAME_EXPANDED, static::TAB_NAME_CUSTOMER_ASSIGNMENT, static::TAB_NAME_GENERAL],
            $this->getTabNames($tabsViewTransfer),
        );
    }

    public function testCreateViewRendersTheTabsMissingFromTheConfiguredOrderAfterTheListedTabsInTheOrderTheyAreAdded(): void
    {
        // Arrange
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER,
            [$this->createTabExpanderPluginStub()],
        );
        $this->tester->mockConfigMethod('getFormTabOrder', [static::TAB_NAME_EXPANDED]);

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_NAME_EXPANDED, static::TAB_NAME_GENERAL, static::TAB_NAME_CUSTOMER_ASSIGNMENT],
            $this->getTabNames($tabsViewTransfer),
        );
    }

    public function testCreateViewKeepsATabWhoseNameAnotherTabAlreadyUses(): void
    {
        // Arrange
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER,
            [$this->createTabExpanderPluginStub((new TabItemTransfer())->setName(static::TAB_NAME_GENERAL)->setTitle(static::TAB_TITLE_EXPANDED))],
        );

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_TITLE_GENERAL, static::TAB_TITLE_EXPANDED, static::TAB_TITLE_CUSTOMER_ASSIGNMENT],
            $this->getTabTitles($tabsViewTransfer),
        );
    }

    public function testCreateViewRendersATabWithoutANameAfterTheConfiguredTabs(): void
    {
        // Arrange
        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER,
            [$this->createTabExpanderPluginStub((new TabItemTransfer())->setTitle(static::TAB_TITLE_EXPANDED))],
        );
        $this->tester->mockConfigMethod('getFormTabOrder', [static::TAB_NAME_CUSTOMER_ASSIGNMENT]);

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(
            [static::TAB_TITLE_CUSTOMER_ASSIGNMENT, static::TAB_TITLE_GENERAL, static::TAB_TITLE_EXPANDED],
            $this->getTabTitles($tabsViewTransfer),
        );
    }

    public function testCreateViewOpensTheFirstTabOfTheConfiguredOrder(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER, []);
        $this->tester->mockConfigMethod('getFormTabOrder', [static::TAB_NAME_CUSTOMER_ASSIGNMENT]);

        // Act
        $tabsViewTransfer = $this->getFactory()->createCustomerGroupFormTabs()->createView();

        // Assert
        $this->assertSame(static::TAB_NAME_CUSTOMER_ASSIGNMENT, $tabsViewTransfer->getActiveTabName());
    }

    public function testFormTabOrderListsTheBuiltInTabsByDefault(): void
    {
        // Act
        $formTabOrder = (new CustomerGroupConfig())->getFormTabOrder();

        // Assert
        $this->assertSame([static::TAB_NAME_GENERAL, static::TAB_NAME_CUSTOMER_ASSIGNMENT], $formTabOrder);
    }

    protected function createTabExpanderPluginStub(?TabItemTransfer $tabItemTransfer = null): CustomerGroupFormTabExpanderPluginInterface
    {
        $tabItemTransfer ??= (new TabItemTransfer())->setName(static::TAB_NAME_EXPANDED);
        $customerGroupFormTabExpanderPluginStub = $this->createStub(CustomerGroupFormTabExpanderPluginInterface::class);
        $customerGroupFormTabExpanderPluginStub
            ->method('expand')
            ->willReturnCallback(function (TabsViewTransfer $tabsViewTransfer) use ($tabItemTransfer): TabsViewTransfer {
                return $tabsViewTransfer->addTab($tabItemTransfer);
            });

        return $customerGroupFormTabExpanderPluginStub;
    }

    /**
     * @return list<string>
     */
    protected function getTabTitles(TabsViewTransfer $tabsViewTransfer): array
    {
        $tabTitles = [];

        foreach ($tabsViewTransfer->getTabs() as $tabItemTransfer) {
            $tabTitles[] = $tabItemTransfer->getTitleOrFail();
        }

        return $tabTitles;
    }

    /**
     * @return list<string>
     */
    protected function getTabNames(TabsViewTransfer $tabsViewTransfer): array
    {
        $tabNames = [];

        foreach ($tabsViewTransfer->getTabs() as $tabItemTransfer) {
            $tabNames[] = $tabItemTransfer->getNameOrFail();
        }

        return $tabNames;
    }

    protected function getFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
