<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Model;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Spryker\Zed\CustomerGroup\Business\Exception\CustomerGroupNotFoundException;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface;

class CustomerGroup
{

    /**
     * @var \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @param \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface $queryContainer
     */
    public function __construct(CustomerGroupQueryContainerInterface $queryContainer)
    {
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupTransfer
     */
    public function get(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerGroupEntity = $this->getCustomerGroup($customerGroupTransfer);
        $customerGroupTransfer->fromArray($customerGroupEntity->toArray(), true);

        $customers = $customerGroupEntity->getSpyCustomerGroupToCustomers();
        if ($customers) {
            //$customerGroupTransfer->setAddresses($this->entityCollectionToTransferCollection($customers, $customerGroupEntity));
        }

        return $customerGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupTransfer
     */
    public function add(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerGroupEntity = new SpyCustomerGroup();
        $customerGroupEntity->fromArray($customerGroupTransfer->toArray());

        $this->queryContainer->getConnection()->beginTransaction();

        $customerGroupEntity->save();

        $this->saveCustomers($customerGroupTransfer, $customerGroupEntity);

        $this->queryContainer->getConnection()->commit();

        $customerGroupTransfer->setIdCustomerGroup($customerGroupEntity->getIdCustomerGroup());

        return $customerGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return void
     */
    public function update(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerGroupEntity = $this->getCustomerGroup($customerGroupTransfer);
        $customerGroupEntity->fromArray($customerGroupTransfer->toArray());

        $this->queryContainer->getConnection()->beginTransaction();
        $customerGroupEntity->save();

        $this->queryContainer
            ->queryCustomerGroupToCustomerByFkCustomerGroup($customerGroupEntity->getIdCustomerGroup())
            ->deleteAll();

        $this->saveCustomers($customerGroupTransfer, $customerGroupEntity);

        $this->queryContainer->getConnection()->commit();
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     * @param \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup $customerGroupEntity
     *
     * @return void
     */
    protected function saveCustomers(CustomerGroupTransfer $customerGroupTransfer, SpyCustomerGroup $customerGroupEntity)
    {
        foreach ($customerGroupTransfer->getCustomers() as $customerTransfer) {
            $customerGroupToCustomerEntity = new SpyCustomerGroupToCustomer();
            $customerGroupToCustomerEntity->setFkCustomerGroup($customerGroupEntity->getIdCustomerGroup());
            $customerGroupToCustomerEntity->setFkCustomer($customerTransfer->getFkCustomer());

            $customerGroupToCustomerEntity->save();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return bool
     */
    public function delete(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerEntity = $this->getCustomerGroup($customerGroupTransfer);
        $customerEntity->delete();

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @throws \Spryker\Zed\CustomerGroup\Business\Exception\CustomerGroupNotFoundException
     *
     * @return \Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup
     */
    protected function getCustomerGroup(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerGroupTransfer->requireIdCustomerGroup();

        $customerEntity = $this->queryContainer->queryCustomerGroupById($customerGroupTransfer->getIdCustomerGroup())
                ->findOne();

        if (!$customerEntity) {
            throw new CustomerGroupNotFoundException(sprintf(
                'Customer group not found by ID `%s`',
                $customerGroupTransfer->getIdCustomerGroup()
            ));
        }

        return $customerEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return void
     */
    public function removeCustomersFromGroup(CustomerGroupTransfer $customerGroupTransfer)
    {
        $customerGroupTransfer->requireIdCustomerGroup();
        $customerGroupTransfer->requireCustomers();

        foreach ($customerGroupTransfer->getCustomers() as $customer) {
            $customerEntity = $this->queryContainer
                ->queryCustomerGroupToCustomerByFkCustomerGroup($customerGroupTransfer->getIdCustomerGroup())
                ->filterByFkCustomer($customer->getFkCustomer())->findOne();
            if (!$customerEntity) {
                continue;
            }

            $customerEntity->delete();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return bool
     */
    protected function hasCustomer(CustomerGroupTransfer $customerGroupTransfer)
    {
        $result = false;
        $customerEntity = null;

        if ($customerGroupTransfer->getIdCustomer()) {
            $customerEntity = $this->queryContainer
                ->queryCustomerById($customerGroupTransfer->getIdCustomer())
                ->findOne();
        } elseif ($customerGroupTransfer->getEmail()) {
            $customerEntity = $this->queryContainer
                ->queryCustomerByEmail($customerGroupTransfer->getEmail())
                ->findOne();
        }

        if ($customerEntity !== null) {
            $result = true;
        }

        return $result;
    }

}
