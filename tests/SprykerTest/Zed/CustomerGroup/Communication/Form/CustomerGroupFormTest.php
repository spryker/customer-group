<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Form;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormExpanderPluginInterface;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Form
 * @group CustomerGroupFormTest
 * Add your own group annotations below this line
 */
class CustomerGroupFormTest extends Unit
{
    protected const string FIELD_EXPANDED = 'expandedField';

    protected const string FIELD_NAME = 'name';

    protected const int ID_CUSTOMER_GROUP = 1;

    protected CustomerGroupCommunicationTester $tester;

    public function testCreateCustomerGroupFormAddsTheFieldsOfFormExpanderPluginsForTheGivenCustomerGroup(): void
    {
        // Arrange
        $customerGroupTransfer = $this->createCustomerGroupTransfer()->setIdCustomerGroup(static::ID_CUSTOMER_GROUP);

        $customerGroupFormExpanderPluginMock = $this->createMock(CustomerGroupFormExpanderPluginInterface::class);
        $customerGroupFormExpanderPluginMock->expects($this->once())
            ->method('expand')
            ->with($this->anything(), $this->identicalTo($customerGroupTransfer))
            ->willReturnCallback(function (FormBuilderInterface $builder): FormBuilderInterface {
                return $builder->add(static::FIELD_EXPANDED, HiddenType::class, ['mapped' => false]);
            });

        $this->tester->setDependency(
            CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER,
            [$customerGroupFormExpanderPluginMock],
        );

        // Act
        $form = $this->getFactory()->createCustomerGroupForm($customerGroupTransfer);

        // Assert
        $this->assertTrue($form->has(static::FIELD_EXPANDED));
        $this->assertTrue($form->has(static::FIELD_NAME));
    }

    public function testCreateCustomerGroupFormKeepsTheBuiltInFieldsWithoutFormExpanderPlugins(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER, []);

        // Act
        $form = $this->getFactory()->createCustomerGroupForm($this->createCustomerGroupTransfer());

        // Assert
        $this->assertTrue($form->has(static::FIELD_NAME));
        $this->assertFalse($form->has(static::FIELD_EXPANDED));
    }

    protected function createCustomerGroupTransfer(): CustomerGroupTransfer
    {
        return (new CustomerGroupTransfer())->setCustomerAssignment(new CustomerGroupToCustomerAssignmentTransfer());
    }

    protected function getFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
