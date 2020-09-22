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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use MU\NewsModule\Entity\Factory\EntityFactory;
use MU\NewsModule\Helper\ControllerHelper;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\PermissionHelper;

/**
 * Ajax controller base class.
 */
abstract class AbstractAjaxController extends AbstractController
{
    
    /**
     * Retrieve item list for finder selections, for example used in Scribite editor plug-ins.
     */
    public function getItemListFinder(
        Request $request,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper,
        EntityFactory $entityFactory,
        EntityDisplayHelper $entityDisplayHelper
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }
        
        if (!$this->hasPermission('MUNewsModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $objectType = $request->query->getAlnum('ot', 'message');
        $contextArgs = ['controller' => 'ajax', 'action' => 'getItemListFinder'];
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $contextArgs), true)) {
            $objectType = $controllerHelper->getDefaultObjectType('controllerAction', $contextArgs);
        }
        
        $repository = $entityFactory->getRepository($objectType);
        $descriptionFieldName = $entityDisplayHelper->getDescriptionFieldName($objectType);
        
        $sort = $request->query->getAlnum('sort');
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields(), true)) {
            $sort = $repository->getDefaultSortingField();
        }
        
        $sdir = mb_strtolower($request->query->getAlpha('sortdir'));
        if ('asc' !== $sdir && 'desc' !== $sdir) {
            $sdir = 'asc';
        }
        
        $where = ''; // filters are processed inside the repository class
        $searchTerm = $request->query->get('q');
        $sortParam = $sort . ' ' . $sdir;
        
        $entities = [];
        if ('' !== $searchTerm) {
            list($entities, $totalAmount) = $repository->selectSearch($searchTerm, [], $sortParam, 1, 50, false);
        } else {
            $entities = $repository->selectWhere($where, $sortParam);
        }
        
        $slimItems = [];
        foreach ($entities as $item) {
            if (!$permissionHelper->mayRead($item)) {
                continue;
            }
            $itemId = $item->getKey();
            $slimItems[] = $this->prepareSlimItem(
                $controllerHelper,
                $repository,
                $entityDisplayHelper,
                $item,
                $itemId,
                $descriptionFieldName
            );
        }
        
        // return response
        return $this->json($slimItems);
    }
    
    /**
     * Builds and returns a slim data array from a given entity.
     */
    protected function prepareSlimItem(
        ControllerHelper $controllerHelper,
        EntityRepository $repository,
        EntityDisplayHelper $entityDisplayHelper,
        $item,
        string $itemId,
        string $descriptionField
    ): array {
        $objectType = $item->get_objectType();
        $previewParameters = [
            $objectType => $item,
        ];
        $contextArgs = ['controller' => $objectType, 'action' => 'display'];
        $previewParameters = $controllerHelper->addTemplateParameters(
            $objectType,
            $previewParameters,
            'controllerAction',
            $contextArgs
        );
    
        $previewInfo = $this->renderView(
            '@MUNewsModule/External/' . ucfirst($objectType) . '/info.html.twig',
            $previewParameters
        );
        $previewInfo = base64_encode($previewInfo);
    
        $title = $entityDisplayHelper->getFormattedTitle($item);
        $description = '' !== $descriptionField ? $item[$descriptionField] : '';
    
        return [
            'id' => $itemId,
            'title' => str_replace('&amp;', '&', $title),
            'description' => $description,
            'previewInfo' => $previewInfo,
        ];
    }
    
    /**
     * Checks whether a field value is a duplicate or not.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function checkForDuplicate(
        Request $request,
        ControllerHelper $controllerHelper,
        EntityFactory $entityFactory
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }
        
        if (!$this->hasPermission('MUNewsModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $objectType = $request->query->getAlnum('ot', 'message');
        $contextArgs = ['controller' => 'ajax', 'action' => 'checkForDuplicate'];
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $contextArgs), true)) {
            $objectType = $controllerHelper->getDefaultObjectType('controllerAction', $contextArgs);
        }
        
        $fieldName = $request->query->getAlnum('fn');
        $value = $request->query->get('v');
        
        if (empty($fieldName) || empty($value)) {
            return $this->json($this->trans('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // check if the given field is existing and unique
        $uniqueFields = [];
        switch ($objectType) {
            case 'message':
                $uniqueFields = ['slug'];
                break;
        }
        if (!count($uniqueFields) || !in_array($fieldName, $uniqueFields, true)) {
            return $this->json($this->trans('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $exclude = $request->query->getInt('ex');
        
        $result = false;
        switch ($objectType) {
            case 'message':
                $repository = $entityFactory->getRepository($objectType);
                switch ($fieldName) {
                    case 'slug':
                        $entity = $repository->selectBySlug($value, false, false, $exclude);
                        $result = null !== $entity && isset($entity['slug']);
                        break;
                }
                break;
        }
        
        // return response
        return $this->json(['isDuplicate' => $result]);
    }
    
    /**
     * Changes a given flag (boolean field) by switching between true and false.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function toggleFlag(
        Request $request,
        LoggerInterface $logger,
        EntityFactory $entityFactory,
        CurrentUserApiInterface $currentUserApi
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }
        
        if (!$this->hasPermission('MUNewsModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $objectType = $request->request->getAlnum('ot', 'message');
        $field = $request->request->getAlnum('field');
        $id = $request->request->getInt('id');
        
        if (
            0 === $id
            || ('message' !== $objectType)
            || ('message' === $objectType && !in_array($field, ['displayOnIndex', 'allowComments', 'noEndDate'], true))
        ) {
            return $this->json($this->trans('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // select data from data source
        $repository = $entityFactory->getRepository($objectType);
        $entity = $repository->selectById($id, false);
        if (null === $entity) {
            return $this->json($this->trans('No such item.'), JsonResponse::HTTP_NOT_FOUND);
        }
        
        // toggle the flag
        $entity[$field] = !$entity[$field];
        
        // save entity back to database
        $entityFactory->getEntityManager()->flush();
        
        $logArgs = [
            'app' => 'MUNewsModule',
            'user' => $currentUserApi->get('uname'),
            'field' => $field,
            'entity' => $objectType,
            'id' => $id,
        ];
        $logger->notice('{app}: User {user} toggled the {field} flag the {entity} with id {id}.', $logArgs);
        
        // return response
        return $this->json([
            'id' => $id,
            'state' => $entity[$field],
            'message' => $this->trans('The setting has been successfully changed.'),
        ]);
    }
    
    /**
     * Updates the sort positions for a given list of entities.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function updateSortPositions(
        Request $request,
        EntityFactory $entityFactory
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json($this->trans('Only ajax access is allowed!'), Response::HTTP_BAD_REQUEST);
        }
        
        if (!$this->hasPermission('MUNewsModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $objectType = $request->request->getAlnum('ot', 'message');
        $itemIds = $request->request->get('identifiers', []);
        $min = $request->request->getInt('min');
        $max = $request->request->getInt('max');
        
        if (!is_array($itemIds) || 2 > count($itemIds) || 1 > $max || $max <= $min) {
            return $this->json($this->trans('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $repository = $entityFactory->getRepository($objectType);
        $sortableFieldMap = [
            'image' => 'sortNumber',
        ];
        
        $sortFieldSetter = 'set' . ucfirst($sortableFieldMap[$objectType]);
        $sortCounter = $min;
        
        // update sort values
        foreach ($itemIds as $itemId) {
            if (empty($itemId) || !is_numeric($itemId)) {
                continue;
            }
            $entity = $repository->selectById($itemId);
            $entity->$sortFieldSetter($sortCounter);
            ++$sortCounter;
        }
        
        // save entities back to database
        $entityFactory->getEntityManager()->flush();
        
        // return response
        return $this->json([
            'message' => $this->trans('The setting has been successfully changed.'),
        ]);
    }
}
