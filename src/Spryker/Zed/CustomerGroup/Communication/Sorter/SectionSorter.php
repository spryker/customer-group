<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Sorter;

use Generated\Shared\Transfer\TabItemTransfer;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;

class SectionSorter implements SectionSorterInterface
{
    public function __construct(protected CustomerGroupConfig $customerGroupConfig)
    {
    }

    /**
     * @template TSection
     *
     * @param array<string, TSection> $sectionsByName
     *
     * @return array<string, TSection>
     */
    public function sortByFormTabOrder(array $sectionsByName): array
    {
        $listedSectionsByName = [];

        foreach ($this->customerGroupConfig->getFormTabOrder() as $sectionName) {
            if (!array_key_exists($sectionName, $sectionsByName)) {
                continue;
            }

            $listedSectionsByName[$sectionName] = $sectionsByName[$sectionName];
        }

        return $listedSectionsByName + $sectionsByName;
    }

    /**
     * @param array<\Generated\Shared\Transfer\TabItemTransfer> $tabItemTransfers
     *
     * @return list<\Generated\Shared\Transfer\TabItemTransfer>
     */
    public function sortTabsByFormTabOrder(array $tabItemTransfers): array
    {
        $positionsByName = array_flip($this->customerGroupConfig->getFormTabOrder());

        usort(
            $tabItemTransfers,
            fn (TabItemTransfer $firstTabItemTransfer, TabItemTransfer $secondTabItemTransfer): int => $this->getFormTabPosition($firstTabItemTransfer, $positionsByName)
                <=> $this->getFormTabPosition($secondTabItemTransfer, $positionsByName),
        );

        return $tabItemTransfers;
    }

    /**
     * @param array<string, int> $positionsByName
     */
    protected function getFormTabPosition(TabItemTransfer $tabItemTransfer, array $positionsByName): int
    {
        return $positionsByName[(string)$tabItemTransfer->getName()] ?? count($positionsByName);
    }
}
