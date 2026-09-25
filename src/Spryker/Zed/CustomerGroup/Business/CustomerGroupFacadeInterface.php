<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business;

use Generated\Shared\Transfer\CustomerCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionResponseTransfer;
use Generated\Shared\Transfer\CustomerGroupCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerGroupFacadeInterface
{
    /**
     * Specification:
     *  - Adds new group
     *  - If list of customers is not empty, assigns customers, specified in customers array to group
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupTransfer
     */
    public function add(CustomerGroupTransfer $customerGroupTransfer);

    /**
     * Specification:
     *  - Finds customer group by customer group ID
     *  - Throws CustomerGroupNotFoundException if not found
     *  - Incoming CustomerGroupTransfer is modified with data from DB
     *  - If group has customers assigned, they are fetched and returned in Customers property
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @throws \Spryker\Zed\CustomerGroup\Business\Exception\CustomerGroupNotFoundException
     *
     * @return \Generated\Shared\Transfer\CustomerGroupTransfer
     */
    public function get(CustomerGroupTransfer $customerGroupTransfer);

    /**
     * Specification:
     *  - Finds customer group by customer group ID
     *  - Throws CustomerGroupNotFoundException if not found
     *  - Entity is modified with data from CustomerGroupTransfer and saved
     *  - All assigned customers are deleted, customers specified in getCustomers are assigned to the group
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @throws \Spryker\Zed\CustomerGroup\Business\Exception\CustomerGroupNotFoundException
     *
     * @return void
     */
    public function update(CustomerGroupTransfer $customerGroupTransfer);

    /**
     * Specification:
     *  - Finds customer group by customer group ID
     *  - Throws CustomerGroupNotFoundException if not found
     *  - Deletes customer group
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @throws \Spryker\Zed\CustomerGroup\Business\Exception\CustomerGroupNotFoundException
     *
     * @return void
     */
    public function delete(CustomerGroupTransfer $customerGroupTransfer);

    /**
     * Specification:
     *  - If getCustomers is empty, does nothing
     *  - Finds customers from getCustomers assigned to the group and unassigns them
     *  - If costomer is not assign to the group, skips
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return void
     */
    public function removeCustomersFromGroup(CustomerGroupTransfer $customerGroupTransfer);

    /**
     * Specification:
     *  - Finds Customer group by given customer id
     *
     * @api
     *
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupTransfer|null
     */
    public function findCustomerGroupByIdCustomer($idCustomer);

    /**
     * Specification:
     *  - Retrieves all customer groups by customer id
     *
     * @api
     *
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionTransfer
     */
    public function getCustomerGroupCollectionByIdCustomer(int $idCustomer): CustomerGroupCollectionTransfer;

    /**
     * Specification:
     *  - Finds all groups which a customer participates
     *  - Removes each customer group in the found batch
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return void
     */
    public function removeCustomerFromAllGroups(CustomerTransfer $customerTransfer);

    /**
     * Specification:
     *  - Retrieves a customer group collection filtered by the given criteria.
     *  - Supported conditions: uuids, names, and a free-text search term matched against name and description.
     *  - Applies `CustomerGroupConditionsTransfer.uuids` only while the `spy_customer_group.uuid`
     *    column is installed, as that column is contributed by an optional schema extension rather
     *    than by this module.
     *  - Supported sort fields: `name`, `createdAt`; unsupported fields are ignored.
     *  - Populates `CustomerGroupCollectionTransfer.pagination` with the total result count when
     *    the criteria carries a pagination transfer.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerGroupCollectionTransfer
     */
    public function getCustomerGroupCollection(
        CustomerGroupCriteriaTransfer $customerGroupCriteriaTransfer
    ): CustomerGroupCollectionTransfer;

    /**
     * Specification:
     *  - Retrieves the customers assigned to the customer groups named by the criteria.
     *  - Scopes the result to `CustomerGroupCustomerConditionsTransfer.customerGroupIds`; without it every customer is returned.
     *  - Returns a customer once even when it belongs to more than one of the given groups.
     *  - Supported sort fields: `email`, `firstName`, `lastName`; unsupported fields are ignored.
     *  - Populates `CustomerCollectionTransfer.pagination` with the total result count when the
     *    criteria carries a pagination transfer.
     *  - Returns identification data only (id, reference, email, first and last name).
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerCollectionTransfer
     */
    public function getCustomerCollectionByCustomerGroupCriteria(
        CustomerGroupCustomerCriteriaTransfer $customerGroupCustomerCriteriaTransfer
    ): CustomerCollectionTransfer;

    /**
     * Specification:
     * - Creates customer groups from the collection.
     * - Rejects an item whose name is already taken by another group, compared case-insensitively.
     * - Rejects an item carrying a customer reference that matches no customer.
     * - Runs both checks over every item, so one request reports every problem it has.
     * - Adds an error to the response for each invalid item, with the offending value in `Error.entityIdentifier`.
     * - Persists no items and returns early when `CustomerGroupCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `CustomerGroupCollectionRequestTransfer.isTransactional` is `false`.
     * - Resolves `CustomerGroupTransfer.customerReferences` (with `hasAssignmentChange` set) into `customerAssignment` ids before writing.
     * - Delegates the write itself to the same logic as {@link static::add()}.
     * - Returns the persisted items alongside any validation errors.
     * - Populates `CustomerGroupTransfer.uuid` on the persisted items only while the
     *   `spy_customer_group.uuid` column is installed, as the uuid behavior that generates it comes
     *   with that optional schema extension; the property stays null otherwise.
     *
     * @api
     */
    public function createCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates customer groups from the collection by their `CustomerGroupTransfer.idCustomerGroup`.
     * - Rejects an item whose name is already taken by another group, compared case-insensitively.
     * - Rejects an item carrying a customer reference that matches no customer.
     * - Runs both checks over every item, so one request reports every problem it has.
     * - Adds an error to the response for each invalid item, with the offending value in `Error.entityIdentifier`.
     * - Persists no items and returns early when `CustomerGroupCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `CustomerGroupCollectionRequestTransfer.isTransactional` is `false`.
     * - Applies the assignment as a delta from `CustomerGroupTransfer.customerAssignment`: `idsCustomerToDeAssign` are removed and `idsCustomerToAssign` are added.
     * - Lets callers express the assignment in customer references instead by setting `customerReferences` plus `hasAssignmentChange`, and `isAssignmentReplacement` when the list is the complete new member set.
     * - Reduces such an assignment change to a delta against the stored members, so re-assigning an existing member is a no-op rather than a unique-constraint violation.
     * - Delegates the write itself to the same logic as {@link static::update()}.
     * - Returns all items from the request alongside any validation errors.
     *
     * @api
     */
    public function updateCustomerGroupCollection(
        CustomerGroupCollectionRequestTransfer $customerGroupCollectionRequestTransfer
    ): CustomerGroupCollectionResponseTransfer;
}
