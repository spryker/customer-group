<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business\Writer;

use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Business\Model\CustomerGroupInterface;
use Spryker\Zed\CustomerGroup\Business\Resolver\CustomerGroupAssignmentResolverInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupValidatorInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CustomerGroupWriter implements CustomerGroupWriterInterface
{
    use TransactionTrait;

    public function __construct(
        protected CustomerGroupInterface $customerGroupModel,
        protected CustomerGroupValidatorInterface $customerGroupValidator,
        protected CustomerGroupAssignmentResolverInterface $customerGroupAssignmentResolver,
    ) {
    }

    public function createCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): CustomerGroupCollectionResponseTransfer => $this->persistCollection(
                $customerGroupCollectionRequestTransfer,
                fn (CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer => $this->customerGroupModel->add($customerGroupTransfer),
            ),
        );
    }

    public function updateCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): CustomerGroupCollectionResponseTransfer => $this->persistCollection(
                $customerGroupCollectionRequestTransfer,
                function (CustomerGroupTransfer $customerGroupTransfer): CustomerGroupTransfer {
                    $this->customerGroupModel->update($customerGroupTransfer);

                    return $customerGroupTransfer;
                },
            ),
        );
    }

    /**
     * @param callable(\Generated\Shared\Transfer\CustomerGroupTransfer): \Generated\Shared\Transfer\CustomerGroupTransfer $persistCallback
     */
    protected function persistCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer,
        callable $persistCallback
    ): CustomerGroupCollectionResponseTransfer {
        $customerGroupValidationResponseTransfer = $this->customerGroupValidator->validateCollection(
            array_values(iterator_to_array($customerGroupCollectionRequestTransfer->getCustomerGroups())),
        );

        $customerGroupCollectionResponseTransfer = new CustomerGroupCollectionResponseTransfer();

        foreach ($customerGroupValidationResponseTransfer->getErrors() as $errorTransfer) {
            $customerGroupCollectionResponseTransfer->addError($errorTransfer);
        }

        if ($this->shouldSkipPersistence($customerGroupCollectionRequestTransfer, $customerGroupCollectionResponseTransfer)) {
            return $customerGroupCollectionResponseTransfer;
        }

        $resolvedCustomerGroupTransfers = $this->customerGroupAssignmentResolver->resolveCollection(
            array_values(iterator_to_array($customerGroupValidationResponseTransfer->getCustomerGroups())),
        );

        foreach ($resolvedCustomerGroupTransfers as $customerGroupTransfer) {
            $customerGroupCollectionResponseTransfer->addCustomerGroup($persistCallback($customerGroupTransfer));
        }

        return $customerGroupCollectionResponseTransfer;
    }

    protected function shouldSkipPersistence(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer,
        CustomerGroupCollectionResponseTransfer $customerGroupCollectionResponseTransfer
    ): bool {
        return $customerGroupCollectionRequestTransfer->getIsTransactional() === true
            && $customerGroupCollectionResponseTransfer->getErrors()->count() > 0;
    }
}
