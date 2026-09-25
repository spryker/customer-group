<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Validator;

use Generated\Shared\Transfer\CustomerGroupResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

class CustomerGroupNameUniquenessValidator implements CustomerGroupNameUniquenessValidatorInterface
{
    public const string GLOSSARY_KEY_ERROR_NAME_TAKEN = 'message.customer_group.validation.name_taken';

    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository,
    ) {
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\CustomerGroupResponseTransfer>
     */
    public function validateCollection(array $customerGroupTransfers): array
    {
        $customerGroupIdsGroupedByLowercasedName = $this->customerGroupRepository
            ->getCustomerGroupIdsGroupedByLowercasedName($this->collectNames($customerGroupTransfers));

        $customerGroupResponseTransfers = [];

        foreach ($customerGroupTransfers as $key => $customerGroupTransfer) {
            $customerGroupResponseTransfers[$key] = $this->validate(
                $customerGroupTransfer,
                $customerGroupIdsGroupedByLowercasedName,
            );
        }

        return $customerGroupResponseTransfers;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     *
     * @return array<int, string>
     */
    protected function collectNames(array $customerGroupTransfers): array
    {
        $names = [];

        foreach ($customerGroupTransfers as $customerGroupTransfer) {
            $name = $customerGroupTransfer->getName();

            if ($name === null || $name === '') {
                continue;
            }

            $names[] = $name;
        }

        return array_values(array_unique($names));
    }

    /**
     * @param array<string, array<int, int>> $customerGroupIdsGroupedByLowercasedName
     */
    protected function validate(
        CustomerGroupTransfer $customerGroupTransfer,
        array $customerGroupIdsGroupedByLowercasedName
    ): CustomerGroupResponseTransfer {
        $customerGroupResponseTransfer = (new CustomerGroupResponseTransfer())
            ->setIsSuccessful(true)
            ->setCustomerGroup($customerGroupTransfer);

        $name = $customerGroupTransfer->getName();

        if ($name === null || $name === '') {
            return $customerGroupResponseTransfer;
        }

        $conflictingCustomerGroupIds = array_diff(
            $customerGroupIdsGroupedByLowercasedName[mb_strtolower($name)] ?? [],
            [$customerGroupTransfer->getIdCustomerGroup()],
        );

        if ($conflictingCustomerGroupIds === []) {
            return $customerGroupResponseTransfer;
        }

        return $customerGroupResponseTransfer
            ->setIsSuccessful(false)
            ->addError(
                (new ErrorTransfer())
                    ->setMessage(static::GLOSSARY_KEY_ERROR_NAME_TAKEN)
                    ->setEntityIdentifier($name),
            );
    }
}
