<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\CustomerGroup\Persistence;

use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomer;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;

/**
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupPersistenceFactory getFactory()
 */
class CustomerGroupEntityManager extends AbstractEntityManager implements CustomerGroupEntityManagerInterface
{
    use ActiveRecordBatchProcessorTrait;

    /**
     * @var int<1, max>
     */
    protected const int BATCH_SIZE = 500;

    /**
     * @uses \Orm\Zed\CustomerGroup\Persistence\Map\SpyCustomerGroupToCustomerTableMap::COL_FK_CUSTOMER
     */
    protected const string COL_FK_CUSTOMER = 'FkCustomer';

    /**
     * {@inheritDoc}
     *
     * @param array<int, int> $customerIds
     */
    public function createCustomerGroupToCustomerRelations(int $idCustomerGroup, array $customerIds): void
    {
        foreach (array_chunk($customerIds, static::BATCH_SIZE) as $customerIdsChunk) {
            $customerIdsToAssign = array_diff(
                $customerIdsChunk,
                $this->getAssignedCustomerIds($idCustomerGroup, $customerIdsChunk),
            );

            if ($customerIdsToAssign === []) {
                continue;
            }

            foreach ($customerIdsToAssign as $idCustomer) {
                $this->persist(
                    (new SpyCustomerGroupToCustomer())
                        ->setFkCustomerGroup($idCustomerGroup)
                        ->setFkCustomer($idCustomer),
                );
            }

            $this->commitIdentical();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param array<int, int> $customerIds
     */
    public function deleteCustomerGroupToCustomerRelations(int $idCustomerGroup, array $customerIds): void
    {
        foreach (array_chunk($customerIds, static::BATCH_SIZE) as $customerIdsChunk) {
            $this->getFactory()
                ->createCustomerGroupToCustomerQuery()
                ->filterByFkCustomerGroup($idCustomerGroup)
                ->filterByFkCustomer_In($customerIdsChunk)
                ->delete();
        }
    }

    /**
     * @param array<int, int> $customerIds
     *
     * @return array<int, int>
     */
    protected function getAssignedCustomerIds(int $idCustomerGroup, array $customerIds): array
    {
        $assignedCustomerIds = $this->getFactory()
            ->createCustomerGroupToCustomerQuery()
            ->filterByFkCustomerGroup($idCustomerGroup)
            ->filterByFkCustomer_In($customerIds)
            ->select([static::COL_FK_CUSTOMER])
            ->find()
            ->getData();

        return array_map('intval', $assignedCustomerIds);
    }
}
