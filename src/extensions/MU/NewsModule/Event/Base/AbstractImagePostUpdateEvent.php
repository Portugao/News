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

namespace MU\NewsModule\Event\Base;

use MU\NewsModule\Entity\ImageEntity;

/**
 * Event base class for filtering image processing.
 */
abstract class AbstractImagePostUpdateEvent
{
    /**
     * @var ImageEntity Reference to treated entity instance
     */
    protected $image;

    public function __construct(ImageEntity $image)
    {
        $this->image = $image;
    }

    public function getImage(): ImageEntity
    {
        return $this->image;
    }
}
