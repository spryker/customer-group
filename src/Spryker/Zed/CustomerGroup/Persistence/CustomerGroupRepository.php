<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Orm\Zed\Customer\Persistence\SpyCustomerQuery;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupToCustomerTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupPersistenceFactory getFactory()
 */
class CustomerGroupRepository extends AbstractRepository implements CustomerGroupRepositoryInterface
{
    protected const string LIKE_WILDCARD = '%%%s%%';

    /**
     * @module Customer
     *
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionTransfer
     */
    public function getCustomerGroupCollectionByIdCustomer(int $idCustomer): CustomerGroupCollectionTransfer
    {
        $customerGroupEntities = $this->getFactory()->createCustomerGroupQuery()
            ->innerJoinSpyCustomerGroupToCustomer()
            ->useSpyCustomerGroupToCustomerQuery()
            ->filterByFkCustomer($idCustomer)
            ->endUse()->find();

        return $this->getFactory()->createCustomerGroupMapper()
            ->mapCustomerGroupEntitiesToCustomerGroupCollectionTransfer($customerGroupEntities);
    }

    public function getCustomerGroupCollection(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): CustomerGroupCollectionTransfer {
        $paginationTransfer = $customerGroupCriteriaTransfer->getPagination();

        $query = $this->applyCustomerGroupConditions(
            $this->getFactory()->createCustomerGroupQuery(),
            $customerGroupCriteriaTransfer,
        );
        $query = $this->applyCustomerGroupSort($query, $customerGroupCriteriaTransfer->getSortCollection());
        $query = $this->applyCustomerGroupPagination($query, $paginationTransfer);

        $customerGroupCollectionTransfer = $this->getFactory()->createCustomerGroupMapper()
            ->mapCustomerGroupEntitiesToCustomerGroupCollectionTransfer($query->find());

        return $customerGroupCollectionTransfer->setPagination($paginationTransfer);
    }

    /**
     * @module Customer
     *
     * @param \Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerCollectionTransfer
     */
    public function getCustomerCollectionByCustomerGroupCriteria(
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): CustomerCollectionTransfer {
        $paginationTransfer = $customerGroupCustomerCriteriaTransfer->getPagination();

        $query = $this->applyCustomerGroupCustomerConditions(
            $this->getFactory()->createCustomerQuery(),
            $customerGroupCustomerCriteriaTransfer,
        );
        $query = $this->applyCustomerSort($query, $customerGroupCustomerCriteriaTransfer->getSortCollection());
        $query = $this->applyCustomerPagination($query, $paginationTransfer);

        $customerCollectionTransfer = new CustomerCollectionTransfer();

        /** @var \Orm\Zed\Customer\Persistence\SpyCustomer $customerEntity */
        foreach ($query->find() as $customerEntity) {
            $customerCollectionTransfer->addCustomer(
                (new CustomerTransfer())
                    ->setIdCustomer($customerEntity->getIdCustomer())
                    ->setCustomerReference($customerEntity->getCustomerReference())
                    ->setEmail($customerEntity->getEmail())
                    ->setFirstName($customerEntity->getFirstName())
                    ->setLastName($customerEntity->getLastName()),
            );
        }

        return $customerCollectionTransfer->setPagination($paginationTransfer);
    }

    /**
     * @module Customer
     *
     * @param array<int, int> $customerGroupIds
     *
     * @return array<int, array<int, int>>
     */
    public function getCustomerIdsGroupedByIdCustomerGroup(array $customerGroupIds): array
    {
        if ($customerGroupIds === []) {
            return [];
        }

        $assignmentRows = $this->getFactory()->createCustomerGroupToCustomerQuery()
            ->filterByFkCustomerGroup_In($customerGroupIds)
            ->orderByFkCustomer(Criteria::ASC)
            ->select([
                SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER_GROUP,
                SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER,
            ])
            ->find()
            ->getData();

        $customerIdsGroupedByIdCustomerGroup = array_fill_keys($customerGroupIds, []);

        foreach ($assignmentRows as $assignmentRow) {
            $customerIdsGroupedByIdCustomerGroup[(int)$assignmentRow[SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER_GROUP]][]
                = (int)$assignmentRow[SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER];
        }

        return $customerIdsGroupedByIdCustomerGroup;
    }

    /**
     * @module Customer
     *
     * @param array<int, string> $customerReferences
     *
     * @return array<string, int>
     */
    public function getCustomerIdsIndexedByCustomerReference(array $customerReferences): array
    {
        if ($customerReferences === []) {
            return [];
        }

        $customerRows = $this->getFactory()->createCustomerQuery()
            ->filterByCustomerReference_In($customerReferences)
            ->select([SpyCustomerTableMap::COL_CUSTOMER_REFERENCE, SpyCustomerTableMap::COL_ID_CUSTOMER])
            ->find()
            ->getData();

        $customerIdsIndexedByReference = [];

        foreach ($customerRows as $customerRow) {
            $customerIdsIndexedByReference[(string)$customerRow[SpyCustomerTableMap::COL_CUSTOMER_REFERENCE]]
                = (int)$customerRow[SpyCustomerTableMap::COL_ID_CUSTOMER];
        }

        return $customerIdsIndexedByReference;
    }

    /**
     * @param array<int, string> $names
     *
     * @return array<string, array<int, int>>
     */
    public function getCustomerGroupIdsGroupedByLowercasedName(array $names): array
    {
        if ($names === []) {
            return [];
        }

        $customerGroupRows = $this->applyCaseInsensitiveNameFilter(
            $this->getFactory()->createCustomerGroupQuery(),
            $names,
        )
            ->select([SpyCustomerGroupTableMap::COL_NAME, SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP])
            ->find()
            ->getData();

        $customerGroupIdsGroupedByLowercasedName = [];

        foreach ($customerGroupRows as $customerGroupRow) {
            $lowercasedName = mb_strtolower((string)$customerGroupRow[SpyCustomerGroupTableMap::COL_NAME]);
            $customerGroupIdsGroupedByLowercasedName[$lowercasedName][]
                = (int)$customerGroupRow[SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP];
        }

        return $customerGroupIdsGroupedByLowercasedName;
    }

    protected function applyCustomerGroupConditions(
        SpyCustomerGroupQuery $query,
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): SpyCustomerGroupQuery {
        $customerGroupConditionsTransfer = $customerGroupCriteriaTransfer->getCustomerGroupConditions();

        if ($customerGroupConditionsTransfer === null) {
            return $query;
        }

        /** @phpstan-ignore function.alreadyNarrowedType */
        if ($customerGroupConditionsTransfer->getUuids() && method_exists($query, 'filterByUuid_In')) {
            $query->filterByUuid_In($customerGroupConditionsTransfer->getUuids());
        }

        if ($customerGroupConditionsTransfer->getNames()) {
            $this->applyCaseInsensitiveNameFilter($query, $customerGroupConditionsTransfer->getNames());
        }

        $searchTerm = $customerGroupConditionsTransfer->getSearchTerm();

        if ($searchTerm !== null && $searchTerm !== '') {
            $pattern = sprintf(static::LIKE_WILDCARD, $searchTerm);

            $query
                ->condition('nameLike', sprintf('%s LIKE ?', SpyCustomerGroupTableMap::COL_NAME), $pattern)
                ->condition('descriptionLike', sprintf('%s LIKE ?', SpyCustomerGroupTableMap::COL_DESCRIPTION), $pattern)
                ->where(['nameLike', 'descriptionLike'], Criteria::LOGICAL_OR);
        }

        return $query;
    }

    /**
     * @param \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $query
     * @param array<int, string> $names
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery
     */
    protected function applyCaseInsensitiveNameFilter(SpyCustomerGroupQuery $query, array $names): SpyCustomerGroupQuery
    {
        $conditionNames = [];

        foreach (array_values($names) as $index => $name) {
            $conditionName = sprintf('customerGroupName%d', $index);
            $conditionNames[] = $conditionName;

            $query->condition(
                $conditionName,
                sprintf('LOWER(%s) = ?', SpyCustomerGroupTableMap::COL_NAME),
                mb_strtolower($name),
            );
        }

        return $query->where($conditionNames, Criteria::LOGICAL_OR);
    }

    protected function applyCustomerGroupCustomerConditions(
        SpyCustomerQuery $query,
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): SpyCustomerQuery {
        $conditionsTransfer = $customerGroupCustomerCriteriaTransfer->getCustomerGroupCustomerConditions();

        if ($conditionsTransfer === null) {
            return $query;
        }

        if ($conditionsTransfer->getCustomerGroupIds()) {
            $query
                ->useSpyCustomerGroupToCustomerQuery()
                    ->filterByFkCustomerGroup_In($conditionsTransfer->getCustomerGroupIds())
                ->endUse()
                ->distinct();
        }

        if ($conditionsTransfer->getCustomerReferences()) {
            $query->filterByCustomerReference_In($conditionsTransfer->getCustomerReferences());
        }

        $searchTerm = $conditionsTransfer->getSearchTerm();

        if ($searchTerm !== null && $searchTerm !== '') {
            $pattern = sprintf(static::LIKE_WILDCARD, $searchTerm);

            $query
                ->condition('emailLike', sprintf('%s LIKE ?', SpyCustomerTableMap::COL_EMAIL), $pattern)
                ->condition('firstNameLike', sprintf('%s LIKE ?', SpyCustomerTableMap::COL_FIRST_NAME), $pattern)
                ->condition('lastNameLike', sprintf('%s LIKE ?', SpyCustomerTableMap::COL_LAST_NAME), $pattern)
                ->where(['emailLike', 'firstNameLike', 'lastNameLike'], Criteria::LOGICAL_OR);
        }

        return $query;
    }

    /**
     * @param \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $query
     * @param iterable<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery
     */
    protected function applyCustomerGroupSort(SpyCustomerGroupQuery $query, iterable $sortTransfers): SpyCustomerGroupQuery
    {
        /** @var \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $sortedQuery */
        $sortedQuery = $this->applySort(
            $query,
            $sortTransfers,
            $this->getFactory()->getConfig()->getCustomerGroupCollectionSortableFieldMap(),
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
        );

        return $sortedQuery;
    }

    /**
     * @param \Orm\Zed\Customer\Persistence\SpyCustomerQuery $query
     * @param iterable<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     *
     * @return \Orm\Zed\Customer\Persistence\SpyCustomerQuery
     */
    protected function applyCustomerSort(SpyCustomerQuery $query, iterable $sortTransfers): SpyCustomerQuery
    {
        /** @var \Orm\Zed\Customer\Persistence\SpyCustomerQuery $sortedQuery */
        $sortedQuery = $this->applySort(
            $query,
            $sortTransfers,
            $this->getFactory()->getConfig()->getCustomerGroupCustomerCollectionSortableFieldMap(),
            SpyCustomerTableMap::COL_ID_CUSTOMER,
        );

        return $sortedQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $query
     * @param iterable<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     * @param array<string, string> $columnBySortField
     * @param string $tiebreakerColumn
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function applySort(
        ModelCriteria $query,
        iterable $sortTransfers,
        array $columnBySortField,
        string $tiebreakerColumn
    ): ModelCriteria {
        $tiebreakerDirection = Criteria::ASC;

        foreach ($sortTransfers as $sortTransfer) {
            $column = $columnBySortField[$sortTransfer->getFieldOrFail()] ?? null;

            if ($column === null) {
                continue;
            }

            $direction = $sortTransfer->getIsAscending() === false ? Criteria::DESC : Criteria::ASC;

            $query->orderBy($column, $direction);
            $tiebreakerDirection = $direction;
        }

        return $query->orderBy($tiebreakerColumn, $tiebreakerDirection);
    }

    protected function applyCustomerGroupPagination(
        SpyCustomerGroupQuery $query,
        ?PaginationTransfer $paginationTransfer = null
    ): SpyCustomerGroupQuery {
        /** @var \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $paginatedQuery */
        $paginatedQuery = $this->applyPagination($query, $paginationTransfer);

        return $paginatedQuery;
    }

    protected function applyCustomerPagination(
        SpyCustomerQuery $query,
        ?PaginationTransfer $paginationTransfer = null
    ): SpyCustomerQuery {
        /** @var \Orm\Zed\Customer\Persistence\SpyCustomerQuery $paginatedQuery */
        $paginatedQuery = $this->applyPagination($query, $paginationTransfer);

        return $paginatedQuery;
    }

    protected function applyPagination(
        ModelCriteria $query,
        ?PaginationTransfer $paginationTransfer = null
    ): ModelCriteria {
        if ($paginationTransfer === null) {
            return $query;
        }

        $paginationModel = $query->paginate(
            $paginationTransfer->requirePage()->getPageOrFail(),
            $paginationTransfer->requireMaxPerPage()->getMaxPerPageOrFail(),
        );

        $paginationTransfer->setNbResults($paginationModel->getNbResults());

        return $paginationModel->getQuery();
    }
}
