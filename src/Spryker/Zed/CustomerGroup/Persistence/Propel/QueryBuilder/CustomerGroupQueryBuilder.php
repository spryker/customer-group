<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence\Propel\QueryBuilder;

use ArrayObject;
use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class CustomerGroupQueryBuilder implements CustomerGroupQueryBuilderInterface
{
    protected const string LIKE_WILDCARD = '%%%s%%';

    protected const string ALIAS_CUSTOMER_GROUP_TO_CUSTOMER_BY_ID = 'customerGroupToCustomerById';

    protected const string ALIAS_CUSTOMER_GROUP_TO_CUSTOMER_BY_REFERENCE = 'customerGroupToCustomerByReference';

    /**
     * @param list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupQueryExpanderPluginInterface> $customerGroupQueryExpanderPlugins
     */
    public function __construct(protected array $customerGroupQueryExpanderPlugins = [])
    {
    }

    public function applyCustomerGroupConditions(
        SpyCustomerGroupQuery $customerGroupQuery,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): SpyCustomerGroupQuery {
        /** @phpstan-ignore function.alreadyNarrowedType */
        if ($customerGroupConditionsTransfer->getUuids() && method_exists($customerGroupQuery, 'filterByUuid_In')) {
            $customerGroupQuery->filterByUuid_In($customerGroupConditionsTransfer->getUuids());
        }

        if ($customerGroupConditionsTransfer->getNames()) {
            $this->applyCaseInsensitiveNameFilter($customerGroupQuery, $customerGroupConditionsTransfer->getNames());
        }

        $this->applySearchTerm($customerGroupQuery, $customerGroupConditionsTransfer->getSearchTerm());

        return $this->applyCustomerConditions($customerGroupQuery, $customerGroupConditionsTransfer);
    }

    public function addQueryExpanderJoins(
        SpyCustomerGroupQuery $customerGroupQuery,
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): SpyCustomerGroupQuery {
        $queryJoinCollectionTransfer = new QueryJoinCollectionTransfer();

        foreach ($this->customerGroupQueryExpanderPlugins as $customerGroupQueryExpanderPlugin) {
            $queryJoinCollectionTransfer = $customerGroupQueryExpanderPlugin->expand($customerGroupCriteriaTransfer, $queryJoinCollectionTransfer);
        }

        return $this->addQueryJoins($customerGroupQuery, $queryJoinCollectionTransfer);
    }

    protected function addQueryJoins(
        SpyCustomerGroupQuery $customerGroupQuery,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): SpyCustomerGroupQuery {
        if ($queryJoinCollectionTransfer->getQueryJoins()->count() === 0) {
            return $customerGroupQuery;
        }

        $customerGroupQuery->distinct();

        foreach ($queryJoinCollectionTransfer->getQueryJoins() as $queryJoinTransfer) {
            $customerGroupQuery->addJoin(
                $queryJoinTransfer->getLeft(),
                $queryJoinTransfer->getRight(),
                $queryJoinTransfer->getJoinType() ?? Criteria::INNER_JOIN,
            );

            $this->addWhereConditions($customerGroupQuery, $queryJoinTransfer->getWhereConditions());
        }

        return $customerGroupQuery;
    }

    /**
     * @param array<int, string> $names
     */
    public function applyCaseInsensitiveNameFilter(SpyCustomerGroupQuery $customerGroupQuery, array $names): SpyCustomerGroupQuery
    {
        $conditionNames = [];

        foreach (array_values($names) as $index => $name) {
            $conditionName = sprintf('customerGroupName%d', $index);
            $conditionNames[] = $conditionName;

            $customerGroupQuery->condition(
                $conditionName,
                sprintf('LOWER(%s) = ?', SpyCustomerGroupTableMap::COL_NAME),
                mb_strtolower($name),
            );
        }

        return $customerGroupQuery->where($conditionNames, Criteria::LOGICAL_OR);
    }

    protected function applySearchTerm(SpyCustomerGroupQuery $customerGroupQuery, ?string $searchTerm): SpyCustomerGroupQuery
    {
        if ($searchTerm === null || $searchTerm === '') {
            return $customerGroupQuery;
        }

        $pattern = sprintf(static::LIKE_WILDCARD, $searchTerm);

        return $customerGroupQuery
            ->condition('nameLike', sprintf('%s LIKE ?', SpyCustomerGroupTableMap::COL_NAME), $pattern)
            ->condition('descriptionLike', sprintf('%s LIKE ?', SpyCustomerGroupTableMap::COL_DESCRIPTION), $pattern)
            ->where(['nameLike', 'descriptionLike'], Criteria::LOGICAL_OR);
    }

    protected function applyCustomerConditions(
        SpyCustomerGroupQuery $customerGroupQuery,
        CustomerGroupConditionsTransfer $customerGroupConditionsTransfer
    ): SpyCustomerGroupQuery {
        $customerGroupQuery = $this->applyCustomerIdsCondition($customerGroupQuery, $customerGroupConditionsTransfer->getCustomerIds());

        return $this->applyCustomerReferencesCondition($customerGroupQuery, $customerGroupConditionsTransfer->getCustomerReferences());
    }

    /**
     * @param array<int> $customerIds
     */
    protected function applyCustomerIdsCondition(SpyCustomerGroupQuery $customerGroupQuery, array $customerIds): SpyCustomerGroupQuery
    {
        if ($customerIds === []) {
            return $customerGroupQuery;
        }

        /** @var \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $customerGroupQuery */
        $customerGroupQuery = $customerGroupQuery
            ->useSpyCustomerGroupToCustomerExistsQuery(static::ALIAS_CUSTOMER_GROUP_TO_CUSTOMER_BY_ID)
                ->filterByFkCustomer_In($customerIds)
            ->endUse();

        return $customerGroupQuery;
    }

    /**
     * @param array<string> $customerReferences
     */
    protected function applyCustomerReferencesCondition(
        SpyCustomerGroupQuery $customerGroupQuery,
        array $customerReferences
    ): SpyCustomerGroupQuery {
        if ($customerReferences === []) {
            return $customerGroupQuery;
        }

        /** @var \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery $customerGroupQuery */
        $customerGroupQuery = $customerGroupQuery
            ->useSpyCustomerGroupToCustomerExistsQuery(static::ALIAS_CUSTOMER_GROUP_TO_CUSTOMER_BY_REFERENCE)
                ->useCustomerQuery(null, Criteria::INNER_JOIN)
                    ->filterByCustomerReference_In($customerReferences)
                ->endUse()
            ->endUse();

        return $customerGroupQuery;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\QueryWhereConditionTransfer> $queryWhereConditionTransfers
     */
    protected function addWhereConditions(SpyCustomerGroupQuery $customerGroupQuery, ArrayObject $queryWhereConditionTransfers): void
    {
        foreach ($queryWhereConditionTransfers as $queryWhereConditionTransfer) {
            $customerGroupQuery->addAnd(
                $queryWhereConditionTransfer->getColumnOrFail(),
                $queryWhereConditionTransfer->getValues() ?: $queryWhereConditionTransfer->getValue(),
                $queryWhereConditionTransfer->getComparison() ?? Criteria::EQUAL,
            );
        }
    }
}
