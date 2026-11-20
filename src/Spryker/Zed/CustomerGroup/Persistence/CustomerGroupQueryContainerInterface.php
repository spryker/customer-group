<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery;
use Spryker\Zed\Kernel\Persistence\QueryContainer\QueryContainerInterface;

interface CustomerGroupQueryContainerInterface extends QueryContainerInterface
{
    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery
     */
    public function queryCustomerGroup();

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param int $idCustomerGroup
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery
     */
    public function queryCustomerGroupById($idCustomerGroup);

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param int $idCustomerGroup
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery
     */
    public function queryCustomerGroupToCustomerByFkCustomerGroup($idCustomerGroup);

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @return \Orm\Zed\Customer\Persistence\SpyCustomerQuery
     */
    public function queryCustomer();

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param int $idCustomer
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupQuery
     */
    public function queryCustomerGroupByFkCustomer($idCustomer);

    /**
     * Specification:
     * - Returns a query on the customer groups that match `CustomerGroupCriteria.customerGroupConditions`, with the
     *   same conditions as `CustomerGroupFacadeInterface::getCustomerGroupCollection()`.
     * - Executes the `CustomerGroupQueryExpanderPluginInterface` stack with the criteria and adds each `QueryJoin` the
     *   plugins return as a join from its `left` to its `right` columns, an inner join unless `joinType` is set, and
     *   each of its `whereConditions` as an AND condition on `column` with `values` (or `value`), an equality unless
     *   `comparison` is set.
     * - Selects each customer group once, also when a join or a condition matches it several times.
     * - Ignores the sorting and the pagination of the criteria.
     *
     * @api
     */
    public function queryCustomerGroupByCriteria(CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer): SpyCustomerGroupQuery;
}
