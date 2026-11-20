<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup;

use Spryker\Zed\CustomerGroup\Dependency\QueryContainer\CustomerGroupToCustomerQueryContainerBridge;
use Spryker\Zed\CustomerGroup\Dependency\Service\CustomerGroupToUtilEncodingBridge;
use Spryker\Zed\CustomerGroup\Dependency\Service\CustomerGroupToUtilSanitizeServiceBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\CustomerGroup\CustomerGroupConfig getConfig()
 */
class CustomerGroupDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const QUERY_CONTAINER_CUSTOMER = 'QUERY_CONTAINER_CUSTOMER';

    /**
     * @var string
     */
    public const SERVICE_DATE_FORMATTER = 'date formatter';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const SERVICE_UTIL_SANITIZE = 'SERVICE_UTIL_SANITIZE';

    public const string PLUGINS_CUSTOMER_GROUP_POST_SAVE = 'PLUGINS_CUSTOMER_GROUP_POST_SAVE';

    public const string PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER = 'PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER';

    public const string PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER = 'PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER';

    public const string PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER = 'PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER';

    public const string PLUGINS_CUSTOMER_GROUP_PROVIDER = 'PLUGINS_CUSTOMER_GROUP_PROVIDER';

    public const string PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER = 'PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER';

    public const string PLUGINS_CUSTOMER_GROUP_QUERY_EXPANDER = 'PLUGINS_CUSTOMER_GROUP_QUERY_EXPANDER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addCustomerGroupPostSavePlugins($container);
        $container = $this->addCustomerGroupProviderPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container)
    {
        $container = parent::providePersistenceLayerDependencies($container);

        $container = $this->addCustomerGroupQueryExpanderPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addDataFormatterService($container);
        $container = $this->addCustomerQueryContainer($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addUtilSanitizeService($container);
        $container = $this->addCustomerGroupFormTabExpanderPlugins($container);
        $container = $this->addCustomerGroupFormExpanderPlugins($container);
        $container = $this->addCustomerGroupViewExpanderPlugins($container);
        $container = $this->addCustomerGroupAssignmentFilterPlugins($container);

        return $container;
    }

    protected function addCustomerGroupAssignmentFilterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_ASSIGNMENT_FILTER, function (): array {
            return $this->getCustomerGroupAssignmentFilterPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupAssignmentFilterPluginInterface>
     */
    protected function getCustomerGroupAssignmentFilterPlugins(): array
    {
        return [];
    }

    protected function addCustomerGroupQueryExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_QUERY_EXPANDER, function (): array {
            return $this->getCustomerGroupQueryExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupQueryExpanderPluginInterface>
     */
    protected function getCustomerGroupQueryExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataFormatterService(Container $container)
    {
        $container->set(static::SERVICE_DATE_FORMATTER, function (Container $container) {
            return $container->getLocator()->utilDateTime()->service();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCustomerQueryContainer(Container $container)
    {
        $container->set(static::QUERY_CONTAINER_CUSTOMER, function (Container $container) {
            return new CustomerGroupToCustomerQueryContainerBridge(
                $container->getLocator()->customer()->queryContainer(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container)
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new CustomerGroupToUtilEncodingBridge($container->getLocator()->utilEncoding()->service());
        });

        return $container;
    }

    protected function addUtilSanitizeService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_SANITIZE, function (Container $container) {
            return new CustomerGroupToUtilSanitizeServiceBridge($container->getLocator()->utilSanitize()->service());
        });

        return $container;
    }

    protected function addCustomerGroupPostSavePlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_POST_SAVE, function (): array {
            return $this->getCustomerGroupPostSavePlugins();
        });

        return $container;
    }

    protected function addCustomerGroupProviderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_PROVIDER, function (): array {
            return $this->getCustomerGroupProviderPlugins();
        });

        return $container;
    }

    protected function addCustomerGroupFormTabExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_FORM_TAB_EXPANDER, function (): array {
            return $this->getCustomerGroupFormTabExpanderPlugins();
        });

        return $container;
    }

    protected function addCustomerGroupFormExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_FORM_EXPANDER, function (): array {
            return $this->getCustomerGroupFormExpanderPlugins();
        });

        return $container;
    }

    protected function addCustomerGroupViewExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CUSTOMER_GROUP_VIEW_EXPANDER, function (): array {
            return $this->getCustomerGroupViewExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupPostSavePluginInterface>
     */
    protected function getCustomerGroupPostSavePlugins(): array
    {
        return [];
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupProviderPluginInterface>
     */
    protected function getCustomerGroupProviderPlugins(): array
    {
        return [];
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormTabExpanderPluginInterface>
     */
    protected function getCustomerGroupFormTabExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupFormExpanderPluginInterface>
     */
    protected function getCustomerGroupFormExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return list<\Spryker\Zed\CustomerGroupExtension\Dependency\Plugin\CustomerGroupViewExpanderPluginInterface>
     */
    protected function getCustomerGroupViewExpanderPlugins(): array
    {
        return [];
    }
}
