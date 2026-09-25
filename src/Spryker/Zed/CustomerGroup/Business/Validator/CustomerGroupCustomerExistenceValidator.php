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

class CustomerGroupCustomerExistenceValidator implements CustomerGroupCustomerExistenceValidatorInterface
{
    /**
     * @api
     *
     * @var string
     */
    public const GLOSSARY_KEY_ERROR_CUSTOMER_NOT_FOUND = 'message.customer_group.validation.customer_not_found';

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
        $knownCustomerReferences = array_keys(
            $this->customerGroupRepository->getCustomerIdsIndexedByCustomerReference(
                $this->collectCustomerReferences($customerGroupTransfers),
            ),
        );

        $customerGroupResponseTransfers = [];

        foreach ($customerGroupTransfers as $key => $customerGroupTransfer) {
            $customerGroupResponseTransfers[$key] = $this->validate($customerGroupTransfer, $knownCustomerReferences);
        }

        return $customerGroupResponseTransfers;
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
            if ($customerGroupTransfer->getHasAssignmentChange() !== true) {
                continue;
            }

            $customerReferences[] = $customerGroupTransfer->getCustomerReferences();
        }

        if ($customerReferences === []) {
            return [];
        }

        return array_values(array_unique(array_merge(...$customerReferences)));
    }

    /**
     * @param array<int, string> $knownCustomerReferences
     */
    protected function validate(
        CustomerGroupTransfer $customerGroupTransfer,
        array $knownCustomerReferences
    ): CustomerGroupResponseTransfer {
        $customerGroupResponseTransfer = (new CustomerGroupResponseTransfer())
            ->setIsSuccessful(true)
            ->setCustomerGroup($customerGroupTransfer);

        if ($customerGroupTransfer->getHasAssignmentChange() !== true) {
            return $customerGroupResponseTransfer;
        }

        $unknownCustomerReferences = array_diff(
            $customerGroupTransfer->getCustomerReferences(),
            $knownCustomerReferences,
        );

        if ($unknownCustomerReferences === []) {
            return $customerGroupResponseTransfer;
        }

        return $this->rejectUnknownCustomerReferences($customerGroupResponseTransfer, $unknownCustomerReferences);
    }

    /**
     * @param array<int, string> $unknownCustomerReferences
     */
    protected function rejectUnknownCustomerReferences(
        CustomerGroupResponseTransfer $customerGroupResponseTransfer,
        array $unknownCustomerReferences
    ): CustomerGroupResponseTransfer {
        foreach ($unknownCustomerReferences as $unknownCustomerReference) {
            $customerGroupResponseTransfer->addError(
                (new ErrorTransfer())
                    ->setMessage(static::GLOSSARY_KEY_ERROR_CUSTOMER_NOT_FOUND)
                    ->setEntityIdentifier($unknownCustomerReference),
            );
        }

        return $customerGroupResponseTransfer->setIsSuccessful(false);
    }
}
