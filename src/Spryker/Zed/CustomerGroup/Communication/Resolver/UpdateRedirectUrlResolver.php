<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Resolver;

use Spryker\Zed\CustomerGroup\CustomerGroupConfig;

class UpdateRedirectUrlResolver implements UpdateRedirectUrlResolverInterface
{
    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Controller\EditController::indexAction()
     */
    protected const string URL_EDIT = '/customer-group/edit';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Controller\ViewController::indexAction()
     */
    protected const string URL_VIEW = '/customer-group/view';

    /**
     * @uses \Spryker\Zed\CustomerGroup\Communication\Controller\EditController::PARAM_ID_CUSTOMER_GROUP
     */
    protected const string PARAM_ID_CUSTOMER_GROUP = 'id-customer-group';

    public function __construct(protected CustomerGroupConfig $customerGroupConfig)
    {
    }

    public function resolveUpdateRedirectUrl(int $idCustomerGroup): string
    {
        $url = $this->customerGroupConfig->isRedirectToEditPageAfterUpdateEnabled() ? static::URL_EDIT : static::URL_VIEW;

        return sprintf('%s?%s=%d', $url, static::PARAM_ID_CUSTOMER_GROUP, $idCustomerGroup);
    }
}
