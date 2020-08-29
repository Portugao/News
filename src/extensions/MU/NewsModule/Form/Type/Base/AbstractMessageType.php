<?php

/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 *
 * @see https://homepages-mit-zikula.de
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

declare(strict_types=1);

namespace MU\NewsModule\Form\Type\Base;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use MU\NewsModule\Entity\Factory\EntityFactory;
use MU\NewsModule\Form\Type\Field\TranslationType;
use MU\NewsModule\Form\Type\Field\UploadType;
use Zikula\UsersModule\Form\Type\UserLiveSearchType;
use MU\NewsModule\Entity\MessageEntity;
use MU\NewsModule\Entity\MessageCategoryEntity;
use MU\NewsModule\Helper\CollectionFilterHelper;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\FeatureActivationHelper;
use MU\NewsModule\Helper\ListEntriesHelper;
use MU\NewsModule\Helper\TranslatableHelper;
use MU\NewsModule\Helper\UploadHelper;
use MU\NewsModule\Traits\ModerationFormFieldsTrait;
use MU\NewsModule\Traits\WorkflowFormFieldsTrait;

/**
 * Message editing form type base class.
 */
abstract class AbstractMessageType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    use ModerationFormFieldsTrait;
    use WorkflowFormFieldsTrait;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var CollectionFilterHelper
     */
    protected $collectionFilterHelper;

    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;

    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var TranslatableHelper
     */
    protected $translatableHelper;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    /**
     * @var UploadHelper
     */
    protected $uploadHelper;

    /**
     * @var LocaleApiInterface
     */
    protected $localeApi;

    /**
     * @var FeatureActivationHelper
     */
    protected $featureActivationHelper;

    public function __construct(
        RequestStack $requestStack,
        EntityFactory $entityFactory,
        CollectionFilterHelper $collectionFilterHelper,
        EntityDisplayHelper $entityDisplayHelper,
        VariableApiInterface $variableApi,
        TranslatableHelper $translatableHelper,
        ListEntriesHelper $listHelper,
        UploadHelper $uploadHelper,
        LocaleApiInterface $localeApi,
        FeatureActivationHelper $featureActivationHelper
    ) {
        $this->requestStack = $requestStack;
        $this->entityFactory = $entityFactory;
        $this->collectionFilterHelper = $collectionFilterHelper;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->variableApi = $variableApi;
        $this->translatableHelper = $translatableHelper;
        $this->listHelper = $listHelper;
        $this->uploadHelper = $uploadHelper;
        $this->localeApi = $localeApi;
        $this->featureActivationHelper = $featureActivationHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder, $options);
        if ($this->featureActivationHelper->isEnabled(FeatureActivationHelper::ATTRIBUTES, 'message')) {
            $this->addAttributeFields($builder, $options);
        }
        if ($this->featureActivationHelper->isEnabled(FeatureActivationHelper::CATEGORIES, 'message')) {
            $this->addCategoriesField($builder, $options);
        }
        $this->addOutgoingRelationshipFields($builder, $options);
        $this->addAdditionalNotificationRemarksField($builder, $options);
        $this->addModerationFields($builder, $options);
        $this->addSubmitButtons($builder, $options);
    }

    /**
     * Adds basic entity fields.
     */
    public function addEntityFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'Title:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the title of the message.',
            ],
            'required' => true,
        ]);
        $builder->add('startText', TextareaType::class, [
            'label' => 'Start text:',
            'help' => 'Note: this value must not exceed %length% characters.',
            'help_translation_parameters' => ['%length%' => 20000],
            'empty_data' => '',
            'attr' => [
                'maxlength' => 20000,
                'class' => '',
                'title' => 'Enter the start text of the message.',
            ],
            'required' => true,
        ]);
        $builder->add('mainText', TextareaType::class, [
            'label' => 'Main text:',
            'help' => 'Note: this value must not exceed %length% characters.',
            'help_translation_parameters' => ['%length%' => 100000],
            'empty_data' => '',
            'attr' => [
                'maxlength' => 100000,
                'class' => '',
                'title' => 'Enter the main text of the message.',
            ],
            'required' => false,
        ]);
        $helpText = /** @Translate */'You can input a custom permalink for the message or let this field free to create one automatically.';
        if ('create' !== $options['mode']) {
            $helpText = '';
        }
        $builder->add('slug', TextType::class, [
            'label' => 'Permalink:',
            'required' => 'create' !== $options['mode'],
            'attr' => [
                'maxlength' => 255,
                'class' => 'validate-unique',
                /** @Ignore */
                'title' => $helpText,
            ],
            /** @Ignore */
            'help' => $helpText,
        ]);
        
        if ($this->variableApi->getSystemVar('multilingual') && $this->featureActivationHelper->isEnabled(FeatureActivationHelper::TRANSLATIONS, 'message')) {
            $supportedLanguages = $this->translatableHelper->getSupportedLanguages('message');
            if (is_array($supportedLanguages) && count($supportedLanguages) > 1) {
                $currentLanguage = $this->translatableHelper->getCurrentLanguage();
                $translatableFields = $this->translatableHelper->getTranslatableFields('message');
                $mandatoryFields = $this->translatableHelper->getMandatoryFields('message');
                foreach ($supportedLanguages as $language) {
                    if ($language === $currentLanguage) {
                        continue;
                    }
                    $builder->add('translations' . $language, TranslationType::class, [
                        'fields' => $translatableFields,
                        'mandatory_fields' => $mandatoryFields[$language],
                        'values' => $options['translations'][$language] ?? [],
                    ]);
                }
            }
        }
        $builder->add('imageUpload1', UploadType::class, [
            'label' => 'Image upload 1:',
            'attr' => [
                'accept' => '.' . implode(',.', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload1')),
                'class' => ' validate-upload',
                'title' => 'Enter the image upload 1 of the message.',
            ],
            'required' => false && 'create' === $options['mode'],
            'entity' => $options['entity'],
            'allow_deletion' => true,
            'allowed_extensions' => implode(', ', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload1')),
            'allowed_size' => '200k',
        ]);
        $builder->add('amountOfViews', IntegerType::class, [
            'label' => 'Amount of views:',
            'empty_data' => 0,
            'attr' => [
                'maxlength' => 11,
                'class' => '',
                'title' => 'Enter the amount of views of the message. Only digits are allowed.',
            ],
            'required' => false,
        ]);
        $builder->add('author', TextType::class, [
            'label' => 'Author:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 100,
                'class' => '',
                'title' => 'Enter the author of the message.',
            ],
            'required' => true,
        ]);
        $builder->add('approver', UserLiveSearchType::class, [
            'label' => 'Approver:',
            'empty_data' => null,
            'attr' => [
                'maxlength' => 11,
                'class' => '',
                'title' => 'Enter the approver of the message.',
            ],
            'required' => false,
            'inline_usage' => $options['inline_usage'],
        ]);
        $builder->add('notes', TextareaType::class, [
            'label' => 'Notes:',
            'help' => 'Note: this value must not exceed %length% characters.',
            'help_translation_parameters' => ['%length%' => 2000],
            'empty_data' => '',
            'attr' => [
                'maxlength' => 2000,
                'class' => '',
                'title' => 'Enter the notes of the message.',
            ],
            'required' => false,
        ]);
        $builder->add('displayOnIndex', CheckboxType::class, [
            'label' => 'Display on index:',
            'label_attr' => [
                'class' => 'switch-custom',
            ],
            'attr' => [
                'class' => '',
                'title' => 'display on index ?',
            ],
            'required' => false,
        ]);
        $builder->add('messageLanguage', LocaleType::class, [
            'label' => 'Message language:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 7,
                'class' => '',
                'title' => 'Choose the message language of the message.',
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => /** @Ignore */$this->localeApi->getSupportedLocaleNames(),
            'choice_loader' => null,
        ]);
        $builder->add('allowComments', CheckboxType::class, [
            'label' => 'Allow comments:',
            'label_attr' => [
                'class' => 'switch-custom',
            ],
            'attr' => [
                'class' => '',
                'title' => 'allow comments ?',
            ],
            'required' => false,
        ]);
        $builder->add('imageUpload2', UploadType::class, [
            'label' => 'Image upload 2:',
            'attr' => [
                'accept' => '.' . implode(',.', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload2')),
                'class' => ' validate-upload',
                'title' => 'Enter the image upload 2 of the message.',
            ],
            'required' => false && 'create' === $options['mode'],
            'entity' => $options['entity'],
            'allow_deletion' => true,
            'allowed_extensions' => implode(', ', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload2')),
            'allowed_size' => '200k',
        ]);
        $builder->add('imageUpload3', UploadType::class, [
            'label' => 'Image upload 3:',
            'attr' => [
                'accept' => '.' . implode(',.', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload3')),
                'class' => ' validate-upload',
                'title' => 'Enter the image upload 3 of the message.',
            ],
            'required' => false && 'create' === $options['mode'],
            'entity' => $options['entity'],
            'allow_deletion' => true,
            'allowed_extensions' => implode(', ', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload3')),
            'allowed_size' => '200k',
        ]);
        $builder->add('imageUpload4', UploadType::class, [
            'label' => 'Image upload 4:',
            'attr' => [
                'accept' => '.' . implode(',.', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload4')),
                'class' => ' validate-upload',
                'title' => 'Enter the image upload 4 of the message.',
            ],
            'required' => false && 'create' === $options['mode'],
            'entity' => $options['entity'],
            'allow_deletion' => true,
            'allowed_extensions' => implode(', ', $this->uploadHelper->getAllowedFileExtensions('message', 'imageUpload4')),
            'allowed_size' => '200k',
        ]);
        $builder->add('startDate', DateTimeType::class, [
            'label' => 'Start date:',
            'attr' => [
                'class' => ' validate-daterange-entity-message',
                'title' => 'Enter the start date of the message.',
            ],
            'required' => false,
            'empty_data' => '',
            'with_seconds' => true,
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ]);
        $builder->add('noEndDate', CheckboxType::class, [
            'label' => 'No end date:',
            'label_attr' => [
                'class' => 'switch-custom',
            ],
            'attr' => [
                'class' => '',
                'title' => 'no end date ?',
            ],
            'required' => false,
        ]);
        $builder->add('endDate', DateTimeType::class, [
            'label' => 'End date:',
            'attr' => [
                'class' => ' validate-daterange-entity-message',
                'title' => 'Enter the end date of the message.',
            ],
            'required' => false,
            'empty_data' => '',
            'with_seconds' => true,
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ]);
        $builder->add('weight', IntegerType::class, [
            'label' => 'Weight:',
            'empty_data' => 1,
            'attr' => [
                'maxlength' => 2,
                'class' => '',
                'title' => 'Enter the weight of the message. Only digits are allowed.',
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds fields for attributes.
     */
    public function addAttributeFields(FormBuilderInterface $builder, array $options = []): void
    {
        foreach ($options['attributes'] as $attributeName => $attributeValue) {
            $builder->add('attributes' . $attributeName, TextType::class, [
                'mapped' => false,
                /** @Ignore */
                'label' => $attributeName,
                'attr' => [
                    'maxlength' => 255,
                ],
                'data' => $attributeValue,
                'required' => false,
            ]);
        }
    }

    /**
     * Adds a categories field.
     */
    public function addCategoriesField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('categories', CategoriesType::class, [
            'label' => 'Categories:',
            'empty_data' => [],
            'attr' => [
                'class' => 'category-selector',
            ],
            'required' => false,
            'multiple' => true,
            'module' => 'MUNewsModule',
            'entity' => 'MessageEntity',
            'entityCategoryClass' => MessageCategoryEntity::class,
            'showRegistryLabels' => true,
        ]);
    }

    /**
     * Adds fields for outgoing relationships.
     */
    public function addOutgoingRelationshipFields(FormBuilderInterface $builder, array $options = []): void
    {
        $queryBuilder = function (EntityRepository $er) {
            // select without joins
            return $er->getListQueryBuilder('', '', false);
        };
        $entityDisplayHelper = $this->entityDisplayHelper;
        $choiceLabelClosure = function ($entity) use ($entityDisplayHelper) {
            return $entityDisplayHelper->getFormattedTitle($entity);
        };
        $builder->add('images', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'class' => 'MUNewsModule:ImageEntity',
            'choice_label' => $choiceLabelClosure,
            'by_reference' => false,
            'multiple' => true,
            'expanded' => false,
            'query_builder' => $queryBuilder,
            'required' => false,
            'label' => 'Images',
            'attr' => [
                'title' => 'Choose the images.',
            ],
        ]);
    }

    /**
     * Adds submit buttons.
     */
    public function addSubmitButtons(FormBuilderInterface $builder, array $options = []): void
    {
        foreach ($options['actions'] as $action) {
            $builder->add($action['id'], SubmitType::class, [
                /** @Ignore */
                'label' => $action['title'],
                'icon' => 'delete' === $action['id'] ? 'fa-trash-alt' : '',
                'attr' => [
                    'class' => $action['buttonClass'],
                ],
            ]);
            if ('create' === $options['mode'] && 'submit' === $action['id'] && !$options['inline_usage']) {
                // add additional button to submit item and return to create form
                $builder->add('submitrepeat', SubmitType::class, [
                    'label' => 'Submit and repeat',
                    'icon' => 'fa-repeat',
                    'attr' => [
                        'class' => $action['buttonClass'],
                    ],
                ]);
            }
        }
        $builder->add('reset', ResetType::class, [
            'label' => 'Reset',
            'icon' => 'fa-sync',
            'attr' => [
                'formnovalidate' => 'formnovalidate',
            ],
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => 'Cancel',
            'validate' => false,
            'icon' => 'fa-times',
            'attr' => [
                'formnovalidate' => 'formnovalidate',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'munewsmodule_message';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                // define class for underlying data (required for embedding forms)
                'data_class' => MessageEntity::class,
                'translation_domain' => 'message',
                'empty_data' => function (FormInterface $form) {
                    return $this->entityFactory->createMessage();
                },
                'error_mapping' => [
                    'isApproverUserValid' => 'approver',
                    'imageUpload1' => 'imageUpload1.imageUpload1',
                    'imageUpload2' => 'imageUpload2.imageUpload2',
                    'imageUpload3' => 'imageUpload3.imageUpload3',
                    'imageUpload4' => 'imageUpload4.imageUpload4',
                    'isStartDateBeforeEndDate' => 'startDate',
                ],
                'mode' => 'create',
                'attributes' => [],
                'is_moderator' => false,
                'is_creator' => false,
                'actions' => [],
                'has_moderate_permission' => false,
                'allow_moderation_specific_creator' => false,
                'allow_moderation_specific_creation_date' => false,
                'translations' => [],
                'filter_by_ownership' => true,
                'inline_usage' => false,
            ])
            ->setRequired(['entity', 'mode', 'actions'])
            ->setAllowedTypes('mode', 'string')
            ->setAllowedTypes('attributes', 'array')
            ->setAllowedTypes('is_moderator', 'bool')
            ->setAllowedTypes('is_creator', 'bool')
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('has_moderate_permission', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creator', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creation_date', 'bool')
            ->setAllowedTypes('translations', 'array')
            ->setAllowedTypes('filter_by_ownership', 'bool')
            ->setAllowedTypes('inline_usage', 'bool')
            ->setAllowedValues('mode', ['create', 'edit'])
        ;
    }
}
