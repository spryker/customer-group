<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Reader;

use Generated\Shared\Transfer\CustomerGroupConditionsTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Spryker\Zed\CustomerGroup\CustomerGroupConfig;
use Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface;

class CustomerGroupSuggestionReader implements CustomerGroupSuggestionReaderInterface
{
    protected const string KEY_ID = 'id';

    protected const string KEY_TEXT = 'text';

    protected const int FIRST_PAGE = 1;

    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository,
        protected CustomerGroupConfig $customerGroupConfig
    ) {
    }

    /**
     * @return list<array<string, string>>
     */
    public function getSuggestionsBySearchTerm(string $searchTerm): array
    {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return [];
        }

        $customerGroupCollectionTransfer = $this->customerGroupRepository->getCustomerGroupCollection(
            $this->createCustomerGroupCriteriaTransfer($searchTerm),
        );

        $suggestions = [];

        foreach ($customerGroupCollectionTransfer->getGroups() as $customerGroupTransfer) {
            $suggestions[] = [
                static::KEY_ID => $customerGroupTransfer->getNameOrFail(),
                static::KEY_TEXT => $customerGroupTransfer->getNameOrFail(),
            ];
        }

        return $suggestions;
    }

    protected function createCustomerGroupCriteriaTransfer(string $searchTerm): CustomerGroupCriteriaTransfer
    {
        return (new CustomerGroupCriteriaTransfer())
            ->setCustomerGroupConditions((new CustomerGroupConditionsTransfer())->setSearchTerm($searchTerm))
            ->addSort(
                (new SortTransfer())
                    ->setField(CustomerGroupConfig::SORT_FIELD_NAME)
                    ->setIsAscending(true),
            )
            ->setPagination(
                (new PaginationTransfer())
                    ->setPage(static::FIRST_PAGE)
                    ->setMaxPerPage($this->customerGroupConfig->getCustomerGroupSuggestionLimit()),
            );
    }
}
