<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Controller;

use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface getFacade()
 * @method \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory getFactory()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 */
class SuggestController extends AbstractController
{
    protected const string PARAM_TERM = 'term';

    protected const string KEY_RESULTS = 'results';

    public function indexAction(Request $request): JsonResponse
    {
        $searchTerm = (string)$request->query->get(static::PARAM_TERM);

        return $this->jsonResponse([
            static::KEY_RESULTS => $this->getFactory()
                ->createCustomerGroupSuggestionReader()
                ->getSuggestionsBySearchTerm($searchTerm),
        ]);
    }
}
