<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Communication\Resolver;

use Codeception\Test\Unit;
use Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory;
use Spryker\Zed\CustomerGroup\Communication\Resolver\UpdateRedirectUrlResolver;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use SprykerTest\Zed\CustomerGroup\CustomerGroupCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Communication
 * @group Resolver
 * @group UpdateRedirectUrlResolverTest
 * Add your own group annotations below this line
 */
class UpdateRedirectUrlResolverTest extends Unit
{
    protected const int ID_CUSTOMER_GROUP = 5;

    protected const string EDIT_PAGE_URL = '/customer-group/edit?id-customer-group=5';

    protected const string VIEW_PAGE_URL = '/customer-group/view?id-customer-group=5';

    /**
     * @uses \Spryker\Zed\CustomerGroup\CustomerGroupConfig::isRedirectToEditPageAfterUpdateEnabled()
     */
    protected const string CONFIG_METHOD_IS_REDIRECT_TO_EDIT_PAGE_AFTER_UPDATE_ENABLED = 'isRedirectToEditPageAfterUpdateEnabled';

    protected CustomerGroupCommunicationTester $tester;

    public function testResolveUpdateRedirectUrlReturnsTheEditPageWhenTheRedirectToTheEditPageIsEnabled(): void
    {
        // Arrange
        $this->tester->mockConfigMethod(static::CONFIG_METHOD_IS_REDIRECT_TO_EDIT_PAGE_AFTER_UPDATE_ENABLED, true);

        // Act
        $redirectUrl = $this->getFactory()->createUpdateRedirectUrlResolver()->resolveUpdateRedirectUrl(static::ID_CUSTOMER_GROUP);

        // Assert
        $this->assertSame(static::EDIT_PAGE_URL, $redirectUrl);
    }

    public function testResolveUpdateRedirectUrlReturnsTheViewPageWhenTheRedirectToTheEditPageIsDisabled(): void
    {
        // Arrange
        $this->tester->mockConfigMethod(static::CONFIG_METHOD_IS_REDIRECT_TO_EDIT_PAGE_AFTER_UPDATE_ENABLED, false);

        // Act
        $redirectUrl = $this->getFactory()->createUpdateRedirectUrlResolver()->resolveUpdateRedirectUrl(static::ID_CUSTOMER_GROUP);

        // Assert
        $this->assertSame(static::VIEW_PAGE_URL, $redirectUrl);
    }

    /**
     * Builds the resolver with the module's own configuration, so a project override of the setting cannot hide the default.
     */
    public function testResolveUpdateRedirectUrlReturnsTheViewPageWithTheDefaultModuleConfiguration(): void
    {
        // Arrange
        $updateRedirectUrlResolver = new UpdateRedirectUrlResolver(new CustomerGroupConfig());

        // Act
        $redirectUrl = $updateRedirectUrlResolver->resolveUpdateRedirectUrl(static::ID_CUSTOMER_GROUP);

        // Assert
        $this->assertSame(static::VIEW_PAGE_URL, $redirectUrl);
    }

    protected function getFactory(): CustomerGroupCommunicationFactory
    {
        /** @var \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory $customerGroupCommunicationFactory */
        $customerGroupCommunicationFactory = $this->tester->getFactory();

        return $customerGroupCommunicationFactory;
    }
}
