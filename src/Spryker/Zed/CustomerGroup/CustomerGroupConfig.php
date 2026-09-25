<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup;

use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class CustomerGroupConfig extends AbstractBundleConfig
{
    public const string SORT_FIELD_NAME = 'name';

    public const string SORT_FIELD_CREATED_AT = 'createdAt';

    public const string SORT_FIELD_EMAIL = 'email';

    public const string SORT_FIELD_FIRST_NAME = 'firstName';

    public const string SORT_FIELD_LAST_NAME = 'lastName';

    /**
     * Specification:
     * - Returns the sortable fields of the customer group collection, mapping each publicly accepted
     *   field name to the database column that `CustomerGroupCriteriaTransfer.sortCollection` orders by.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getCustomerGroupCollectionSortableFieldMap(): array
    {
        return [
            static::SORT_FIELD_NAME => SpyCustomerGroupTableMap::COL_NAME,
            static::SORT_FIELD_CREATED_AT => SpyCustomerGroupTableMap::COL_CREATED_AT,
        ];
    }

    /**
     * Specification:
     * - Returns the sortable fields of a customer group's member collection, mapping each publicly
     *   accepted field name to the database column that `CustomerGroupCustomerCriteriaTransfer.sortCollection` orders by.
     *
     * @api
     *
     * @module Customer
     *
     * @return array<string, string>
     */
    public function getCustomerGroupCustomerCollectionSortableFieldMap(): array
    {
        return [
            static::SORT_FIELD_EMAIL => SpyCustomerTableMap::COL_EMAIL,
            static::SORT_FIELD_FIRST_NAME => SpyCustomerTableMap::COL_FIRST_NAME,
            static::SORT_FIELD_LAST_NAME => SpyCustomerTableMap::COL_LAST_NAME,
        ];
    }
}
