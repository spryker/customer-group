<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Zed\CustomerGroup\Persistence\Fixtures;

class CustomerGroupUuidGuardProbeWithoutUuidColumn extends CustomerGroupUuidGuardProbe
{
    protected const string UUID_FILTER_METHOD = 'filterByUuidColumnThatDoesNotExist_In';
}
