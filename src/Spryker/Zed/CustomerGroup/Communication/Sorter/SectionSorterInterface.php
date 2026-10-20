<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Sorter;

interface SectionSorterInterface
{
    /**
     * @template TSection
     *
     * @param array<string, TSection> $sectionsByName
     *
     * @return array<string, TSection>
     */
    public function sortByFormTabOrder(array $sectionsByName): array;

    /**
     * @param array<\Generated\Shared\Transfer\TabItemTransfer> $tabItemTransfers
     *
     * @return list<\Generated\Shared\Transfer\TabItemTransfer>
     */
    public function sortTabsByFormTabOrder(array $tabItemTransfers): array;
}
