<?php
/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 * @link https://homepages-mit-zikula.de
 * @link https://ziku.la
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace MU\NewsModule\Twig\Base;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use MU\NewsModule\Helper\EntityDisplayHelper;
use MU\NewsModule\Helper\ListEntriesHelper;
use MU\NewsModule\Helper\WorkflowHelper;

/**
 * Twig extension base class.
 */
abstract class AbstractTwigExtension extends Twig_Extension
{
    use TranslatorTrait;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var EntityDisplayHelper
     */
    protected $entityDisplayHelper;
    
    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;
    
    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;
    
    /**
     * TwigExtension constructor.
     *
     * @param TranslatorInterface $translator     Translator service instance
     * @param RequestStack        $requestStack   RequestStack service instance
     * @param VariableApiInterface   $variableApi    VariableApi service instance
     * @param EntityDisplayHelper    $entityDisplayHelper EntityDisplayHelper service instance
     * @param WorkflowHelper         $workflowHelper WorkflowHelper service instance
     * @param ListEntriesHelper   $listHelper     ListEntriesHelper service instance
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        EntityDisplayHelper $entityDisplayHelper,
        WorkflowHelper $workflowHelper,
        ListEntriesHelper $listHelper
    ) {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->entityDisplayHelper = $entityDisplayHelper;
        $this->workflowHelper = $workflowHelper;
        $this->listHelper = $listHelper;
    }
    
    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Returns a list of custom Twig functions.
     *
     * @return \Twig_SimpleFunction[] List of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('munewsmodule_moderationObjects', [$this, 'getModerationObjects']),
            new \Twig_SimpleFunction('munewsmodule_objectTypeSelector', [$this, 'getObjectTypeSelector']),
            new \Twig_SimpleFunction('munewsmodule_templateSelector', [$this, 'getTemplateSelector'])
        ];
    }
    
    /**
     * Returns a list of custom Twig filters.
     *
     * @return \Twig_SimpleFilter[] List of filters
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('munewsmodule_fileSize', [$this, 'getFileSize'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('munewsmodule_listEntry', [$this, 'getListEntry']),
            new \Twig_SimpleFilter('munewsmodule_icalText', [$this, 'formatIcalText']),
            new \Twig_SimpleFilter('munewsmodule_formattedTitle', [$this, 'getFormattedEntityTitle']),
            new \Twig_SimpleFilter('munewsmodule_objectState', [$this, 'getObjectState'], ['is_safe' => ['html']])
        ];
    }
    
    /**
     * The munewsmodule_objectState filter displays the name of a given object's workflow state.
     * Examples:
     *    {{ item.workflowState|munewsmodule_objectState }}        {# with visual feedback #}
     *    {{ item.workflowState|munewsmodule_objectState(false) }} {# no ui feedback #}
     *
     * @param string  $state      Name of given workflow state
     * @param boolean $uiFeedback Whether the output should include some visual feedback about the state
     *
     * @return string Enriched and translated workflow state ready for display
     */
    public function getObjectState($state = 'initial', $uiFeedback = true)
    {
        $stateInfo = $this->workflowHelper->getStateInfo($state);
    
        $result = $stateInfo['text'];
        if (true === $uiFeedback) {
            $result = '<span class="label label-' . $stateInfo['ui'] . '">' . $result . '</span>';
        }
    
        return $result;
    }
    
    
    /**
     * The munewsmodule_fileSize filter displays the size of a given file in a readable way.
     * Example:
     *     {{ 12345|munewsmodule_fileSize }}
     *
     * @param integer $size     File size in bytes
     * @param string  $filepath The input file path including file name (if file size is not known)
     * @param boolean $nodesc   If set to true the description will not be appended
     * @param boolean $onlydesc If set to true only the description will be returned
     *
     * @return string File size in a readable form
     */
    public function getFileSize($size = 0, $filepath = '', $nodesc = false, $onlydesc = false)
    {
        if (!is_numeric($size)) {
            $size = (int) $size;
        }
        if (!$size) {
            if (empty($filepath) || !file_exists($filepath)) {
                return '';
            }
            $size = filesize($filepath);
        }
        if (!$size) {
            return '';
        }
    
        return $this->getReadableFileSize($size, $nodesc, $onlydesc);
    }
    
    /**
     * Display a given file size in a readable format
     *
     * @param string  $size     File size in bytes
     * @param boolean $nodesc   If set to true the description will not be appended
     * @param boolean $onlydesc If set to true only the description will be returned
     *
     * @return string File size in a readable form
     */
    private function getReadableFileSize($size, $nodesc = false, $onlydesc = false)
    {
        $sizeDesc = $this->__('Bytes');
        if ($size >= 1024) {
            $size /= 1024;
            $sizeDesc = $this->__('KB');
        }
        if ($size >= 1024) {
            $size /= 1024;
            $sizeDesc = $this->__('MB');
        }
        if ($size >= 1024) {
            $size /= 1024;
            $sizeDesc = $this->__('GB');
        }
        $sizeDesc = '&nbsp;' . $sizeDesc;
    
        // format number
        $dec_point = ',';
        $thousands_separator = '.';
        if ($size - number_format($size, 0) >= 0.005) {
            $size = number_format($size, 2, $dec_point, $thousands_separator);
        } else {
            $size = number_format($size, 0, '', $thousands_separator);
        }
    
        // append size descriptor if desired
        if (!$nodesc) {
            $size .= $sizeDesc;
        }
    
        // return either only the description or the complete string
        return $onlydesc ? $sizeDesc : $size;
    }
    
    
    /**
     * The munewsmodule_listEntry filter displays the name
     * or names for a given list item.
     * Example:
     *     {{ entity.listField|munewsmodule_listEntry('entityName', 'fieldName') }}
     *
     * @param string $value      The dropdown value to process
     * @param string $objectType The treated object type
     * @param string $fieldName  The list field's name
     * @param string $delimiter  String used as separator for multiple selections
     *
     * @return string List item name
     */
    public function getListEntry($value, $objectType = '', $fieldName = '', $delimiter = ', ')
    {
        if ((empty($value) && $value != '0') || empty($objectType) || empty($fieldName)) {
            return $value;
        }
    
        $this->translator->setDomain('munewsmodule');
    
        return $this->listHelper->resolve($value, $objectType, $fieldName, $delimiter);
    }
    
    
    /**
     * The munewsmodule_moderationObjects function determines the amount of unapproved objects.
     * It uses the same logic as the moderation block and the pending content listener.
     *
     * @return string The output of the plugin
     */
    public function getModerationObjects()
    {
        return $this->workflowHelper->collectAmountOfModerationItems();
    }
    
    
    /**
     * The munewsmodule_icalText filter outputs a given text for the ics output format.
     * Example:
     *     {{ 'someString'|munewsmodule_icalText }}
     *
     * @param string $string The given output string
     *
     * @return string Processed string for ics output
     */
    public function formatIcalText($string)
    {
        $result = preg_replace('/<a href="(.*)">.*<\/a>/i', "$1", $string);
        $result = str_replace('€', 'Euro', $result);
        $result = ereg_replace("(\r\n|\n|\r)", '=0D=0A', $result);
    
        return ';LANGUAGE=' . $this->requestStack->getCurrentRequest()->getLocale() . ';ENCODING=QUOTED-PRINTABLE:' . $result . "\r\n";
    }
    
    
    /**
     * The munewsmodule_objectTypeSelector function provides items for a dropdown selector.
     *
     * @return string The output of the plugin
     */
    public function getObjectTypeSelector()
    {
        $result = [];
    
        $result[] = [
            'text' => $this->__('Messages'),
            'value' => 'message'
        ];
        $result[] = [
            'text' => $this->__('Images'),
            'value' => 'image'
        ];
    
        return $result;
    }
    
    
    /**
     * The munewsmodule_templateSelector function provides items for a dropdown selector.
     *
     * @return string The output of the plugin
     */
    public function getTemplateSelector()
    {
        $result = [];
    
        $result[] = [
            'text' => $this->__('Only item titles'),
            'value' => 'itemlist_display.html.twig'
        ];
        $result[] = [
            'text' => $this->__('With description'),
            'value' => 'itemlist_display_description.html.twig'
        ];
        $result[] = [
            'text' => $this->__('Custom template'),
            'value' => 'custom'
        ];
    
        return $result;
    }
    
    /**
     * The munewsmodule_formattedTitle filter outputs a formatted title for a given entity.
     * Example:
     *     {{ myPost|munewsmodule_formattedTitle }}
     *
     * @param object $entity The given entity instance
     *
     * @return string The formatted title
     */
    public function getFormattedEntityTitle($entity)
    {
        return $this->entityDisplayHelper->getFormattedTitle($entity);
    }
}
