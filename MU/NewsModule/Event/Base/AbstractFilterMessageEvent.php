<?php
/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepage-mit-zikula.de>.
 * @link https://homepages-mit-zikula.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.0.1 (https://modulestudio.de).
 */

namespace MU\NewsModule\Event\Base;

use Symfony\Component\EventDispatcher\Event;
use MU\NewsModule\Entity\MessageEntity;

/**
 * Event base class for filtering message processing.
 */
class AbstractFilterMessageEvent extends Event
{
    /**
     * @var MessageEntity Reference to treated entity instance.
     */
    protected $message;

    /**
     * @var array Entity change set for preUpdate events.
     */
    protected $entityChangeSet = [];

    /**
     * FilterMessageEvent constructor.
     *
     * @param MessageEntity $message Processed entity
     * @param array $entityChangeSet Change set for preUpdate events
     */
    public function __construct(MessageEntity $message, $entityChangeSet = [])
    {
        $this->message = $message;
        $this->entityChangeSet = $entityChangeSet;
    }

    /**
     * Returns the entity.
     *
     * @return MessageEntity
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the change set.
     *
     * @return array
     */
    public function getEntityChangeSet()
    {
        return $this->entityChangeSet;
    }
}
