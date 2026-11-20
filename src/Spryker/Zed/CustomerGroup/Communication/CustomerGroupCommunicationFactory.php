<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication;

use Generated\Shared\Transfer\CustomerGroupTransfer;
use Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpander;
use Spryker\Zed\CustomerGroup\Communication\Expander\CustomerGroupViewExpanderInterface;
use Spryker\Zed\CustomerGroup\Communication\Form\CustomerGroupForm;
use Spryker\Zed\CustomerGroup\Communication\Form\CustomerGroupTableFilterForm;
use Spryker\Zed\CustomerGroup\Communication\Form\DataProvider\CustomerGroupFormDataProvider;
use Spryker\Zed\CustomerGroup\Communication\Form\DataProvider\CustomerGroupTableFilterFormDataProvider;
use Spryker\Zed\CustomerGroup\Communication\Reader\CustomerGroupSuggestionReader;
use Spryker\Zed\CustomerGroup\Communication\Reader\CustomerGroupSuggestionReaderInterface;
use Spryker\Zed\CustomerGroup\Communication\Resolver\CustomerGroupAssignmentFilterResolver;
use Spryker\Zed\CustomerGroup\Communication\Resolver\CustomerGroupAssignmentFilterResolverInterface;
use Spryker\Zed\CustomerGroup\Communication\Resolver\UpdateRedirectUrlResolver;
use Spryker\Zed\CustomerGroup\Communication\Resolver\UpdateRedirectUrlResolverInterface;
use Spryker\Zed\CustomerGroup\Communication\Sorter\SectionSorter;
use Spryker\Zed\CustomerGroup\Communication\Sorter\SectionSorterInterface;
use Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AssignedCustomerTable;
use Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AssignmentCustomerQueryBuilder;
use Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AvailableCustomerTable;
use Spryker\Zed\CustomerGroup\Communication\Table\CustomerGroupTable;
use Spryker\Zed\CustomerGroup\Communication\Table\CustomerTable;
use Spryker\Zed\CustomerGroup\Communication\Tabs\CustomerGroupFormTabs;
use Spryker\Zed\CustomerGroup\CustomerGroupDependencyProvider;
use Spryker\Zed\CustomerGroup\Dependency\Service\CustomerGroupToUtilSanitizeServiceInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\CustomerGroupConfig getConfig()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 * @method \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface getFacade()
 */
class CustomerGroupCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \Spryker\Zed\CustomerGroup\Communication\Table\CustomerGroupTable
     */
    public function createCustomerGroupTable()
    {
        return new CustomerGroupTable(
            $this->getQueryContainer(),
            $this->getDateFormatterService(),
        );
    }

    public function createCustomerGroupTableFilterForm(Request $request): FormInterface
    {
        $customerGroupTableFilterFormDataProvider = $this->createCustomerGroupTableFilterFormDataProvider();

        return $this->getFormFactory()->create(
            CustomerGroupTableFilterForm::class,
            $customerGroupTableFilterFormDataProvider->getData(),
            $customerGroupTableFilterFormDataProvider->getOptions($request),
        );
    }

    public function createCustomerGroupTableFilterFormDataProvider(): CustomerGroupTableFilterFormDataProvider
    {
        return new CustomerGroupTableFilterFormDataProvider($this->createCustomerGroupAssignmentFilterResolver());
    }

    public function createCustomerGroupAssignmentFilterResolver(): CustomerGroupAssignmentFilterResolverInterface
    {
        return new CustomerGroupAssignmentFilterResolver($this->getCustomerGroupAssignmentFilterPlugins());
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface>
     */
    public function getCustomerGroupAssignmentFilterPlugins(): array
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER);
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $customerGroupTransfer
     *
     * @return \Spryker\Zed\CustomerGroup\Communication\Table\CustomerTable
     */
    public function createCustomerTable(CustomerGroupTransfer $customerGroupTransfer)
    {
        return new CustomerTable(
            $this->getQueryContainer(),
            $customerGroupTransfer,
        );
    }

    /**
     * @param int|null $idCustomerGroup
     *
     * @return \Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AssignedCustomerTable
     */
    public function createAssignedCustomerTable($idCustomerGroup = null)
    {
        return new AssignedCustomerTable(
            $this->createAssignmentCustomerQueryBuilder(),
            $this->getUtilEncodingService(),
            $idCustomerGroup,
            $this->getUtilSanitizeService(),
        );
    }

    /**
     * @param int|null $idCustomerGroup
     *
     * @return \Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AvailableCustomerTable
     */
    public function createAvailableCustomerTable($idCustomerGroup = null)
    {
        return new AvailableCustomerTable(
            $this->createAssignmentCustomerQueryBuilder(),
            $this->getUtilEncodingService(),
            $idCustomerGroup,
            $this->getUtilSanitizeService(),
        );
    }

    /**
     * @return \Spryker\Zed\CustomerGroup\Communication\Table\Assignment\AssignmentCustomerQueryBuilder
     */
    protected function createAssignmentCustomerQueryBuilder()
    {
        return new AssignmentCustomerQueryBuilder(
            $this->getCustomerQueryContainer(),
        );
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerGroupTransfer $data
     * @param array<string, mixed> $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCustomerGroupForm(CustomerGroupTransfer $data, array $options = [])
    {
        $options[CustomerGroupForm::ID_CUSTOMER_GROUP] = $data->getIdCustomerGroup();

        return $this->getFormFactory()->create(CustomerGroupForm::class, $data, $options);
    }

    /**
     * @return \Spryker\Zed\Gui\Communication\Tabs\TabsInterface
     */
    public function createCustomerGroupFormTabs()
    {
        return new CustomerGroupFormTabs($this->createSectionSorter(), $this->getCustomerGroupFormTabExpanderPlugins());
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface>
     */
    public function getCustomerGroupFormTabExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormExpanderPluginInterface>
     */
    public function getCustomerGroupFormExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER);
    }

    public function createCustomerGroupViewExpander(): CustomerGroupViewExpanderInterface
    {
        return new CustomerGroupViewExpander($this->getCustomerGroupViewExpanderPlugins(), $this->createSectionSorter());
    }

    public function createSectionSorter(): SectionSorterInterface
    {
        return new SectionSorter($this->getConfig());
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupViewExpanderPluginInterface>
     */
    public function getCustomerGroupViewExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER);
    }

    public function createCustomerGroupSuggestionReader(): CustomerGroupSuggestionReaderInterface
    {
        return new CustomerGroupSuggestionReader($this->getRepository(), $this->getConfig());
    }

    public function createUpdateRedirectUrlResolver(): UpdateRedirectUrlResolverInterface
    {
        return new UpdateRedirectUrlResolver($this->getConfig());
    }

    /**
     * @return \Spryker\Zed\CustomerGroup\Communication\Form\DataProvider\CustomerGroupFormDataProvider
     */
    public function createCustomerGroupFormDataProvider()
    {
        return new CustomerGroupFormDataProvider($this->getQueryContainer());
    }

    /**
     * @return \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface
     */
    protected function getDateFormatterService()
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::SERVICE_DATE_FORMATTER);
    }

    /**
     * @return \Spryker\Zed\CustomerGroup\Dependency\QueryContainer\CustomerGroupToCustomerQueryContainerInterface
     */
    protected function getCustomerQueryContainer()
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::QUERY_CONTAINER_CUSTOMER);
    }

    /**
     * @return \Spryker\Zed\CustomerGroup\Dependency\Service\CustomerGroupToUtilEncodingInterface
     */
    protected function getUtilEncodingService()
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    public function getUtilSanitizeService(): CustomerGroupToUtilSanitizeServiceInterface
    {
        return $this->getProvidedDependency(CustomerGroupDependencyProvider::SERVICE_UTIL_SANITIZE);
    }
}
