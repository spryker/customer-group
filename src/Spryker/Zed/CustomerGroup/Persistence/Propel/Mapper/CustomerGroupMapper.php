<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Persistence\Propel\Mapper;

use DateTimeInterface;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Propel\Runtime\Collection\Collection;

class CustomerGroupMapper implements CustomerGroupMapperInterface
{
    /**
     * @var string
     */
    protected const DATE_TIME_FORMAT = DateTimeInterface::ATOM;

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup> $customerGroupEntities
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionTransfer
     */
    public function mapCustomerGroupEntitiesToCustomerGroupCollectionTransfer(Collection $customerGroupEntities): CustomerGroupCollectionTransfer
    {
        $customerGroupCollectionTransfer = new CustomerGroupCollectionTransfer();

        foreach ($customerGroupEntities as $customerGroupEntity) {
            $customerGroupCollectionTransfer->addGroup(
                $this->mapCustomerGroupEntityToCustomerGroupTransfer($customerGroupEntity, new CustomerGroupTransfer()),
            );
        }

        return $customerGroupCollectionTransfer;
    }

    public function mapCustomerGroupEntityToCustomerGroupTransfer(
        SpyCustomerGroup $customerGroupEntity,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupTransfer {
        $customerGroupTransfer->fromArray($customerGroupEntity->toArray(), true);

        $createdAt = $customerGroupEntity->getCreatedAt();

        return $customerGroupTransfer->setCreatedAt(
            $createdAt instanceof DateTimeInterface ? $createdAt->format(static::DATE_TIME_FORMAT) : null,
        );
    }
}
