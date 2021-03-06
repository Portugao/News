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

namespace MU\NewsModule\Helper;

use MU\NewsModule\Helper\Base\AbstractControllerHelper;

/**
 * Helper implementation class for controller layer methods.
 */
class ControllerHelper extends AbstractControllerHelper
{
    protected function determineDefaultViewSorting(string $objectType): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $attributes = $request->attributes;
        $route = $attributes->get('_route', '');
        $repository = $this->entityFactory->getRepository($objectType);

        $sort = $request->query->get('sort', '');
        $sortdir = '';
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields())) {
            $sort = $repository->getDefaultSortingField();

            if ('munewsmodule_message_view' === $route) {
                $sorting = 'defaultMessageSorting';
            } else {
                $sorting = 'defaultMessageSortingBackend';
            }
            $defaultSorting = $this->variableApi->get('MUNewsModule', $sorting, 'articledatetime');
            $defaultSortingDirection = $this->variableApi->get('MUNewsModule', 'sortingDirection', 'descending');
            $sortdir = str_replace('ending', '', $defaultSortingDirection);
            if ('articleID' === $defaultSorting) {
                $sort = 'id';
            } elseif ('articledatetime' === $defaultSorting) {
                $sort = 'createdDate';
            } elseif ('articleweight' === $defaultSorting) {
                $sort = 'weight';
            } elseif ('articlestartdate' === $defaultSorting) {
                $sort = 'startDate';
            }

            $request->query->set('sort', $sort);
            // set default sorting in route parameters (e.g. for the pager)
            $routeParams = $request->attributes->get('_route_params');
            $routeParams['sort'] = $sort;
            $request->attributes->set('_route_params', $routeParams);
        }
        if ('' === $sortdir) {
            $sortdir = $request->query->get('sortdir', 'ASC');
            if (false !== mb_strpos($sort, ' DESC')) {
                $sort = str_replace(' DESC', '', $sort);
                $sortdir = 'desc';
            }
        }

        return [$sort, $sortdir];
    }
}
