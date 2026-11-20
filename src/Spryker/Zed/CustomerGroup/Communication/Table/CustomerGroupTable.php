<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Table;

use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupTableMap;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\CustomerGroup\Communication\Form\CustomerGroupTableFilterForm;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class CustomerGroupTable extends AbstractTable
{
    /**
     * @var string
     */
    public const ACTIONS = 'Actions';

    /**
     * @var string
     */
    public const COL_ID_CUSTOMER_GROUP = 'id_customer_group';

    /**
     * @var string
     */
    public const COL_NAME = 'name';

    /**
     * @var string
     */
    public const COL_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const COL_CREATED_AT = 'created_at';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Controller\IndexController::tableAction()
     */
    protected const string URL_TABLE = 'table';

    /**
     * @var \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface
     */
    protected $customerGroupQueryContainer;

    /**
     * @var \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface
     */
    protected $utilDateTimeService;

    protected CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer;

    public function __construct(
        CustomerGroupQueryContainerInterface $customerQueryContainer,
        UtilDateTimeServiceInterface $utilDateTimeService
    ) {
        $this->customerGroupQueryContainer = $customerQueryContainer;
        $this->utilDateTimeService = $utilDateTimeService;
        $this->customerGroupCriteriaTransfer = new CustomerGroupCriteriaTransfer();
    }

    public function applyCriteria(CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer): void
    {
        $this->customerGroupCriteriaTransfer = $customerGroupCriteriaTransfer;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader([
            static::COL_ID_CUSTOMER_GROUP => '#',
            static::COL_NAME => 'Name',
            static::COL_DESCRIPTION => 'Description',
            static::COL_CREATED_AT => 'Created',
            static::ACTIONS => static::ACTIONS,
        ]);

        $config->addRawColumn(static::ACTIONS);

        $config->setSortable([
            static::COL_ID_CUSTOMER_GROUP,
            static::COL_NAME,
            static::COL_CREATED_AT,
        ]);

        $config->setUrl($this->buildTableUrl());

        $config->setSearchable([
            SpyCustomerGroupTableMap::COL_ID_CUSTOMER_GROUP,
            SpyCustomerGroupTableMap::COL_NAME,
            SpyCustomerGroupTableMap::COL_DESCRIPTION,
            SpyCustomerGroupTableMap::COL_CREATED_AT,
        ]);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this->customerGroupQueryContainer->queryCustomerGroupByCriteria($this->customerGroupCriteriaTransfer);

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup> $customerGroupCollection */
        $customerGroupCollection = $this->runQuery($query, $config, true);

        if ($customerGroupCollection->count() < 1) {
            return [];
        }

        return $this->mapCustomerGroupCollection($customerGroupCollection);
    }

    protected function buildTableUrl(): string
    {
        $filterData = $this->getRequest()->query->all(CustomerGroupTableFilterForm::BLOCK_PREFIX);

        if ($filterData === []) {
            return static::URL_TABLE;
        }

        return sprintf('%s?%s', static::URL_TABLE, http_build_query([CustomerGroupTableFilterForm::BLOCK_PREFIX => $filterData]));
    }

    /**
     * @param \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup|null $customerGroup
     *
     * @return string
     */
    protected function buildLinks(?SpyCustomerGroup $customerGroup = null)
    {
        if ($customerGroup === null) {
            return '';
        }

        $buttons = [];
        $buttons[] = $this->generateViewButton('/customer-group/view?id-customer-group=' . $customerGroup->getIdCustomerGroup(), 'View');
        $buttons[] = $this->generateEditButton('/customer-group/edit?id-customer-group=' . $customerGroup->getIdCustomerGroup(), 'Edit');

        return implode(' ', $buttons);
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup> $customersCollection
     *
     * @return array
     */
    protected function mapCustomerGroupCollection(ObjectCollection $customersCollection)
    {
        $customersList = [];
        foreach ($customersCollection as $customer) {
            $customersList[] = $this->mapCustomerListRow($customer);
        }

        return $customersList;
    }

    /**
     * @param \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup $customerGroup
     *
     * @return array
     */
    protected function mapCustomerListRow(SpyCustomerGroup $customerGroup)
    {
        $customerRow = $customerGroup->toArray();

        $customerRow[static::COL_CREATED_AT] = $this->utilDateTimeService->formatDateTime($customerGroup->getCreatedAt());
        $customerRow[static::ACTIONS] = $this->buildLinks($customerGroup);

        return $customerRow;
    }
}
