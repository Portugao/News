<?php
/**
 * MUNews.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license 
 * @package MUNews
 * @author Michael Ueberschaer <kontakt@webdesign-in-bremen.com>.
 * @link http://webdesign-in-bremen.com
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.6.1 (http://modulestudio.de) at Mon Feb 03 13:42:10 CET 2014.
 */

/**
 * Version information base class.
 */
class MUNews_Base_Version extends Zikula_AbstractVersion
{
    /**
     * Retrieves meta data information for this application.
     *
     * @return array List of meta data.
     */
    public function getMetaData()
    {
        $meta = array();
        // the current module version
        $meta['version']              = '1.0.0';
        // the displayed name of the module
        $meta['displayname']          = $this->__('M u news');
        // the module description
        $meta['description']          = $this->__('M u news module generated by ModuleStudio 0.6.1.');
        //! url version of name, should be in lowercase without space
        $meta['url']                  = $this->__('munews');
        // core requirement
        $meta['core_min']             = '1.3.5'; // requires minimum 1.3.5
        $meta['core_max']             = '1.3.6'; // not ready for 1.3.7 yet

        // define special capabilities of this module
        $meta['capabilities'] = array(
                          HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true)
/*,
                          HookUtil::PROVIDER_CAPABLE => array('enabled' => true), // TODO: see #15
                          'authentication' => array('version' => '1.0'),
                          'profile'        => array('version' => '1.0', 'anotherkey' => 'anothervalue'),
                          'message'        => array('version' => '1.0', 'anotherkey' => 'anothervalue')
*/
        );

        // permission schema
        $meta['securityschema'] = array(
            'MUNews::' => '::',
            'MUNews::Ajax' => '::',
            'MUNews:ItemListBlock:' => 'Block title::',
            'MUNews:ModerationBlock:' => 'Block title::',
            'MUNews:Message:' => 'Message ID::',
        );
        // DEBUG: permission schema aspect ends


        return $meta;
    }

    /**
     * Define hook subscriber bundles.
     */
    protected function setupHookBundles()
    {
        
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.munews.ui_hooks.messages', 'ui_hooks', __('munews Messages Display Hooks'));
        
        // Display hook for view/display templates.
        $bundle->addEvent('display_view', 'munews.ui_hooks.messages.display_view');
        // Display hook for create/edit forms.
        $bundle->addEvent('form_edit', 'munews.ui_hooks.messages.form_edit');
        // Display hook for delete dialogues.
        $bundle->addEvent('form_delete', 'munews.ui_hooks.messages.form_delete');
        // Validate input from an ui create/edit form.
        $bundle->addEvent('validate_edit', 'munews.ui_hooks.messages.validate_edit');
        // Validate input from an ui create/edit form (generally not used).
        $bundle->addEvent('validate_delete', 'munews.ui_hooks.messages.validate_delete');
        // Perform the final update actions for a ui create/edit form.
        $bundle->addEvent('process_edit', 'munews.ui_hooks.messages.process_edit');
        // Perform the final delete actions for a ui form.
        $bundle->addEvent('process_delete', 'munews.ui_hooks.messages.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.munews.filter_hooks.messages', 'filter_hooks', __('munews Messages Filter Hooks'));
        // A filter applied to the given area.
        $bundle->addEvent('filter', 'munews.filter_hooks.messages.filter');
        $this->registerHookSubscriberBundle($bundle);

        
    }
}
