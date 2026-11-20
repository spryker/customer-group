<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Form\DataProvider;

use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;
use Spryker\Zed\CustomerGroup\Communication\Form\CustomerGroupTableFilterForm;
use Spryker\Zed\CustomerGroup\Communication\Resolver\CustomerGroupAssignmentFilterResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerGroupTableFilterFormDataProvider
{
    public const string OPTION_ASSIGNMENT_TYPE_CHOICES = 'assignment_type_choices';

    public const string OPTION_SUGGEST_URLS = 'suggest_urls';

    public const string OPTION_ASSIGNED_ENTITY_CHOICES = 'assigned_entity_choices';

    public const string OPTION_ASSIGNED_ENTITY_SUGGEST_URL = 'assigned_entity_suggest_url';

    public function __construct(protected CustomerGroupAssignmentFilterResolverInterface $customerGroupAssignmentFilterResolver)
    {
    }

    public function getData(): CustomerGroupTableFilterTransfer
    {
        return new CustomerGroupTableFilterTransfer();
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(Request $request): array
    {
        $customerGroupTableFilterTransfer = $this->mapRequestToCustomerGroupTableFilterTransfer($request);
        $suggestUrls = $this->customerGroupAssignmentFilterResolver->getSuggestUrls();

        return [
            static::OPTION_ASSIGNMENT_TYPE_CHOICES => $this->customerGroupAssignmentFilterResolver->getAssignmentTypeChoices(),
            static::OPTION_SUGGEST_URLS => $suggestUrls,
            static::OPTION_ASSIGNED_ENTITY_CHOICES => $this->getAssignedEntityChoices($customerGroupTableFilterTransfer),
            static::OPTION_ASSIGNED_ENTITY_SUGGEST_URL => $suggestUrls[$customerGroupTableFilterTransfer->getAssignmentType()] ?? '',
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function getAssignedEntityChoices(CustomerGroupTableFilterTransfer $customerGroupTableFilterTransfer): array
    {
        $assignedEntityName = $this->customerGroupAssignmentFilterResolver->findAssignedEntityName($customerGroupTableFilterTransfer);

        if ($assignedEntityName === null) {
            return [];
        }

        return [$assignedEntityName => $customerGroupTableFilterTransfer->getIdAssignedEntityOrFail()];
    }

    protected function mapRequestToCustomerGroupTableFilterTransfer(Request $request): CustomerGroupTableFilterTransfer
    {
        $filterData = $request->query->all(CustomerGroupTableFilterForm::BLOCK_PREFIX);
        $idAssignedEntity = (int)($filterData[CustomerGroupTableFilterForm::FIELD_ID_ASSIGNED_ENTITY] ?? 0);

        return (new CustomerGroupTableFilterTransfer())
            ->setAssignmentType((string)($filterData[CustomerGroupTableFilterForm::FIELD_ASSIGNMENT_TYPE] ?? ''))
            ->setIdAssignedEntity($idAssignedEntity > 0 ? $idAssignedEntity : null);
    }
}
