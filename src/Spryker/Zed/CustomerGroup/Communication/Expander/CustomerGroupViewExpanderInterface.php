<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\CustomerGroup\Communication\Expander;

use Generated\Shared\Transfer\CustomerGroupTransfer;

interface CustomerGroupViewExpanderInterface
{
    /**
     * @param array<string, mixed> $viewData
     *
     * @return array<string, mixed>
     */
    public function expandViewData(array $viewData, CustomerGroupTransfer $customerGroupTransfer): array;
}
