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

namespace MU\NewsModule\Entity;

use MU\NewsModule\Entity\Base\AbstractMessageCategoryEntity as BaseEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity extension domain class storing message categories.
 *
 * This is the concrete category class for message entities.
 * @ORM\Entity(repositoryClass="\MU\NewsModule\Entity\Repository\MessageCategoryRepository")
 * @ORM\Table(name="mu_news_message_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cat_unq", columns={"registryId", "categoryId", "entityId"})
 *     }
 * )
 */
class MessageCategoryEntity extends BaseEntity
{
    // feel free to add your own methods here
}
