<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Reader;

interface CustomerGroupSuggestionReaderInterface
{
    /**
     * @return list<array<string, string>>
     */
    public function getSuggestionsBySearchTerm(string $searchTerm): array;
}
