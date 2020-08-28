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

namespace MU\NewsModule\Controller\Base;

use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use MU\NewsModule\Entity\MessageEntity;
use MU\NewsModule\Entity\Factory\EntityFactory;
use MU\NewsModule\Form\Handler\Message\EditHandler;
use MU\NewsModule\Helper\ControllerHelper;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\HookHelper;
use MU\NewsModule\Helper\PermissionHelper;
use MU\NewsModule\Helper\ViewHelper;
use MU\NewsModule\Helper\WorkflowHelper;

/**
 * Message controller base class.
 */
abstract class AbstractMessageController extends AbstractController
{
    
    /**
     * This is the default action handling the index area called without defining arguments.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    protected function indexInternal(
        Request $request,
        PermissionHelper $permissionHelper,
        bool $isAdmin = false
    ): Response {
        $objectType = 'message';
        // permission check
        $permLevel = $isAdmin ? ACCESS_ADMIN : ACCESS_OVERVIEW;
        if (!$permissionHelper->hasComponentPermission($objectType, $permLevel)) {
            throw new AccessDeniedException();
        }
        
        $templateParameters = [
            'routeArea' => $isAdmin ? 'admin' : '',
        ];
        
        return $this->redirectToRoute('munewsmodule_message_' . $templateParameters['routeArea'] . 'view');
    }
    
    /**
     * This action provides an item list overview.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws Exception
     */
    protected function viewInternal(
        Request $request,
        RouterInterface $router,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        string $sort,
        string $sortdir,
        int $page,
        int $num,
        bool $isAdmin = false
    ): Response {
        $objectType = 'message';
        // permission check
        $permLevel = $isAdmin ? ACCESS_ADMIN : ACCESS_READ;
        if (!$permissionHelper->hasComponentPermission($objectType, $permLevel)) {
            throw new AccessDeniedException();
        }
        
        $templateParameters = [
            'routeArea' => $isAdmin ? 'admin' : '',
        ];
        
        $request->query->set('sort', $sort);
        $request->query->set('sortdir', $sortdir);
        $request->query->set('page', $page);
        
        $routeName = 'munewsmodule_message_' . ($isAdmin ? 'admin' : '') . 'view';
        $sortableColumns = new SortableColumns($router, $routeName, 'sort', 'sortdir');
        
        $sortableColumns->addColumns([
            new Column('workflowState'),
            new Column('title'),
            new Column('imageUpload1'),
            new Column('displayOnIndex'),
            new Column('createdBy'),
            new Column('createdDate'),
            new Column('updatedBy'),
            new Column('updatedDate'),
        ]);
        
        $templateParameters = $controllerHelper->processViewActionParameters(
            $objectType,
            $sortableColumns,
            $templateParameters,
            true
        );
        
        // filter by permissions
        $templateParameters['items'] = $permissionHelper->filterCollection(
            $objectType,
            $templateParameters['items'],
            $permLevel
        );
        
        // fetch and return the appropriate template
        return $viewHelper->processTemplate($objectType, 'view', $templateParameters);
    }
    
    /**
     * This action provides a item detail view.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown if message to be displayed isn't found
     */
    protected function displayInternal(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EntityFactory $entityFactory,
        EntityDisplayHelper $entityDisplayHelper,
        ?MessageEntity $message = null,
        string $slug = '',
        bool $isAdmin = false
    ): Response {
        if (null === $message) {
            $message = $entityFactory->getRepository('message')->selectBySlug($slug);
        }
        if (null === $message) {
            throw new NotFoundHttpException(
                $this->trans(
                    'No such message found.',
                    [],
                    'message'
                )
            );
        }
        
        $objectType = 'message';
        // permission check
        $permLevel = $isAdmin ? ACCESS_ADMIN : ACCESS_READ;
        if (!$permissionHelper->hasEntityPermission($message, $permLevel)) {
            throw new AccessDeniedException();
        }
        
        if (
            'approved' !== $message->getWorkflowState()
            && !$permissionHelper->hasEntityPermission($message, ACCESS_EDIT)
        ) {
            throw new AccessDeniedException();
        }
        
        $templateParameters = [
            'routeArea' => $isAdmin ? 'admin' : '',
            $objectType => $message,
        ];
        
        $templateParameters = $controllerHelper->processDisplayActionParameters(
            $objectType,
            $templateParameters,
            $message->supportsHookSubscribers()
        );
        
        // fetch and return the appropriate template
        $response = $viewHelper->processTemplate($objectType, 'display', $templateParameters);
        
        if ('ics' === $request->getRequestFormat()) {
            $fileName = $objectType . '_' .
                (property_exists($message, 'slug')
                    ? $message['slug']
                    : $entityDisplayHelper->getFormattedTitle($message)
                ) . '.ics'
            ;
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName);
        }
        
        return $response;
    }
    
    /**
     * This action provides a handling of edit requests.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws RuntimeException Thrown if another critical error occurs (e.g. workflow actions not available)
     * @throws Exception
     */
    protected function editInternal(
        Request $request,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EditHandler $formHandler,
        bool $isAdmin = false
    ): Response {
        $objectType = 'message';
        // permission check
        $permLevel = $isAdmin ? ACCESS_ADMIN : ACCESS_COMMENT;
        if (!$permissionHelper->hasComponentPermission($objectType, $permLevel)) {
            throw new AccessDeniedException();
        }
        
        $templateParameters = [
            'routeArea' => $isAdmin ? 'admin' : '',
        ];
        
        // delegate form processing to the form handler
        $result = $formHandler->processForm($templateParameters);
        if ($result instanceof RedirectResponse) {
            return $result;
        }
        
        $templateParameters = $formHandler->getTemplateParameters();
        
        $templateParameters = $controllerHelper->processEditActionParameters(
            $objectType,
            $templateParameters,
            $templateParameters['message']->supportsHookSubscribers()
        );
        
        // fetch and return the appropriate template
        return $viewHelper->processTemplate($objectType, 'edit', $templateParameters);
    }
    
    /**
     * This action provides a handling of simple delete requests.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown if message to be deleted isn't found
     * @throws RuntimeException Thrown if another critical error occurs (e.g. workflow actions not available)
     */
    protected function deleteInternal(
        Request $request,
        LoggerInterface $logger,
        PermissionHelper $permissionHelper,
        ControllerHelper $controllerHelper,
        ViewHelper $viewHelper,
        EntityFactory $entityFactory,
        CurrentUserApiInterface $currentUserApi,
        WorkflowHelper $workflowHelper,
        HookHelper $hookHelper,
        string $slug,
        bool $isAdmin = false
    ): Response {
        $message = $entityFactory->getRepository('message')->selectBySlug($slug);
        if (null === $message) {
            throw new NotFoundHttpException(
                $this->trans(
                    'No such message found.',
                    [],
                    'message'
                )
            );
        }
        
        $objectType = 'message';
        // permission check
        $permLevel = $isAdmin ? ACCESS_ADMIN : ACCESS_DELETE;
        if (!$permissionHelper->hasEntityPermission($message, $permLevel)) {
            throw new AccessDeniedException();
        }
        
        $logArgs = ['app' => 'MUNewsModule', 'user' => $currentUserApi->get('uname'), 'entity' => 'message', 'id' => $message->getKey()];
        
        // determine available workflow actions
        $actions = $workflowHelper->getActionsForObject($message);
        if (false === $actions || !is_array($actions)) {
            $this->addFlash('error', 'Error! Could not determine workflow actions.');
            $logger->error('{app}: User {user} tried to delete the {entity} with id {id}, but failed to determine available workflow actions.', $logArgs);
            throw new RuntimeException($this->trans('Error! Could not determine workflow actions.'));
        }
        
        // redirect to the list of messages
        $redirectRoute = 'munewsmodule_message_' . ($isAdmin ? 'admin' : '') . 'view';
        
        // check whether deletion is allowed
        $deleteActionId = 'delete';
        $deleteAllowed = false;
        foreach ($actions as $actionId => $action) {
            if ($actionId != $deleteActionId) {
                continue;
            }
            $deleteAllowed = true;
            break;
        }
        if (!$deleteAllowed) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Error! It is not allowed to delete this message.',
                    [],
                    'message'
                )
            );
            $logger->error('{app}: User {user} tried to delete the {entity} with id {id}, but this action was not allowed.', $logArgs);
        
            return $this->redirectToRoute($redirectRoute);
        }
        
        $form = $this->createForm(DeletionType::class, $message);
        if ($message->supportsHookSubscribers()) {
            // call form aware display hooks
            $formHook = $hookHelper->callFormDisplayHooks($form, $message, FormAwareCategory::TYPE_DELETE);
        }
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                if ($message->supportsHookSubscribers()) {
                    // let any ui hooks perform additional validation actions
                    $validationErrors = $hookHelper->callValidationHooks($message, UiHooksCategory::TYPE_VALIDATE_DELETE);
                    if (0 < count($validationErrors)) {
                        foreach ($validationErrors as $message) {
                            $this->addFlash('error', $message);
                        }
                    } else {
                        // execute the workflow action
                        $success = $workflowHelper->executeAction($message, $deleteActionId);
                        if ($success) {
                            $this->addFlash(
                                'status',
                                $this->trans(
                                    'Done! Message deleted.',
                                    [],
                                    'message'
                                )
                            );
                            $logger->notice('{app}: User {user} deleted the {entity} with id {id}.', $logArgs);
                        }
                        
                        if ($message->supportsHookSubscribers()) {
                            // call form aware processing hooks
                            $hookHelper->callFormProcessHooks($form, $message, FormAwareCategory::TYPE_PROCESS_DELETE);
                        
                            // let any ui hooks know that we have deleted the message
                            $hookHelper->callProcessHooks($message, UiHooksCategory::TYPE_PROCESS_DELETE);
                        }
                        
                        return $this->redirectToRoute($redirectRoute);
                    }
                } else {
                    // execute the workflow action
                    $success = $workflowHelper->executeAction($message, $deleteActionId);
                    if ($success) {
                        $this->addFlash(
                            'status',
                            $this->trans(
                                'Done! Message deleted.',
                                [],
                                'message'
                            )
                        );
                        $logger->notice('{app}: User {user} deleted the {entity} with id {id}.', $logArgs);
                    }
                    
                    if ($message->supportsHookSubscribers()) {
                        // call form aware processing hooks
                        $hookHelper->callFormProcessHooks($form, $message, FormAwareCategory::TYPE_PROCESS_DELETE);
                    
                        // let any ui hooks know that we have deleted the message
                        $hookHelper->callProcessHooks($message, UiHooksCategory::TYPE_PROCESS_DELETE);
                    }
                    
                    return $this->redirectToRoute($redirectRoute);
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
        
                return $this->redirectToRoute($redirectRoute);
            }
        }
        
        $templateParameters = [
            'routeArea' => $isAdmin ? 'admin' : '',
            'deleteForm' => $form->createView(),
            $objectType => $message,
        ];
        if ($message->supportsHookSubscribers()) {
            $templateParameters['formHookTemplates'] = $formHook->getTemplates();
        }
        
        $templateParameters = $controllerHelper->processDeleteActionParameters(
            $objectType,
            $templateParameters,
            $message->supportsHookSubscribers()
        );
        
        // fetch and return the appropriate template
        return $viewHelper->processTemplate($objectType, 'delete', $templateParameters);
    }
    
    /**
     * Process status changes for multiple items.
     *
     * This function processes the items selected in the admin view page.
     * Multiple items may have their state changed or be deleted.
     *
     * @throws RuntimeException Thrown if executing the workflow action fails
     */
    protected function handleSelectedEntriesActionInternal(
        Request $request,
        LoggerInterface $logger,
        EntityFactory $entityFactory,
        WorkflowHelper $workflowHelper,
        HookHelper $hookHelper,
        CurrentUserApiInterface $currentUserApi,
        bool $isAdmin = false
    ): RedirectResponse {
        $objectType = 'message';
        
        // get parameters
        $action = $request->request->get('action');
        $items = $request->request->get('items');
        if (!is_array($items) || !count($items)) {
            return $this->redirectToRoute('munewsmodule_message_' . ($isAdmin ? 'admin' : '') . 'index');
        }
        
        $action = mb_strtolower($action);
        
        $repository = $entityFactory->getRepository($objectType);
        $userName = $currentUserApi->get('uname');
        
        // process each item
        foreach ($items as $itemId) {
            // check if item exists, and get record instance
            $entity = $repository->selectById($itemId, false);
            if (null === $entity) {
                continue;
            }
        
            // check if $action can be applied to this entity (may depend on it's current workflow state)
            $allowedActions = $workflowHelper->getActionsForObject($entity);
            $actionIds = array_keys($allowedActions);
            if (!in_array($action, $actionIds, true)) {
                // action not allowed, skip this object
                continue;
            }
        
            if ($entity->supportsHookSubscribers()) {
                // let any ui hooks perform additional validation actions
                $hookType = 'delete' === $action
                    ? UiHooksCategory::TYPE_VALIDATE_DELETE
                    : UiHooksCategory::TYPE_VALIDATE_EDIT
                ;
                $validationErrors = $hookHelper->callValidationHooks($entity, $hookType);
                if (count($validationErrors) > 0) {
                    foreach ($validationErrors as $message) {
                        $this->addFlash('error', $message);
                    }
                    continue;
                }
            }
        
            $success = false;
            try {
                // execute the workflow action
                $success = $workflowHelper->executeAction($entity, $action);
            } catch (Exception $exception) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'Sorry, but an error occured during the %action% action.',
                        ['%action%' => $action]
                    ) . '  ' . $exception->getMessage()
                );
                $logger->error(
                    '{app}: User {user} tried to execute the {action} workflow action for the {entity} with id {id},'
                        . ' but failed. Error details: {errorMessage}.',
                    [
                        'app' => 'MUNewsModule',
                        'user' => $userName,
                        'action' => $action,
                        'entity' => 'message',
                        'id' => $itemId,
                        'errorMessage' => $exception->getMessage(),
                    ]
                );
            }
        
            if (!$success) {
                continue;
            }
        
            if ('delete' === $action) {
                $this->addFlash(
                    'status',
                    $this->trans(
                        'Done! Message deleted.',
                        [],
                        'message'
                    )
                );
                $logger->notice(
                    '{app}: User {user} deleted the {entity} with id {id}.',
                    [
                        'app' => 'MUNewsModule',
                        'user' => $userName,
                        'entity' => 'message',
                        'id' => $itemId,
                    ]
                );
            } else {
                $this->addFlash(
                    'status',
                    $this->trans(
                        'Done! Message updated.',
                        [],
                        'message'
                    )
                );
                $logger->notice(
                    '{app}: User {user} executed the {action} workflow action for the {entity} with id {id}.',
                    [
                        'app' => 'MUNewsModule',
                        'user' => $userName,
                        'action' => $action,
                        'entity' => 'message',
                        'id' => $itemId,
                    ]
                );
            }
        
            if ($entity->supportsHookSubscribers()) {
                // let any ui hooks know that we have updated or deleted an item
                $hookType = 'delete' === $action
                    ? UiHooksCategory::TYPE_PROCESS_DELETE
                    : UiHooksCategory::TYPE_PROCESS_EDIT
                ;
                $url = null;
                if ('delete' !== $action) {
                    $urlArgs = $entity->createUrlArgs();
                    $urlArgs['_locale'] = $request->getLocale();
                    $url = new RouteUrl('munewsmodule_message_display', $urlArgs);
                }
                $hookHelper->callProcessHooks($entity, $hookType, $url);
            }
        }
        
        return $this->redirectToRoute('munewsmodule_message_' . ($isAdmin ? 'admin' : '') . 'index');
    }
    
    /**
     * This method cares for a redirect within an inline frame.
     */
    public function handleInlineRedirectAction(
        EntityFactory $entityFactory,
        EntityDisplayHelper $entityDisplayHelper,
        string $idPrefix,
        string $commandName,
        int $id = 0
    ): Response {
        if (empty($idPrefix)) {
            return false;
        }
        
        $formattedTitle = '';
        $searchTerm = '';
        if (!empty($id)) {
            $repository = $entityFactory->getRepository('message');
            $message = null;
            if (!is_numeric($id)) {
                $message = $repository->selectBySlug($id);
            }
            if (null === $message && is_numeric($id)) {
                $message = $repository->selectById($id);
            }
            if (null !== $message) {
                $formattedTitle = $entityDisplayHelper->getFormattedTitle($message);
                $searchTerm = $message->getTitle();
            }
        }
        
        $templateParameters = [
            'itemId' => $id,
            'formattedTitle' => $formattedTitle,
            'searchTerm' => $searchTerm,
            'idPrefix' => $idPrefix,
            'commandName' => $commandName,
        ];
        
        return new PlainResponse(
            $this->renderView('@MUNewsModule/Message/inlineRedirectHandler.html.twig', $templateParameters)
        );
    }
    
}
