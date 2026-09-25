<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Resolver;

use Generated\Shared\Transfer\CustomerGroupToCustomerAssignmentTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

class CustomerGroupAssignmentResolver implements CustomerGroupAssignmentResolverInterface
{
    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository,
    ) {
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\CustomerGroupTransfer>
     */
    public function resolveCollection(array $customerGroupTransfers): array
    {
        $customerGroupTransfersToResolve = array_filter(
            $customerGroupTransfers,
            static fn (CustomerGroupTransfer $customerGroupTransfer): bool => $customerGroupTransfer->getHasAssignmentChange() === true,
        );

        if ($customerGroupTransfersToResolve === []) {
            return $customerGroupTransfers;
        }

        $customerIdsIndexedByReference = $this->customerGroupRepository
            ->getCustomerIdsIndexedByCustomerReference(
                $this->collectCustomerReferences($customerGroupTransfersToResolve),
            );

        $currentCustomerIdsGroupedByIdCustomerGroup = $this->customerGroupRepository
            ->getCustomerIdsGroupedByIdCustomerGroup(
                $this->collectCustomerGroupIds($customerGroupTransfersToResolve),
            );

        foreach ($customerGroupTransfersToResolve as $customerGroupTransfer) {
            $this->resolveAssignment(
                $customerGroupTransfer,
                $customerIdsIndexedByReference,
                $currentCustomerIdsGroupedByIdCustomerGroup,
            );
        }

        return $customerGroupTransfers;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, string>
     */
    protected function collectCustomerReferences(array $customerGroupTransfers): array
    {
        $customerReferences = [];

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $customerReferences[] = $customerGroupTransfer->getCustomerReferences();
        }

        return array_values(array_unique(array_merge(...$customerReferences)));
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, int>
     */
    protected function collectCustomerGroupIds(array $customerGroupTransfers): array
    {
        $customerGroupIds = [];

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            if ($customerGroupTransfer->getIdCustomerGroup() === null) {
                continue;
            }

            $customerGroupIds[] = $customerGroupTransfer->getIdCustomerGroupOrFail();
        }

        return array_values(array_unique($customerGroupIds));
    }

    /**
     * @param array<string, int> $customerIdsIndexedByReference
     * @param array<int, array<int, int>> $currentCustomerIdsGroupedByIdCustomerGroup
     */
    protected function resolveAssignment(
        CustomerGroupTransfer $customerGroupTransfer,
        array $customerIdsIndexedByReference,
        array $currentCustomerIdsGroupedByIdCustomerGroup
    ): void {
        $idCustomerGroup = $customerGroupTransfer->getIdCustomerGroup();
        $currentCustomerIds = $currentCustomerIdsGroupedByIdCustomerGroup[$idCustomerGroup] ?? [];

        $targetCustomerIds = array_values(array_intersect_key(
            $customerIdsIndexedByReference,
            array_flip($customerGroupTransfer->getCustomerReferences()),
        ));

        $customerGroupToCustomerAssignmentTransfer = $customerGroupTransfer->getCustomerAssignment()
            ?? new CustomerGroupToCustomerAssignmentTransfer();

        $customerGroupTransfer->setCustomerAssignment(
            $customerGroupToCustomerAssignmentTransfer
                ->setIdCustomerGroup($idCustomerGroup)
                ->setIdsCustomerToAssign(array_values(array_diff($targetCustomerIds, $currentCustomerIds)))
                ->setIdsCustomerToDeAssign(
                    $this->resolveCustomerIdsToDeAssign(
                        $customerGroupTransfer,
                        $targetCustomerIds,
                        $currentCustomerIds,
                    ),
                ),
        );
    }

    /**
     * @param array<int, int> $targetCustomerIds
     * @param array<int, int> $currentCustomerIds
     *
     * @return array<int, int>
     */
    protected function resolveCustomerIdsToDeAssign(
        CustomerGroupTransfer $customerGroupTransfer,
        array $targetCustomerIds,
        array $currentCustomerIds
    ): array {
        if ($customerGroupTransfer->getIsAssignmentReplacement() !== true) {
            return [];
        }

        return array_values(array_diff($currentCustomerIds, $targetCustomerIds));
    }
}
