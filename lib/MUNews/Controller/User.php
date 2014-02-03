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
 * This is the User controller class providing navigation and interaction functionality.
 */
class MUNews_Controller_User extends MUNews_Controller_Base_User
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Set caching to true by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }
}
