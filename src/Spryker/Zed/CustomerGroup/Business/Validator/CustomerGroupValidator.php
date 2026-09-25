<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Validator;

use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupResponseTransfer;

class CustomerGroupValidator implements CustomerGroupValidatorInterface
{
    public function __construct(
        protected CustomerGroupNameUniquenessValidatorInterface $customerGroupNameUniquenessValidator,
        protected CustomerGroupCustomerExistenceValidatorInterface $customerGroupCustomerExistenceValidator,
    ) {
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CustomerGroupTransfer> $customerGroupTransfers
     */
    public function validateCollection(array $customerGroupTransfers): CustomerGroupCollectionResponseTransfer
    {
        $customerGroupCollectionResponseTransfer = new CustomerGroupCollectionResponseTransfer();

        $nameUniquenessResponseTransfers = $this->customerGroupNameUniquenessValidator
            ->validateCollection($customerGroupTransfers);
        $customerExistenceResponseTransfers = $this->customerGroupCustomerExistenceValidator
            ->validateCollection($customerGroupTransfers);

        foreach ($customerGroupTransfers as $key => $customerGroupTransfer) {
            $isNameValid = $this->collectErrors(
                $nameUniquenessResponseTransfers[$key],
                $customerGroupCollectionResponseTransfer,
            );
            $isCustomerAssignmentValid = $this->collectErrors(
                $customerExistenceResponseTransfers[$key],
                $customerGroupCollectionResponseTransfer,
            );

            if (!$isNameValid || !$isCustomerAssignmentValid) {
                continue;
            }

            $customerGroupCollectionResponseTransfer->addCustomerGroup($customerGroupTransfer);
        }

        return $customerGroupCollectionResponseTransfer;
    }

    protected function collectErrors(
        CustomerGroupResponseTransfer $customerGroupResponseTransfer,
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): bool {
        foreach ($customerGroupResponseTransfer->getErrors() as $errorTransfer) {
            $customerGroupCollectionResponseTransfer->addError($errorTransfer);
        }

        return $customerGroupResponseTransfer->getIsSuccessful() === true;
    }
}
