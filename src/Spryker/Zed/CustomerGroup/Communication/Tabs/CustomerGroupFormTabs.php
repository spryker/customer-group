<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Tabs;

use ArrayObject;
use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\CustomerGroup\Communication\Sorter\SectionSorterInterface;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use Spryker\Zed\Gui\Communication\Tabs\AbstractTabs;

class CustomerGroupFormTabs extends AbstractTabs
{
    /**
     * @param list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface> $customerGroupFormTabExpanderPlugins
     */
    public function __construct(
        protected SectionSorterInterface $sectionSorter,
        protected array $customerGroupFormTabExpanderPlugins = []
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\TabsViewTransfer $tabsViewTransfer
     *
     * @return \Generated\Shared\Transfer\TabsViewTransfer
     */
    protected function build(TabsViewTransfer $tabsViewTransfer)
    {
        $this
            ->addGeneralInformationTab($tabsViewTransfer)
            ->addCustomerAssignmentTab($tabsViewTransfer);

        $tabsViewTransfer = $this->executeCustomerGroupFormTabExpanderPlugins($tabsViewTransfer);
        $tabsViewTransfer = $this->sortTabsByFormTabOrder($tabsViewTransfer);

        $this->setFooter($tabsViewTransfer);

        $tabsViewTransfer->setIsNavigable(true);

        return $tabsViewTransfer;
    }

    protected function executeCustomerGroupFormTabExpanderPlugins(TabsViewTransfer $tabsViewTransfer): TabsViewTransfer
    {
        foreach ($this->customerGroupFormTabExpanderPlugins as $customerGroupFormTabExpanderPlugin) {
            $tabsViewTransfer = $customerGroupFormTabExpanderPlugin->expand($tabsViewTransfer);
        }

        return $tabsViewTransfer;
    }

    protected function sortTabsByFormTabOrder(TabsViewTransfer $tabsViewTransfer): TabsViewTransfer
    {
        $sortedTabItemTransfers = $this->sectionSorter->sortTabsByFormTabOrder($tabsViewTransfer->getTabs()->getArrayCopy());

        return $tabsViewTransfer->setTabs(new ArrayObject($sortedTabItemTransfers));
    }

    /**
     * @param \Generated\Shared\Transfer\TabsViewTransfer $tabsViewTransfer
     *
     * @return $this
     */
    protected function addGeneralInformationTab(TabsViewTransfer $tabsViewTransfer)
    {
        $tabItemTransfer = new TabItemTransfer();
        $tabItemTransfer
            ->setName(CustomerGroupConfig::FORM_TAB_NAME_GENERAL)
            ->setTitle('General Information')
            ->setTemplate('@CustomerGroup/_partials/general-information.twig');

        $tabsViewTransfer->addTab($tabItemTransfer);

        return $this;
    }

    /**
     * @param \Generated\Shared\Transfer\TabsViewTransfer $tabsViewTransfer
     *
     * @return $this
     */
    protected function addCustomerAssignmentTab(TabsViewTransfer $tabsViewTransfer)
    {
        $tabItemTransfer = new TabItemTransfer();
        $tabItemTransfer
            ->setName(CustomerGroupConfig::FORM_TAB_NAME_CUSTOMER_ASSIGNMENT)
            ->setTitle('Customers')
            ->setTemplate('@CustomerGroup/_partials/customer-assignment.twig');

        $tabsViewTransfer->addTab($tabItemTransfer);

        return $this;
    }

    /**
     * @param \Generated\Shared\Transfer\TabsViewTransfer $tabsViewTransfer
     *
     * @return $this
     */
    protected function setFooter(TabsViewTransfer $tabsViewTransfer)
    {
        $tabsViewTransfer->setFooterTemplate('@CustomerGroup/_partials/_submit_button.twig');

        return $this;
    }
}
