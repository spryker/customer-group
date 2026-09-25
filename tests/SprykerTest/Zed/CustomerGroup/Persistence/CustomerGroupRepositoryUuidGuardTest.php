<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Zed\CustomerGroup\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupPersistenceFactory;
use SprykerTest\Zed\CustomerGroup\Persistence\Fixtures\CustomerGroupUuidGuardProbe;
use SprykerTest\Zed\CustomerGroup\Persistence\Fixtures\CustomerGroupUuidGuardProbeWithoutUuidColumn;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerGroup
 * @group Persistence
 * @group CustomerGroupRepositoryUuidGuardTest
 * Add your own group annotations below this line
 */
class CustomerGroupRepositoryUuidGuardTest extends Unit
{
    protected const string UUID = '4b1e6a02-9f37-5c48-b6d1-2e8a70c9f4a5';

    protected const string NAME = 'Wholesale';

    public function testAppliesTheUuidFilterWhenTheColumnIsInstalled(): void
    {
        // Arrange
        $customerGroupUuidGuardProbe = $this->createProbe();

        // Act
        $customerGroupQuery = $customerGroupUuidGuardProbe->exposeApplyCustomerGroupConditions(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->addUuid(static::UUID)),
        );

        // Assert
        $params = [];

        $this->assertStringContainsString(
            SpyCustomerGroupTableMap::COL_UUID,
            $customerGroupQuery->createSelectSql($params),
            'This suite runs with the uuid schema extension installed, so the filter must reach the SQL.',
        );
    }

    public function testKeepsTheOtherConditionsWhenTheUuidConditionCannotBeApplied(): void
    {
        // Arrange
        $customerGroupUuidGuardProbe = $this->createProbeWithoutUuidColumn();

        // Act
        $customerGroupQuery = $customerGroupUuidGuardProbe->exposeApplyCustomerGroupConditions(
            $this->createCriteria(
                (new CustomerGroupConditionsTransfer())
                    ->addUuid(static::UUID)
                    ->addName(static::NAME),
            ),
        );

        // Assert
        $params = [];

        $this->assertStringContainsString(
            SpyCustomerGroupTableMap::COL_NAME,
            $customerGroupQuery->createSelectSql($params),
            'The name condition must survive a uuid condition that cannot be applied.',
        );
    }

    public function testBuildsTheQueryForNonUuidConditionsWithoutTheUuidColumn(): void
    {
        // Arrange
        $customerGroupUuidGuardProbe = $this->createProbeWithoutUuidColumn();

        // Act
        $customerGroupQuery = $customerGroupUuidGuardProbe->exposeApplyCustomerGroupConditions(
            $this->createCriteria(
                (new CustomerGroupConditionsTransfer())->addName(static::NAME),
            ),
        );

        // Assert
        $this->assertInstanceOf(SpyCustomerGroupQuery::class, $customerGroupQuery);
    }

    public function testBuildsTheQueryWhenNoConditionsAreSet(): void
    {
        // Arrange
        $customerGroupUuidGuardProbe = $this->createProbeWithoutUuidColumn();

        // Act
        $customerGroupQuery = $customerGroupUuidGuardProbe->exposeApplyCustomerGroupConditions(
            new CustomerGroupCriteriaTransfer(),
        );

        // Assert
        $this->assertInstanceOf(SpyCustomerGroupQuery::class, $customerGroupQuery);
    }

    protected function createCriteria(
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): CustomerGroupCriteriaTransfer {
        return (new CustomerGroupCriteriaTransfer())
            ->setCustomerGroupConditions($customerGroupConditionsTransfer);
    }

    protected function createProbe(): CustomerGroupUuidGuardProbe
    {
        /** @var \SprykerTest\Zed\CustomerGroup\Persistence\Fixtures\CustomerGroupUuidGuardProbe $customerGroupUuidGuardProbe */
        $customerGroupUuidGuardProbe = (new CustomerGroupUuidGuardProbe())
            ->setFactory(new CustomerGroupPersistenceFactory());

        return $customerGroupUuidGuardProbe;
    }

    protected function createProbeWithoutUuidColumn(): CustomerGroupUuidGuardProbeWithoutUuidColumn
    {
        /** @var \SprykerTest\Zed\CustomerGroup\Persistence\Fixtures\CustomerGroupUuidGuardProbeWithoutUuidColumn $customerGroupUuidGuardProbe */
        $customerGroupUuidGuardProbe = (new CustomerGroupUuidGuardProbeWithoutUuidColumn())
            ->setFactory(new CustomerGroupPersistenceFactory());

        return $customerGroupUuidGuardProbe;
    }
}
