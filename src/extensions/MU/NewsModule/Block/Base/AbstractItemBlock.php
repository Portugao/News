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

namespace MU\NewsModule\Block\Base;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Zikula\BlocksModule\AbstractBlockHandler;
use MU\NewsModule\Block\Form\Type\ItemBlockType;
use MU\NewsModule\Helper\ControllerHelper;

/**
 * Generic item detail block base class.
 */
abstract class AbstractItemBlock extends AbstractBlockHandler
{
    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;
    
    /**
     * @var FragmentHandler
     */
    protected $fragmentHandler;
    
    public function getType(): string
    {
        return $this->trans('News detail');
    }
    
    public function display(array $properties = []): string
    {
        // only show block content if the user has the required permissions
        if (!$this->hasPermission('MUNewsModule:ItemBlock:', $properties['title'] . '::', ACCESS_OVERVIEW)) {
            return '';
        }
    
        if (null === $properties['id'] || empty($properties['id'])) {
            return '';
        }
    
        $contextArgs = ['name' => 'detail'];
        $allowedObjectTypes = $this->controllerHelper->getObjectTypes('block', $contextArgs);
        if (
            !isset($properties['objectType'])
            || !in_array($properties['objectType'], $allowedObjectTypes, true)
        ) {
            $properties['objectType'] = $this->controllerHelper->getDefaultObjectType('block', $contextArgs);
        }
    
        $controllerReference = new ControllerReference(
            'MU\NewsModule\Controller\ExternalController::display',
            $this->getDisplayArguments($properties),
            [
                'template' => $properties['customTemplate']
            ]
        );
    
        return $this->fragmentHandler->render($controllerReference);
    }
    
    /**
     * Returns common arguments for displaying the selected object using the external controller.
     */
    protected function getDisplayArguments(array $properties = []): array
    {
        return [
            'objectType' => $properties['objectType'],
            'id' => $properties['id'],
            'source' => 'block',
            'displayMode' => 'embed',
        ];
    }
    
    public function getFormClassName(): string
    {
        return ItemBlockType::class;
    }
    
    public function getFormOptions(): array
    {
        $objectType = 'message';
    
        return [
            'object_type' => $objectType,
        ];
    }
    
    public function getFormTemplate(): string
    {
        return '@MUNewsModule/Block/item_modify.html.twig';
    }
    
    public function getPropertyDefaults(): array
    {
        return [
            'objectType' => 'message',
            'id' => null,
            'template' => 'item_display.html.twig',
            'customTemplate' => null,
        ];
    }
    
    /**
     * @required
     */
    public function setControllerHelper(ControllerHelper $controllerHelper): void
    {
        $this->controllerHelper = $controllerHelper;
    }
    
    /**
     * @required
     */
    public function setFragmentHandler(FragmentHandler $fragmentHandler): void
    {
        $this->fragmentHandler = $fragmentHandler;
    }
}
