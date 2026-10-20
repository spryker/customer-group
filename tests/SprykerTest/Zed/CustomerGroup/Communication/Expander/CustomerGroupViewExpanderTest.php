<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\CustomerGroup\Communication\Expander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupViewExpanderPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Expander
 * @group CustomerGroupViewExpanderTest
 * Add your own group annotations below this line
 */
class CustomerGroupViewExpanderTest extends Unit
{
    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander::VIEW_DATA_KEY_BLOCKS
     */
    protected const string VIEW_DATA_KEY_BLOCKS = 'blocks';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander::BLOCK_KEY_TEMPLATE_PATH
     */
    protected const string BLOCK_KEY_TEMPLATE_PATH = 'templatePath';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander::BLOCK_KEY_TEMPLATE_DATA
     */
    protected const string BLOCK_KEY_TEMPLATE_DATA = 'templateData';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander::TEMPLATE_PATH_GENERAL_INFORMATION
     */
    protected const string TEMPLATE_PATH_GENERAL_INFORMATION = '@CustomerGroup/View/_partials/general-information.twig';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander::TEMPLATE_PATH_CUSTOMERS
     */
    protected const string TEMPLATE_PATH_CUSTOMERS = '@CustomerGroup/View/_partials/customers.twig';

    /**
     * @uses \Spryker\Zed\CustomerGroup\CustomerGroupConfig::FORM_TAB_NAME_GENERAL
     */
    protected const string BLOCK_NAME_GENERAL = 'general';

    /**
     * @uses \Spryker\Zed\CustomerGroup\CustomerGroupConfig::FORM_TAB_NAME_CUSTOMER_ASSIGNMENT
     */
    protected const string BLOCK_NAME_CUSTOMER_ASSIGNMENT = 'customer-assignment';

    protected const string VIEW_DATA_KEY_CUSTOMER_TABLE = 'customerTable';

    protected const string CUSTOMER_TABLE = '<table id="customer-table"></table>';

    protected const string FIRST_BLOCK_NAME = 'first-block';

    protected const string SECOND_BLOCK_NAME = 'second-block';

    protected const string FIRST_TEMPLATE_PATH = '@FirstModule/_partials/first-block.twig';

    protected const string SECOND_TEMPLATE_PATH = '@SecondModule/_partials/second-block.twig';

    protected CustomerGroupCommunicationTester $tester;

    public function testExpandViewDataAddsTheBuiltInBlocksThenTheBlockOfEveryViewExpanderPluginKeyedByItsName(): void
    {
        // Arrange
        $customerGroupTransfer = (new CustomerGroupTransfer())->setName('customer-group');
        $firstTemplateData = ['firstVariable' => 'first'];
        $secondTemplateData = ['secondVariable' => 'second'];

        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER, [
            $this->createCustomerGroupViewExpanderPluginMock(static::FIRST_BLOCK_NAME, static::FIRST_TEMPLATE_PATH, $firstTemplateData, $customerGroupTransfer),
            $this->createCustomerGroupViewExpanderPluginMock(static::SECOND_BLOCK_NAME, static::SECOND_TEMPLATE_PATH, $secondTemplateData, $customerGroupTransfer),
        ]);

        // Act
        $viewData = $this->getFactory()
            ->createCustomerGroupViewExpander()
            ->expandViewData([static::VIEW_DATA_KEY_CUSTOMER_TABLE => static::CUSTOMER_TABLE], $customerGroupTransfer);

        // Assert
        $this->assertSame(
            [
                static::VIEW_DATA_KEY_CUSTOMER_TABLE => static::CUSTOMER_TABLE,
                static::VIEW_DATA_KEY_BLOCKS => [
                    static::BLOCK_NAME_GENERAL => $this->createBlock(static::TEMPLATE_PATH_GENERAL_INFORMATION, []),
                    static::BLOCK_NAME_CUSTOMER_ASSIGNMENT => $this->createBlock(static::TEMPLATE_PATH_CUSTOMERS, []),
                    static::FIRST_BLOCK_NAME => $this->createBlock(static::FIRST_TEMPLATE_PATH, $firstTemplateData),
                    static::SECOND_BLOCK_NAME => $this->createBlock(static::SECOND_TEMPLATE_PATH, $secondTemplateData),
                ],
            ],
            $viewData,
        );
    }

    public function testExpandViewDataOrdersTheBlocksByTheConfiguredFormTabOrder(): void
    {
        // Arrange
        $customerGroupTransfer = new CustomerGroupTransfer();

        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER, [
            $this->createCustomerGroupViewExpanderPluginMock(static::FIRST_BLOCK_NAME, static::FIRST_TEMPLATE_PATH, [], $customerGroupTransfer),
            $this->createCustomerGroupViewExpanderPluginMock(static::SECOND_BLOCK_NAME, static::SECOND_TEMPLATE_PATH, [], $customerGroupTransfer),
        ]);
        $this->tester->mockConfigMethod('getFormTabOrder', [static::SECOND_BLOCK_NAME, static::BLOCK_NAME_CUSTOMER_ASSIGNMENT]);

        // Act
        $viewData = $this->getFactory()
            ->createCustomerGroupViewExpander()
            ->expandViewData([], $customerGroupTransfer);

        // Assert
        $this->assertSame(
            [static::SECOND_BLOCK_NAME, static::BLOCK_NAME_CUSTOMER_ASSIGNMENT, static::BLOCK_NAME_GENERAL, static::FIRST_BLOCK_NAME],
            array_keys($viewData[static::VIEW_DATA_KEY_BLOCKS]),
        );
    }

    public function testExpandViewDataLetsAViewExpanderPluginReplaceTheBlockWithTheSameName(): void
    {
        // Arrange
        $customerGroupTransfer = new CustomerGroupTransfer();

        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER, [
            $this->createCustomerGroupViewExpanderPluginMock(static::BLOCK_NAME_CUSTOMER_ASSIGNMENT, static::FIRST_TEMPLATE_PATH, [], $customerGroupTransfer),
        ]);

        // Act
        $viewData = $this->getFactory()
            ->createCustomerGroupViewExpander()
            ->expandViewData([], $customerGroupTransfer);

        // Assert
        $this->assertSame(
            [
                static::BLOCK_NAME_GENERAL => $this->createBlock(static::TEMPLATE_PATH_GENERAL_INFORMATION, []),
                static::BLOCK_NAME_CUSTOMER_ASSIGNMENT => $this->createBlock(static::FIRST_TEMPLATE_PATH, []),
            ],
            $viewData[static::VIEW_DATA_KEY_BLOCKS],
        );
    }

    public function testExpandViewDataAddsOnlyTheBuiltInBlocksWithoutViewExpanderPlugins(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER, []);

        // Act
        $viewData = $this->getFactory()
            ->createCustomerGroupViewExpander()
            ->expandViewData([], new CustomerGroupTransfer());

        // Assert
        $this->assertSame(
            [static::BLOCK_NAME_GENERAL, static::BLOCK_NAME_CUSTOMER_ASSIGNMENT],
            array_keys($viewData[static::VIEW_DATA_KEY_BLOCKS]),
        );
    }

    /**
     * @param array<string, mixed> $templateData
     */
    protected function createCustomerGroupViewExpanderPluginMock(
        string $blockName,
        string $templatePath,
        array $templateData,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupViewExpanderPluginInterface {
        $customerGroupViewExpanderPluginMock = $this->createMock(CustomerGroupViewExpanderPluginInterface::class);
        $customerGroupViewExpanderPluginMock->method('getBlockName')->willReturn($blockName);
        $customerGroupViewExpanderPluginMock->method('getTemplatePath')->willReturn($templatePath);
        $customerGroupViewExpanderPluginMock->expects($this->once())
            ->method('getTemplateData')
            ->with($this->identicalTo($customerGroupTransfer))
            ->willReturn($templateData);

        return $customerGroupViewExpanderPluginMock;
    }

    /**
     * @param array<string, mixed> $templateData
     *
     * @return array<string, mixed>
     */
    protected function createBlock(string $templatePath, array $templateData): array
    {
        return [
            static::BLOCK_KEY_TEMPLATE_PATH => $templatePath,
            static::BLOCK_KEY_TEMPLATE_DATA => $templateData,
        ];
    }

    protected function getFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
