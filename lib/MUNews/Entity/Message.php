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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the concrete entity class for message entities.
 * @ORM\Entity(repositoryClass="MUNews_Entity_Repository_Message")
  * @ORM\Table(name="munews_message",
  *     indexes={
 *         @ORM\Index(name="workflowstateindex", columns={"workflowState"})
  *     }
  * )
 * @ORM\HasLifecycleCallbacks
 */
class MUNews_Entity_Message extends MUNews_Entity_Base_Message
{
    // feel free to add your own methods here

    
    /**
     * Post-Process the data after the entity has been constructed by the entity manager.
     *
     * @ORM\PostLoad
     * @see MUNews_Entity_Message::performPostLoadCallback()
     * @return void.
     */
    public function postLoadCallback()
    {
        $this->performPostLoadCallback();
    }
    
    /**
     * Pre-Process the data prior to an insert operation.
     *
     * @ORM\PrePersist
     * @see MUNews_Entity_Message::performPrePersistCallback()
     * @return void.
     */
    public function prePersistCallback()
    {
        $this->performPrePersistCallback();
    }
    
    /**
     * Post-Process the data after an insert operation.
     *
     * @ORM\PostPersist
     * @see MUNews_Entity_Message::performPostPersistCallback()
     * @return void.
     */
    public function postPersistCallback()
    {
        $this->performPostPersistCallback();
    }
    
    /**
     * Pre-Process the data prior a delete operation.
     *
     * @ORM\PreRemove
     * @see MUNews_Entity_Message::performPreRemoveCallback()
     * @return void.
     */
    public function preRemoveCallback()
    {
        $this->performPreRemoveCallback();
    }
    
    /**
     * Post-Process the data after a delete.
     *
     * @ORM\PostRemove
     * @see MUNews_Entity_Message::performPostRemoveCallback()
     * @return void
     */
    public function postRemoveCallback()
    {
        $this->performPostRemoveCallback();
    }
    
    /**
     * Pre-Process the data prior to an update operation.
     *
     * @ORM\PreUpdate
     * @see MUNews_Entity_Message::performPreUpdateCallback()
     * @return void.
     */
    public function preUpdateCallback()
    {
        $this->performPreUpdateCallback();
    }
    
    /**
     * Post-Process the data after an update operation.
     *
     * @ORM\PostUpdate
     * @see MUNews_Entity_Message::performPostUpdateCallback()
     * @return void.
     */
    public function postUpdateCallback()
    {
        $this->performPostUpdateCallback();
    }
    
    /**
     * Pre-Process the data prior to a save operation.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @see MUNews_Entity_Message::performPreSaveCallback()
     * @return void.
     */
    public function preSaveCallback()
    {
        $this->performPreSaveCallback();
    }
    
    /**
     * Post-Process the data after a save operation.
     *
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @see MUNews_Entity_Message::performPostSaveCallback()
     * @return void.
     */
    public function postSaveCallback()
    {
        $this->performPostSaveCallback();
    }
    
}
