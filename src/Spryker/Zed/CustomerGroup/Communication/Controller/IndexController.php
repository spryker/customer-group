<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Controller;

use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;
use Spryker\Zed\CustomerGroup\Communication\Table\CustomerGroupTable;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface getFacade()
 * @method \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory getFactory()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 */
class IndexController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    public function indexAction(Request $request): array
    {
        $customerGroupTableFilterForm = $this->createHandledCustomerGroupTableFilterForm($request);
        $viewData = [
            'customerGroupTable' => $this->createFilteredCustomerGroupTable($customerGroupTableFilterForm)->render(),
        ];

        if (!$this->getFactory()->createCustomerGroupAssignmentFilterResolver()->hasAssignmentFilters()) {
            return $this->viewResponse($viewData);
        }

        $viewData['customerGroupTableFilterForm'] = $customerGroupTableFilterForm->createView();

        return $this->viewResponse($viewData);
    }

    public function tableAction(Request $request): JsonResponse
    {
        $customerGroupTable = $this->createFilteredCustomerGroupTable($this->createHandledCustomerGroupTableFilterForm($request));

        return $this->jsonResponse($customerGroupTable->fetchData());
    }

    protected function createHandledCustomerGroupTableFilterForm(Request $request): FormInterface
    {
        return $this->getFactory()
            ->createCustomerGroupTableFilterForm($request)
            ->handleRequest($request);
    }

    protected function createFilteredCustomerGroupTable(FormInterface $customerGroupTableFilterForm): CustomerGroupTable
    {
        $customerGroupTable = $this->getFactory()->createCustomerGroupTable();
        $customerGroupTable->applyCriteria(
            $this->getFactory()
                ->createCustomerGroupAssignmentFilterResolver()
                ->createCustomerGroupCriteria($customerGroupTableFilterForm->getData() ?? new CustomerGroupTableFilterTransfer()),
        );

        return $customerGroupTable;
    }
}
