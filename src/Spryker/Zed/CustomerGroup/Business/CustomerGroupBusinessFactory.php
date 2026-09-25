<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Business;

use Spryker\Zed\CustomerGroup\Business\Model\CustomerGroup;
use Spryker\Zed\CustomerGroup\Business\Resolver\CustomerGroupAssignmentResolver;
use Spryker\Zed\CustomerGroup\Business\Resolver\CustomerGroupAssignmentResolverInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidator;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupCustomerExistenceValidatorInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidator;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupNameUniquenessValidatorInterface;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupValidator;
use Spryker\Zed\CustomerGroup\Business\Validator\CustomerGroupValidatorInterface;
use Spryker\Zed\CustomerGroup\Business\Writer\CustomerGroupWriter;
use Spryker\Zed\CustomerGroup\Business\Writer\CustomerGroupWriterInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CustomerGroup\CustomerGroupConfig getConfig()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupEntityManagerInterface getEntityManager()
 */
class CustomerGroupBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CustomerGroup\Business\Model\CustomerGroupInterface
     */
    public function createCustomerGroup()
    {
        return new CustomerGroup($this->getQueryContainer(), $this->getEntityManager());
    }

    public function createCustomerGroupWriter(): CustomerGroupWriterInterface
    {
        return new CustomerGroupWriter(
            $this->createCustomerGroup(),
            $this->createCustomerGroupValidator(),
            $this->createCustomerGroupAssignmentResolver(),
        );
    }

    public function createCustomerGroupValidator(): CustomerGroupValidatorInterface
    {
        return new CustomerGroupValidator(
            $this->createCustomerGroupNameUniquenessValidator(),
            $this->createCustomerGroupCustomerExistenceValidator(),
        );
    }

    public function createCustomerGroupNameUniquenessValidator(): CustomerGroupNameUniquenessValidatorInterface
    {
        return new CustomerGroupNameUniquenessValidator($this->getRepository());
    }

    public function createCustomerGroupCustomerExistenceValidator(): CustomerGroupCustomerExistenceValidatorInterface
    {
        return new CustomerGroupCustomerExistenceValidator($this->getRepository());
    }

    public function createCustomerGroupAssignmentResolver(): CustomerGroupAssignmentResolverInterface
    {
        return new CustomerGroupAssignmentResolver($this->getRepository());
    }
}
