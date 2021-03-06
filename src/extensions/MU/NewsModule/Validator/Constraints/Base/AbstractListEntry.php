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

namespace MU\NewsModule\Validator\Constraints\Base;

use Symfony\Component\Validator\Constraint;

/**
 * List entry validation constraint.
 */
abstract class AbstractListEntry extends Constraint
{
    /**
     * Entity name.
     *
     * @var string
     */
    public $entityName = '';

    /**
     * Property name.
     *
     * @var string
     */
    public $propertyName = '';

    /**
     * Whether multiple list values are allowed or not.
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * Minimum amount of values for multiple lists.
     *
     * @var int
     */
    public $min;

    /**
     * Maximum amount of values for multiple lists.
     *
     * @var int
     */
    public $max;
}
