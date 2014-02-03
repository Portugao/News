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
 * @version Generated by ModuleStudio 0.6.1 (http://modulestudio.de).
 */

/**
 * Event handler implementation class for view-related events.
 */
class MUNews_Listener_View extends MUNews_Listener_Base_View
{
    /**
     * Listener for the `view.init` event.
     *
     * Occurs just before `Zikula_View#__construct()` finishes.
     * The subject is the Zikula_View instance.
     *
     * @param Zikula_Event $event The event instance.
     */
    public static function init(Zikula_Event $event)
    {
        parent::init($event);
    }
    
    /**
     * Listener for the `view.postfetch` event.
     *
     * Filter of result of a fetch.
     * Receives `Zikula_View` instance as subject,
     * args are `array('template' => $template)`,
     * $data was the result of the fetch to be filtered.
     *
     * @param Zikula_Event $event The event instance.
     */
    public static function postFetch(Zikula_Event $event)
    {
        parent::postFetch($event);
    }
}
