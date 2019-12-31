<?php

/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 * @see https://homepages-mit-zikula.de
 * @see https://ziku.la
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace MU\NewsModule\Menu\Base;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use MU\NewsModule\Entity\MessageEntity;
use MU\NewsModule\Entity\ImageEntity;
use MU\NewsModule\NewsEvents;
use MU\NewsModule\Event\ConfigureItemActionsMenuEvent;
use MU\NewsModule\Event\ConfigureViewActionsMenuEvent;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\ModelHelper;
use MU\NewsModule\Helper\PermissionHelper;

/**
 * Menu builder base class.
 */
class AbstractMenuBuilder
{
    use TranslatorTrait;
    
    /**
     * @var FactoryInterface
     */
    protected $factory;
    
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;
    
    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;
    
    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var ModelHelper
     */
    protected $modelHelper;
    
    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        PermissionHelper $permissionHelper,
        EntityDisplayHelper $entityDisplayHelper,
        CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        ModelHelper $modelHelper
    ) {
        $this->setTranslator($translator);
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->permissionHelper = $permissionHelper;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->currentUserApi = $currentUserApi;
        $this->variableApi = $variableApi;
        $this->modelHelper = $modelHelper;
    }
    
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Builds the item actions menu.
     *
     * @param array $options List of additional options
     *
     * @return ItemInterface The assembled menu
     */
    public function createItemActionsMenu(array $options = [])
    {
        $menu = $this->factory->createItem('itemActions');
        if (!isset($options['entity'], $options['area'], $options['context'])) {
            return $menu;
        }
    
        $entity = $options['entity'];
        $routeArea = $options['area'];
        $context = $options['context'];
        $menu->setChildrenAttribute('class', 'list-inline item-actions');
    
        $this->eventDispatcher->dispatch(
            NewsEvents::MENU_ITEMACTIONS_PRE_CONFIGURE,
            new ConfigureItemActionsMenuEvent($this->factory, $menu, $options)
        );
    
        $currentUserId = $this->currentUserApi->isLoggedIn()
            ? $this->currentUserApi->get('uid')
            : UsersConstant::USER_ID_ANONYMOUS
        ;
        if ($entity instanceof MessageEntity) {
            $routePrefix = 'munewsmodule_message_';
            $isOwner = 0 < $currentUserId
                && null !== $entity->getCreatedBy()
                && $currentUserId === $entity->getCreatedBy()->getUid()
            ;
            
            if ('admin' === $routeArea) {
                $title = $this->__('Preview', 'munewsmodule');
                $previewRouteParameters = $entity->createUrlArgs();
                $previewRouteParameters['preview'] = 1;
                $menu->addChild($title, [
                    'route' => $routePrefix . 'display',
                    'routeParameters' => $previewRouteParameters
                ]);
                $menu[$title]->setLinkAttribute('target', '_blank');
                $menu[$title]->setLinkAttribute(
                    'title',
                    $this->__('Open preview page', 'munewsmodule')
                );
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-search-plus');
            }
            if ('display' !== $context) {
                $title = $this->__('Details', 'munewsmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $entityTitle = $this->entityDisplayHelper->getFormattedTitle($entity);
                $menu[$title]->setLinkAttribute(
                    'title',
                    str_replace('"', '', $entityTitle)
                );
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-eye');
            }
            if ($this->permissionHelper->mayEdit($entity)) {
                $title = $this->__('Edit', 'munewsmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'edit',
                    'routeParameters' => $entity->createUrlArgs(true)
                ]);
                $menu[$title]->setLinkAttribute(
                    'title',
                    $this->__('Edit this message', 'munewsmodule')
                );
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-pencil-square-o');
                $title = $this->__('Reuse', 'munewsmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'edit',
                    'routeParameters' => ['astemplate' => $entity->getKey()]
                ]);
                $menu[$title]->setLinkAttribute(
                    'title',
                    $this->__('Reuse for new message', 'munewsmodule')
                );
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-files-o');
            }
            if ($this->permissionHelper->mayDelete($entity)) {
                $title = $this->__('Delete', 'munewsmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'delete',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute(
                    'title',
                    $this->__('Delete this message', 'munewsmodule')
                );
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-danger');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-trash-o');
            }
            if ('display' === $context) {
                $title = $this->__('Messages list', 'munewsmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view'
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                if ('display' === $context) {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-reply');
            }
        }
        if ($entity instanceof ImageEntity) {
            $routePrefix = 'munewsmodule_image_';
            $isOwner = 0 < $currentUserId
                && null !== $entity->getCreatedBy()
                && $currentUserId === $entity->getCreatedBy()->getUid()
            ;
        }
    
        $this->eventDispatcher->dispatch(
            NewsEvents::MENU_ITEMACTIONS_POST_CONFIGURE,
            new ConfigureItemActionsMenuEvent($this->factory, $menu, $options)
        );
    
        return $menu;
    }
    
    /**
     * Builds the view actions menu.
     *
     * @param array $options List of additional options
     *
     * @return ItemInterface The assembled menu
     */
    public function createViewActionsMenu(array $options = [])
    {
        $menu = $this->factory->createItem('viewActions');
        if (!isset($options['objectType'], $options['area'])) {
            return $menu;
        }
    
        $objectType = $options['objectType'];
        $routeArea = $options['area'];
        $menu->setChildrenAttribute('class', 'list-inline view-actions');
    
        $this->eventDispatcher->dispatch(
            NewsEvents::MENU_VIEWACTIONS_PRE_CONFIGURE,
            new ConfigureViewActionsMenuEvent($this->factory, $menu, $options)
        );
    
        $query = $this->requestStack->getMasterRequest()->query;
        $currentTemplate = $query->getAlnum('tpl', '');
        if ('message' === $objectType) {
            $routePrefix = 'munewsmodule_message_';
            if (!in_array($currentTemplate, [])) {
                $canBeCreated = $this->modelHelper->canBeCreated($objectType);
                if ($canBeCreated) {
                    if ($this->permissionHelper->hasComponentPermission($objectType, ACCESS_COMMENT)) {
                        $title = $this->__('Create message', 'munewsmodule');
                        $menu->addChild($title, [
                            'route' => $routePrefix . $routeArea . 'edit'
                        ]);
                        $menu[$title]->setLinkAttribute('title', $title);
                        $menu[$title]->setAttribute('icon', 'fa fa-plus');
                    }
                }
                $routeParameters = $query->all();
                if (1 === $query->getInt('own')) {
                    $routeParameters['own'] = 1;
                } else {
                    unset($routeParameters['own']);
                }
                if (1 === $query->getInt('all')) {
                    unset($routeParameters['all']);
                    $title = $this->__('Back to paginated view', 'munewsmodule');
                } else {
                    $routeParameters['all'] = 1;
                    $title = $this->__('Show all entries', 'munewsmodule');
                }
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view',
                    'routeParameters' => $routeParameters
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                $menu[$title]->setAttribute('icon', 'fa fa-table');
                if ($this->permissionHelper->hasComponentPermission($objectType, ACCESS_COMMENT)) {
                    $routeParameters = $query->all();
                    if (1 === $query->getInt('own')) {
                        unset($routeParameters['own']);
                        $title = $this->__('Show also entries from other users', 'munewsmodule');
                        $icon = 'users';
                    } else {
                        $routeParameters['own'] = 1;
                        $title = $this->__('Show only own entries', 'munewsmodule');
                        $icon = 'user';
                    }
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'view',
                        'routeParameters' => $routeParameters
                    ]);
                    $menu[$title]->setLinkAttribute('title', $title);
                    $menu[$title]->setAttribute('icon', 'fa fa-' . $icon);
                }
            }
        }
    
        $this->eventDispatcher->dispatch(
            NewsEvents::MENU_VIEWACTIONS_POST_CONFIGURE,
            new ConfigureViewActionsMenuEvent($this->factory, $menu, $options)
        );
    
        return $menu;
    }
}
