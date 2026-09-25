<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerGroup\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
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
 * @group GetCustomerGroupCollectionTest
 * Add your own group annotations below this line
 */
class GetCustomerGroupCollectionTest extends Unit
{
    use LocatorHelperTrait;

    protected string $namePrefix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->namePrefix = uniqid('cg-', false);
    }

    public function testReturnsTheGroupAddressedByUuid(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');
        $this->haveCustomerGroup('beta');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->addUuid($customerGroupEntity->getUuid())),
        );

        // Assert
        $this->assertCount(1, $customerGroupCollectionTransfer->getGroups());
        $this->assertSame(
            $customerGroupEntity->getIdCustomerGroup(),
            $customerGroupCollectionTransfer->getGroups()->offsetGet(0)->getIdCustomerGroup(),
        );
    }

    public function testReturnsAnEmptyCollectionForAnUnknownUuid(): void
    {
        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria(
                (new CustomerGroupConditionsTransfer())->addUuid('00000000-0000-5000-8000-000000000000'),
            ),
        );

        // Assert
        $this->assertCount(0, $customerGroupCollectionTransfer->getGroups());
    }

    public function testMatchesTheNameConditionCaseInsensitively(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria(
                (new CustomerGroupConditionsTransfer())->addName(mb_strtoupper($customerGroupEntity->getName())),
            ),
        );

        // Assert
        $this->assertSame(
            [$customerGroupEntity->getIdCustomerGroup()],
            $this->extractIds($customerGroupCollectionTransfer->getGroups()),
            'Group names are compared case-insensitively on every supported database engine.',
        );
    }

    public function testMatchesTheSearchTermAgainstNameAndDescription(): void
    {
        // Arrange
        $byNameEntity = $this->haveCustomerGroup('alpha');
        $byDescriptionEntity = $this->haveCustomerGroup('beta', $this->namePrefix . '-in-description');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->setSearchTerm($this->namePrefix)),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$byNameEntity->getIdCustomerGroup(), $byDescriptionEntity->getIdCustomerGroup()],
            $this->extractIds($customerGroupCollectionTransfer->getGroups()),
        );
    }

    public function testSortsByNameInBothDirections(): void
    {
        // Arrange
        $firstEntity = $this->haveCustomerGroup('alpha');
        $secondEntity = $this->haveCustomerGroup('beta');

        // Act
        $ascendingCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->setSearchTerm($this->namePrefix))
                ->addSort((new SortTransfer())->setField('name')->setIsAscending(true)),
        );
        $descendingCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->setSearchTerm($this->namePrefix))
                ->addSort((new SortTransfer())->setField('name')->setIsAscending(false)),
        );

        // Assert
        $this->assertSame(
            [$firstEntity->getIdCustomerGroup(), $secondEntity->getIdCustomerGroup()],
            $this->extractIds($ascendingCollectionTransfer->getGroups()),
        );
        $this->assertSame(
            [$secondEntity->getIdCustomerGroup(), $firstEntity->getIdCustomerGroup()],
            $this->extractIds($descendingCollectionTransfer->getGroups()),
        );
    }

    public function testIgnoresASortFieldOutsideTheConfiguredMap(): void
    {
        // Arrange
        $this->haveCustomerGroup('alpha');
        $this->haveCustomerGroup('beta');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->setSearchTerm($this->namePrefix))
                ->addSort((new SortTransfer())->setField('description')->setIsAscending(true)),
        );

        // Assert
        $this->assertCount(
            2,
            $customerGroupCollectionTransfer->getGroups(),
            'An unsupported sort field is dropped rather than reaching the ORDER BY clause.',
        );
    }

    public function testReportsTheTotalCountAlongsideAPageOfResults(): void
    {
        // Arrange
        $this->haveCustomerGroup('alpha');
        $this->haveCustomerGroup('beta');
        $this->haveCustomerGroup('gamma');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria(
                (new CustomerGroupConditionsTransfer())->setSearchTerm($this->namePrefix),
                (new PaginationTransfer())->setPage(1)->setMaxPerPage(2),
            ),
        );

        // Assert
        $this->assertCount(2, $customerGroupCollectionTransfer->getGroups());
        $this->assertSame(
            3,
            $customerGroupCollectionTransfer->getPaginationOrFail()->getNbResults(),
            'The total must describe the whole result set, not the returned page.',
        );
    }

    public function testExposesUuidAndCreatedAtOnEveryGroup(): void
    {
        // Arrange
        $customerGroupEntity = $this->haveCustomerGroup('alpha');

        // Act
        $customerGroupCollectionTransfer = $this->getFacade()->getCustomerGroupCollection(
            $this->createCriteria((new CustomerGroupConditionsTransfer())->addUuid($customerGroupEntity->getUuid())),
        );

        // Assert
        $customerGroupTransfer = $customerGroupCollectionTransfer->getGroups()->offsetGet(0);
        $this->assertSame($customerGroupEntity->getUuid(), $customerGroupTransfer->getUuid());
        $this->assertNotNull($customerGroupTransfer->getCreatedAt());
    }

    protected function createCriteria(
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer,
        ?PaginationTransfer $paginationTransfer = null
    ): CustomerGroupCriteriaTransfer {
        return (new CustomerGroupCriteriaTransfer())
            ->setCustomerGroupConditions($customerGroupConditionsTransfer)
            ->setPagination($paginationTransfer);
    }

    protected function haveCustomerGroup(string $nameSuffix, ?string $description = null): SpyCustomerGroup
    {
        $customerGroupEntity = (new SpyCustomerGroup())
            ->setName(sprintf('%s-%s', $this->namePrefix, $nameSuffix))
            ->setDescription($description);

        $customerGroupEntity->save();

        return $customerGroupEntity;
    }

    /**
     * @param iterable<\Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, int>
     */
    protected function extractIds(iterable $customerGroupTransfers): array
    {
        $customerGroupIds = [];

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $customerGroupIds[] = $customerGroupTransfer->getIdCustomerGroupOrFail();
        }

        return $customerGroupIds;
    }

    protected function getFacade(): CustomerGroupFacadeInterface
    {
        return $this->getLocator()->customerGroup()->facade();
    }
}
