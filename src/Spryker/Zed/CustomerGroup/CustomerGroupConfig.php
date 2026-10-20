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

    public const string FORM_TAB_NAME_GENERAL = 'general';

    public const string FORM_TAB_NAME_CUSTOMER_ASSIGNMENT = 'customer-assignment';

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

    /**
     * Specification:
     * - Defines where a successful save on the Back Office customer group Edit page redirects.
     * - Returns `true` to redirect back to the Edit page, opening the tab that was open when the form was submitted.
     * - Returns `false` to redirect to the customer group View page.
     *
     * @api
     */
    public function isRedirectToEditPageAfterUpdateEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Returns the names of the tabs of the Back Office customer group Add and Edit pages, in the order they are rendered.
     * - The built-in tabs are named `FORM_TAB_NAME_GENERAL` and `FORM_TAB_NAME_CUSTOMER_ASSIGNMENT` a tab added by a `CustomerGroupFormTabExpanderPluginInterface` plugin has the name the plugin gives it.
     * - Tabs that are not listed are rendered after the listed ones, in the order they are added: the built-in tabs, then the tabs of the plugins.
     * - The first rendered tab opens when the page loads.
     * - Returns the built-in tabs by default, General Information then Customers, so the tabs of the plugins follow them.
     *
     * @api
     *
     * @return list<string>
     */
    public function getFormTabOrder(): array
    {
        return [
            static::FORM_TAB_NAME_GENERAL,
            static::FORM_TAB_NAME_CUSTOMER_ASSIGNMENT,
        ];
    }
}
