<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Resolver;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;
use Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface;

class CustomerGroupAssignmentFilterResolver implements CustomerGroupAssignmentFilterResolverInterface
{
    /**
     * @param list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface> $customerGroupAssignmentFilterPlugins
     */
    public function __construct(protected array $customerGroupAssignmentFilterPlugins)
    {
    }

    public function hasAssignmentFilters(): bool
    {
        return $this->customerGroupAssignmentFilterPlugins !== [];
    }

    /**
     * @return array<string, string>
     */
    public function getAssignmentTypeChoices(): array
    {
        $assignmentTypeChoices = [];

        foreach ($this->customerGroupAssignmentFilterPlugins as $customerGroupAssignmentFilterPlugin) {
            $assignmentTypeChoices[$customerGroupAssignmentFilterPlugin->getLabel()] = $customerGroupAssignmentFilterPlugin->getAssignmentType();
        }

        return $assignmentTypeChoices;
    }

    /**
     * @return array<string, string>
     */
    public function getSuggestUrls(): array
    {
        $suggestUrls = [];

        foreach ($this->customerGroupAssignmentFilterPlugins as $customerGroupAssignmentFilterPlugin) {
            $suggestUrls[$customerGroupAssignmentFilterPlugin->getAssignmentType()] = $customerGroupAssignmentFilterPlugin->getSuggestUrl();
        }

        return $suggestUrls;
    }

    public function findAssignedEntityName(CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer): ?string
    {
        $customerGroupAssignmentFilterPlugin = $this->findApplicablePlugin($customerGroupTableFilterTransfer);

        if ($customerGroupAssignmentFilterPlugin === null) {
            return null;
        }

        return $customerGroupAssignmentFilterPlugin->findAssignedEntityName($customerGroupTableFilterTransfer->getIdAssignedEntityOrFail());
    }

    public function createCustomerGroupCriteria(CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer): CustomerGroupCriteriaTransfer
    {
        $customerGroupCriteriaTransfer = (new CustomerGroupCriteriaTransfer())
            ->setCustomerGroupConditions(new CustomerGroupConditionsTransfer());
        $customerGroupAssignmentFilterPlugin = $this->findApplicablePlugin($customerGroupTableFilterTransfer);

        if ($customerGroupAssignmentFilterPlugin === null) {
            return $customerGroupCriteriaTransfer;
        }

        return $customerGroupCriteriaTransfer->setCustomerGroupConditions(
            $customerGroupAssignmentFilterPlugin->expandCustomerGroupConditions(
                $customerGroupTableFilterTransfer->getIdAssignedEntityOrFail(),
                $customerGroupCriteriaTransfer->getCustomerGroupConditionsOrFail(),
            ),
        );
    }

    protected function findApplicablePlugin(
        CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer
    ): ?CustomerGroupAssignmentFilterPluginInterface {
        if (!$customerGroupTableFilterTransfer->getIdAssignedEntity()) {
            return null;
        }

        foreach ($this->customerGroupAssignmentFilterPlugins as $customerGroupAssignmentFilterPlugin) {
            if ($customerGroupAssignmentFilterPlugin->getAssignmentType() === $customerGroupTableFilterTransfer->getAssignmentType()) {
                return $customerGroupAssignmentFilterPlugin;
            }
        }

        return null;
    }
}
