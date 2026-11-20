<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerGroup\Communication\Form;

use Generated\Shared\Transfer\CustomerGroupTableFilterTransfer;
use Spryker\Zed\CustomerGroup\Communication\Form\DataProvider\CustomerGroupTableFilterFormDataProvider;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \Spryker\Zed\CustomerGroup\Communication\CustomerGroupCommunicationFactory getFactory()
 * @method \Spryker\Zed\CustomerGroup\CustomerGroupConfig getConfig()
 * @method \Spryker\Zed\CustomerGroup\Business\CustomerGroupFacadeInterface getFacade()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CustomerGroup\Persistence\CustomerGroupRepositoryInterface getRepository()
 */
class CustomerGroupTableFilterForm extends AbstractType
{
    public const string BLOCK_PREFIX = 'customerGroupTableFilter';

    public const string FIELD_ASSIGNMENT_TYPE = 'assignmentType';

    public const string FIELD_ID_ASSIGNED_ENTITY = 'idAssignedEntity';

    protected const string LABEL_ASSIGNMENT_TYPE = 'Assignment type';

    protected const string PLACEHOLDER_ASSIGNMENT_TYPE = 'Select assignment type';

    protected const string LABEL_ID_ASSIGNED_ENTITY = 'Assigned to';

    protected const string PLACEHOLDER_ID_ASSIGNED_ENTITY = 'Type at least 3 characters';

    protected const string ATTRIBUTE_SUGGEST_URL = 'data-suggest-url';

    protected const int MINIMUM_INPUT_LENGTH = 3;

    public function getBlockPrefix(): string
    {
        return static::BLOCK_PREFIX;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNMENT_TYPE_CHOICES,
            CustomerGroupTableFilterFormDataProvider::OPTION_SUGGEST_URLS,
            CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNED_ENTITY_CHOICES,
            CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNED_ENTITY_SUGGEST_URL,
        ]);

        $resolver->setDefaults([
            'data_class' => CustomerGroupTableFilterTransfer::class,
            'csrf_protection' => false,
            'required' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $this
            ->addAssignmentTypeField($builder, $options)
            ->addIdAssignedEntityField($builder, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addAssignmentTypeField(FormBuilderInterface $builder, array $options)
    {
        $suggestUrls = $options[CustomerGroupTableFilterFormDataProvider::OPTION_SUGGEST_URLS];

        $builder->add(static::FIELD_ASSIGNMENT_TYPE, Select2ComboBoxType::class, [
            'label' => static::LABEL_ASSIGNMENT_TYPE,
            'placeholder' => static::PLACEHOLDER_ASSIGNMENT_TYPE,
            'required' => false,
            'choices' => $options[CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNMENT_TYPE_CHOICES],
            'choice_attr' => fn (string $assignmentType): array => [static::ATTRIBUTE_SUGGEST_URL => $suggestUrls[$assignmentType] ?? ''],
            'attr' => [
                'data-clearable' => true,
                'data-disable-search' => true,
            ],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addIdAssignedEntityField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(static::FIELD_ID_ASSIGNED_ENTITY, Select2ComboBoxType::class, [
            'label' => static::LABEL_ID_ASSIGNED_ENTITY,
            'placeholder' => static::PLACEHOLDER_ID_ASSIGNED_ENTITY,
            'required' => false,
            'choices' => $options[CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNED_ENTITY_CHOICES],
            'attr' => [
                'data-autocomplete-url' => $options[CustomerGroupTableFilterFormDataProvider::OPTION_ASSIGNED_ENTITY_SUGGEST_URL],
                'data-minimum-input-length' => static::MINIMUM_INPUT_LENGTH,
                'data-clearable' => true,
            ],
        ]);

        return $this;
    }
}
