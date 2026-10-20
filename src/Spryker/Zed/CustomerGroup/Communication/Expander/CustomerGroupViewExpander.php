<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\CustomerGroup\Communication\Expander;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\Sorter\SectionSorterInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;

class CustomerGroupViewExpander implements CustomerGroupViewExpanderInterface
{
    protected const string VIEW_DATA_KEY_BLOCKS = 'blocks';

    protected const string BLOCK_KEY_TEMPLATE_PATH = 'templatePath';

    protected const string BLOCK_KEY_TEMPLATE_DATA = 'templateData';

    protected const string TEMPLATE_PATH_GENERAL_INFORMATION = '@CustomerGroup/View/_partials/general-information.twig';

    protected const string TEMPLATE_PATH_CUSTOMERS = '@CustomerGroup/View/_partials/customers.twig';

    /**
     * @param list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupViewExpanderPluginInterface> $customerGroupViewExpanderPlugins
     */
    public function __construct(
        protected array $customerGroupViewExpanderPlugins,
        protected SectionSorterInterface $sectionSorter
    ) {
    }

    /**
     * @param array<string, mixed> $viewData
     *
     * @return array<string, mixed>
     */
    public function expandViewData(array $viewData, CustomerGroupTransfer $customerGroupTransfer): array
    {
        $viewData[static::VIEW_DATA_KEY_BLOCKS] = $this->sectionSorter->sortByFormTabOrder(
            $this->getBlocks($customerGroupTransfer),
        );

        return $viewData;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getBlocks(CustomerGroupTransfer $customerGroupTransfer): array
    {
        $blocks = [
            CustomerGroupConfig::FORM_TAB_NAME_GENERAL => $this->createBlock(static::TEMPLATE_PATH_GENERAL_INFORMATION, []),
            CustomerGroupConfig::FORM_TAB_NAME_CUSTOMER_ASSIGNMENT => $this->createBlock(static::TEMPLATE_PATH_CUSTOMERS, []),
        ];

        foreach ($this->customerGroupViewExpanderPlugins as $customerGroupViewExpanderPlugin) {
            $blocks[$customerGroupViewExpanderPlugin->getBlockName()] = $this->createBlock(
                $customerGroupViewExpanderPlugin->getTemplatePath(),
                $customerGroupViewExpanderPlugin->getTemplateData($customerGroupTransfer),
            );
        }

        return $blocks;
    }

    /**
     * @param array<string, mixed> $templateData
     *
     * @return array<string, mixed>
     */
    protected function createBlock(string $templatePath, array $templateData): array
    {
        return [
            static::BLOCK_KEY_TEMPLATE_PATH => $templatePath,
            static::BLOCK_KEY_TEMPLATE_DATA => $templateData,
        ];
    }
}
