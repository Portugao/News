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

namespace MU\NewsModule\Form\Type\QuickNavigation\Base;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersModule\Entity\UserEntity;
use MU\NewsModule\Entity\Factory\EntityFactory;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\FeatureActivationHelper;
use MU\NewsModule\Helper\ListEntriesHelper;
use MU\NewsModule\Helper\PermissionHelper;

/**
 * Message quick navigation form type base class.
 */
abstract class AbstractMessageQuickNavType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;

    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

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
        PermissionHelper $permissionHelper,
        EntityDisplayHelper $entityDisplayHelper,
        ListEntriesHelper $listHelper,
        LocaleApiInterface $localeApi,
        FeatureActivationHelper $featureActivationHelper
    ) {
        $this->requestStack = $requestStack;
        $this->entityFactory = $entityFactory;
        $this->permissionHelper = $permissionHelper;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->listHelper = $listHelper;
        $this->localeApi = $localeApi;
        $this->featureActivationHelper = $featureActivationHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('all', HiddenType::class)
            ->add('own', HiddenType::class)
            ->add('tpl', HiddenType::class)
        ;

        if ($this->featureActivationHelper->isEnabled(FeatureActivationHelper::CATEGORIES, 'message')) {
            $this->addCategoriesField($builder, $options);
        }
        $this->addOutgoingRelationshipFields($builder, $options);
        $this->addListFields($builder, $options);
        $this->addUserFields($builder, $options);
        $this->addLocaleFields($builder, $options);
        $this->addSearchField($builder, $options);
        $this->addSortingFields($builder, $options);
        $this->addAmountField($builder, $options);
        $this->addBooleanFields($builder, $options);
        $builder->add('updateview', SubmitType::class, [
            'label' => 'OK',
            'attr' => [
                'class' => 'btn-secondary btn-sm',
            ],
        ]);
    }

    /**
     * Adds a categories field.
     */
    public function addCategoriesField(FormBuilderInterface $builder, array $options = []): void
    {
        $objectType = 'message';
        $entityCategoryClass = 'MU\NewsModule\Entity\\' . ucfirst($objectType) . 'CategoryEntity';
        $builder->add('categories', CategoriesType::class, [
            'label' => 'Categories',
            'empty_data' => [],
            'attr' => [
                'class' => 'form-control-sm category-selector',
                'title' => 'This is an optional filter.',
            ],
            'required' => false,
            'multiple' => true,
            'module' => 'MUNewsModule',
            'entity' => ucfirst($objectType) . 'Entity',
            'entityCategoryClass' => $entityCategoryClass,
            'showRegistryLabels' => true,
        ]);
    }

    /**
     * Adds fields for outgoing relationships.
     */
    public function addOutgoingRelationshipFields(FormBuilderInterface $builder, array $options = []): void
    {
        $mainSearchTerm = '';
        $request = $this->requestStack->getCurrentRequest();
        if ($request->query->has('q')) {
            // remove current search argument from request to avoid filtering related items
            $mainSearchTerm = $request->query->get('q');
            $request->query->remove('q');
        }
        $entityDisplayHelper = $this->entityDisplayHelper;
        $objectType = 'image';
        // select without joins
        $entities = $this->entityFactory->getRepository($objectType)->selectWhere('', '', false);
        $permLevel = ACCESS_READ;
        
        $entities = $this->permissionHelper->filterCollection(
            $objectType,
            $entities,
            $permLevel
        );
        $choices = [];
        foreach ($entities as $entity) {
            $choices[$entity->getId()] = $entity;
        }
        
        $builder->add('images', ChoiceType::class, [
            'choices' => /** @Ignore */$choices,
            'choice_label' => function ($entity) use ($entityDisplayHelper) {
                return $entityDisplayHelper->getFormattedTitle($entity);
            },
            'placeholder' => 'All',
            'required' => false,
            'label' => 'Images',
            'attr' => [
                'class' => 'form-control-sm',
            ],
        ]);
    
        if ('' !== $mainSearchTerm) {
            // readd current search argument
            $request->query->set('q', $mainSearchTerm);
        }
    }

    /**
     * Adds list fields.
     */
    public function addListFields(FormBuilderInterface $builder, array $options = []): void
    {
        $listEntries = $this->listHelper->getEntries('message', 'workflowState');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('workflowState', ChoiceType::class, [
            'label' => 'State',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false,
        ]);
    }

    /**
     * Adds user fields.
     */
    public function addUserFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('approver', EntityType::class, [
            'label' => 'Approver',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            'class' => UserEntity::class,
            'choice_label' => 'uname',
        ]);
    }

    /**
     * Adds locale fields.
     */
    public function addLocaleFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('messageLanguage', LocaleType::class, [
            'label' => 'Message language',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            /** @Ignore */
            'choices' => $this->localeApi->getSupportedLocaleNames(),
        ]);
    }

    /**
     * Adds a search field.
     */
    public function addSearchField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('q', SearchType::class, [
            'label' => 'Search',
            'attr' => [
                'maxlength' => 255,
                'class' => 'form-control-sm',
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds sorting fields.
     */
    public function addSortingFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('sort', ChoiceType::class, [
                'label' => 'Sort by',
                'attr' => [
                    'class' => 'form-control-sm',
                ],
                'choices' => [
                    'Workflow state' => 'workflowState',
                    'Title' => 'title',
                    'Image upload 1' => 'imageUpload1',
                    'Display on index' => 'displayOnIndex',
                    'Creation date' => 'createdDate',
                    'Creator' => 'createdBy',
                    'Update date' => 'updatedDate',
                    'Updater' => 'updatedBy',
                ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('sortdir', ChoiceType::class, [
                'label' => 'Sort direction',
                'empty_data' => 'asc',
                'attr' => [
                    'class' => 'form-control-sm',
                ],
                'choices' => [
                    'Ascending' => 'asc',
                    'Descending' => 'desc',
                ],
                'required' => true,
                'expanded' => false,
            ])
        ;
    }

    /**
     * Adds a page size field.
     */
    public function addAmountField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('num', ChoiceType::class, [
            'label' => 'Page size',
            'empty_data' => 20,
            'attr' => [
                'class' => 'form-control-sm text-right',
            ],
            /** @Ignore */
            'choices' => [
                5 => 5,
                10 => 10,
                15 => 15,
                20 => 20,
                30 => 30,
                50 => 50,
                100 => 100,
            ],
            'required' => false,
            'expanded' => false,
        ]);
    }

    /**
     * Adds boolean fields.
     */
    public function addBooleanFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('displayOnIndex', ChoiceType::class, [
            'label' => 'Display on index',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes',
            ],
        ]);
        $builder->add('allowComments', ChoiceType::class, [
            'label' => 'Allow comments',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes',
            ],
        ]);
        $builder->add('noEndDate', ChoiceType::class, [
            'label' => 'No end date',
            'attr' => [
                'class' => 'form-control-sm',
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'munewsmodule_messagequicknav';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'message',
        ]);
    }
}
