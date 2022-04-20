<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Controller;

use Generated\Shared\Transfer\CustomerGroupToCustomerTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface getFacade()
 * @method \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory getFactory()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 */
class DeleteController extends EditController
{
    /**
     * @var string
     */
    public const PARAM_ID_CUSTOMER = 'id-customer';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function indexAction(Request $request)
    {
        $idCustomerGroup = $this->castId($request->get(static::PARAM_ID_CUSTOMER_GROUP));

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setIdCustomerGroup($idCustomerGroup);

        $this->getFacade()->delete(
            $customerGroupTransfer,
        );

        return $this->redirectResponse('/customer-group');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function customerAction(Request $request)
    {
        $idCustomerGroup = $this->castId($request->get(static::PARAM_ID_CUSTOMER_GROUP));
        $idCustomer = $this->castId($request->get(static::PARAM_ID_CUSTOMER));

        $customerGroupTransfer = new CustomerGroupTransfer();
        $customerGroupTransfer->setIdCustomerGroup($idCustomerGroup);

        $customer = new CustomerGroupToCustomerTransfer();
        $customer->setFkCustomer($idCustomer);
        $customerGroupTransfer->addCustomer($customer);

        $this->getFacade()->removeCustomersFromGroup($customerGroupTransfer);

        return $this->redirectResponse('/customer-group/view?id-customer-group=' . $idCustomerGroup);
    }
}
