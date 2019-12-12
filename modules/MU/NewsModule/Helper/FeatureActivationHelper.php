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

namespace MU\NewsModule\Helper;

use MU\NewsModule\Helper\Base\AbstractFeatureActivationHelper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Helper implementation class for dynamic feature enablement methods.
 */
class FeatureActivationHelper extends AbstractFeatureActivationHelper
{
    /**
     * CREATORS feature
     */
    const CREATORS = 'creators';

    /**
     * CREATEDDATES feature
     */
    const CREATEDDATES = 'createddates';

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * FeatureActivationHelper constructor.
     *
     * @param VariableApiInterface $variableApi
     */
    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function isEnabled($feature = '', $objectType = '')
    {
        if (self::CATEGORIES === $feature) {
            $method = 'hasCategories';
            if (method_exists($this, $method)) {
                return $this->$method($objectType);
            }
    
            return in_array($objectType, ['message'], true);
        }
        if (self::ATTRIBUTES === $feature) {
            $method = 'hasAttributes';
            if (method_exists($this, $method)) {
                return $this->$method($objectType);
            }
    
            return in_array($objectType, ['message'], true);
        }
        if (self::TRANSLATIONS === $feature) {
            $method = 'hasTranslations';
            if (method_exists($this, $method)) {
                return $this->$method($objectType);
            }
    
            return in_array($objectType, ['message'], true);
        }
        
        if (self::CREATORS === $feature) {
            $method = 'hasCreators';
            if (method_exists($this, $method)) {
                return $this->$method($objectType);
            }
        
            return in_array($objectType, ['message'], true);
        }
        
        if (self::CREATEDDATES === $feature) {
            $method = 'hasCreatedDates';
            if (method_exists($this, $method)) {
                return $this->$method($objectType);
            }
        
            return in_array($objectType, ['message'], true);
        }
    
        return false;
    }
    
    public function hasCategories($objectType)
    {
        return 'message' === $objectType
            && 1 == $this->variableApi->get('MUNewsModule', 'enableCategorization')
        ;
    }
    
    public function hasAttributes($objectType)
    {
        return 'message' === $objectType
            && $this->variableApi->get('MUNewsModule', 'enableAttribution') == 1
        ;
    }
    
    public function hasTranslations($objectType)
    {
        return 'message' === $objectType
            && $this->variableApi->get('MUNewsModule', 'enableMultiLanguage') == 1
        ;
    }
    
    public function hasCreators($objectType)
    {
        return 'message' === $objectType
            && $this->variableApi->get('MUNewsModule', 'showAuthor') == 1
        ;
    }
    
    public function hasCreatedDates($objectType)
    {
        return 'message' === $objectType
            && $this->variableApi->get('MUNewsModule', 'showDate') == 1
        ;
    }
}
