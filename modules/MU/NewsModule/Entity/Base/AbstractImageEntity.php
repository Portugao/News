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

namespace MU\NewsModule\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;
use MU\NewsModule\Traits\StandardFieldsTrait;
use MU\NewsModule\Validator\Constraints as NewsAssert;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the base entity class for image entities.
 * The following annotation marks it as a mapped superclass so subclasses
 * inherit orm properties.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractImageEntity extends EntityAccess
{
    /**
     * Hook standard fields behaviour embedding createdBy, updatedBy, createdDate, updatedDate fields.
     */
    use StandardFieldsTrait;

    /**
     * @var string The tablename this object maps to
     */
    protected $_objectType = 'image';
    
    /**
     * @var string Path to upload base folder
     */
    protected $_uploadBasePath = '';
    
    /**
     * @var string Base URL to upload files
     */
    protected $_uploadBaseUrl = '';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", unique=true)
     * @var int $id
     */
    protected $id = 0;
    
    /**
     * the current workflow state
     *
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     * @NewsAssert\ListEntry(entityName="image", propertyName="workflowState", multiple=false)
     * @var string $workflowState
     */
    protected $workflowState = 'initial';
    
    /**
     * The file meta data array.
     *
     * @ORM\Column(type="array")
     * @Assert\Type(type="array")
     * @var array $theFileMeta
     */
    protected $theFileMeta = [];
    
    /**
     * @ORM\Column(name="theFile", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $theFileFileName
     */
    protected $theFileFileName = null;
    
    /**
     * Full the file path as url.
     *
     * @Assert\Type(type="string")
     * @var string $theFileUrl
     */
    protected $theFileUrl = '';
    
    /**
     * The file file object.
     *
     * @Assert\File(
     *    mimeTypes = {"image/*"}
     * )
     * @Assert\Image(
     * )
     * @var File $theFile
     */
    protected $theFile = null;
    
    /**
     * @ORM\Column(length=255)
     * @Assert\NotNull()
     * @Assert\Length(min="0", max="255")
     * @var string $caption
     */
    protected $caption = '';
    
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="smallint")
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\NotEqualTo(value=0)
     * @Assert\GreaterThanOrEqual(value=1)
     * @var int $sortNumber
     */
    protected $sortNumber = 1;
    
    
    /**
     * Bidirectional - Many images [images] are linked by one message [message] (OWNING SIDE).
     *
     * @ORM\ManyToOne(
     *     targetEntity="MU\NewsModule\Entity\MessageEntity",
     *     inversedBy="images"
     * )
     * @ORM\JoinTable(name="mu_news_message")
     * @Assert\Type(type="MU\NewsModule\Entity\MessageEntity")
     * @var \MU\NewsModule\Entity\MessageEntity $message
     */
    protected $message;
    
    
    /**
     * ImageEntity constructor.
     *
     * Will not be called by Doctrine and can therefore be used
     * for own implementation purposes. It is also possible to add
     * arbitrary arguments as with every other class method.
     */
    public function __construct()
    {
    }
    
    /**
     * Returns the _object type.
     *
     * @return string
     */
    public function get_objectType()
    {
        return $this->_objectType;
    }
    
    /**
     * Sets the _object type.
     *
     * @param string $_objectType
     *
     * @return void
     */
    public function set_objectType($_objectType)
    {
        if ($this->_objectType !== $_objectType) {
            $this->_objectType = isset($_objectType) ? $_objectType : '';
        }
    }
    
    /**
     * Returns the _upload base path.
     *
     * @return string
     */
    public function get_uploadBasePath()
    {
        return $this->_uploadBasePath;
    }
    
    /**
     * Sets the _upload base path.
     *
     * @param string $_uploadBasePath
     *
     * @return void
     */
    public function set_uploadBasePath($_uploadBasePath)
    {
        if ($this->_uploadBasePath !== $_uploadBasePath) {
            $this->_uploadBasePath = isset($_uploadBasePath) ? $_uploadBasePath : '';
        }
    }
    
    /**
     * Returns the _upload base url.
     *
     * @return string
     */
    public function get_uploadBaseUrl()
    {
        return $this->_uploadBaseUrl;
    }
    
    /**
     * Sets the _upload base url.
     *
     * @param string $_uploadBaseUrl
     *
     * @return void
     */
    public function set_uploadBaseUrl($_uploadBaseUrl)
    {
        if ($this->_uploadBaseUrl !== $_uploadBaseUrl) {
            $this->_uploadBaseUrl = isset($_uploadBaseUrl) ? $_uploadBaseUrl : '';
        }
    }
    
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id = null)
    {
        if ((int)$this->id !== (int)$id) {
            $this->id = (int)$id;
        }
    }
    
    /**
     * Returns the workflow state.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->workflowState;
    }
    
    /**
     * Sets the workflow state.
     *
     * @param string $workflowState
     *
     * @return void
     */
    public function setWorkflowState($workflowState)
    {
        if ($this->workflowState !== $workflowState) {
            $this->workflowState = isset($workflowState) ? $workflowState : '';
        }
    }
    /**
     * Returns the the file.
     *
     * @return File
     */
    public function getTheFile()
    {
        if (null !== $this->theFile) {
            return $this->theFile;
        }
    
        $fileName = $this->theFileFileName;
        if (!empty($fileName) && !$this->_uploadBasePath) {
            throw new RuntimeException('Invalid upload base path in ' . get_class($this) . '#getTheFile().');
        }
    
        $filePath = $this->_uploadBasePath . 'thefile/' . $fileName;
        if (!empty($fileName) && file_exists($filePath)) {
            $this->theFile = new File($filePath);
            $this->setTheFileUrl($this->_uploadBaseUrl . '/' . $filePath);
        } else {
            $this->setTheFileFileName('');
            $this->setTheFileUrl('');
            $this->setTheFileMeta([]);
        }
    
        return $this->theFile;
    }
    
    /**
     * Sets the the file.
     *
     * @return void
     */
    public function setTheFile(File $theFile = null)
    {
        if (null === $this->theFile && null === $theFile) {
            return;
        }
        if (null !== $this->theFile && null !== $theFile && $this->theFile instanceof File && $this->theFile->getRealPath() === $theFile->getRealPath()) {
            return;
        }
        $this->theFile = isset($theFile) ? $theFile : '';
    
        if (null === $this->theFile || '' === $this->theFile) {
            $this->setTheFileFileName('');
            $this->setTheFileUrl('');
            $this->setTheFileMeta([]);
        } else {
            $this->setTheFileFileName($this->theFile->getFilename());
        }
    }
    
    
    /**
     * Returns the the file file name.
     *
     * @return string
     */
    public function getTheFileFileName()
    {
        return $this->theFileFileName;
    }
    
    /**
     * Sets the the file file name.
     *
     * @param string $theFileFileName
     *
     * @return void
     */
    public function setTheFileFileName($theFileFileName = null)
    {
        if ($this->theFileFileName !== $theFileFileName) {
            $this->theFileFileName = isset($theFileFileName) ? $theFileFileName : '';
        }
    }
    
    /**
     * Returns the the file url.
     *
     * @return string
     */
    public function getTheFileUrl()
    {
        return $this->theFileUrl;
    }
    
    /**
     * Sets the the file url.
     *
     * @param string $theFileUrl
     *
     * @return void
     */
    public function setTheFileUrl($theFileUrl = null)
    {
        if ($this->theFileUrl !== $theFileUrl) {
            $this->theFileUrl = isset($theFileUrl) ? $theFileUrl : '';
        }
    }
    
    /**
     * Returns the the file meta.
     *
     * @return array
     */
    public function getTheFileMeta()
    {
        return $this->theFileMeta;
    }
    
    /**
     * Sets the the file meta.
     *
     * @param array $theFileMeta
     *
     * @return void
     */
    public function setTheFileMeta(array $theFileMeta = [])
    {
        if ($this->theFileMeta !== $theFileMeta) {
            $this->theFileMeta = isset($theFileMeta) ? $theFileMeta : '';
        }
    }
    
    /**
     * Returns the caption.
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the caption.
     *
     * @param string $caption
     *
     * @return void
     */
    public function setCaption($caption)
    {
        if ($this->caption !== $caption) {
            $this->caption = isset($caption) ? $caption : '';
        }
    }
    
    /**
     * Returns the sort number.
     *
     * @return int
     */
    public function getSortNumber()
    {
        return $this->sortNumber;
    }
    
    /**
     * Sets the sort number.
     *
     * @param int $sortNumber
     *
     * @return void
     */
    public function setSortNumber($sortNumber)
    {
        if ((int)$this->sortNumber !== (int)$sortNumber) {
            $this->sortNumber = (int)$sortNumber;
        }
    }
    
    /**
     * Returns the message.
     *
     * @return \MU\NewsModule\Entity\MessageEntity
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Sets the message.
     *
     * @param \MU\NewsModule\Entity\MessageEntity $message
     *
     * @return void
     */
    public function setMessage(\MU\NewsModule\Entity\MessageEntity $message = null)
    {
        $this->message = $message;
    }
    
    /**
     * Creates url arguments array for easy creation of display urls.
     *
     * @return array List of resulting arguments
     */
    public function createUrlArgs()
    {
        return [
            'id' => $this->getId()
        ];
    }
    
    /**
     * Returns the primary key.
     *
     * @return int The identifier
     */
    public function getKey()
    {
        return $this->getId();
    }
    
    /**
     * Determines whether this entity supports hook subscribers or not.
     *
     * @return bool
     */
    public function supportsHookSubscribers()
    {
        return true;
    }
    
    /**
     * Return lower case name of multiple items needed for hook areas.
     *
     * @return string
     */
    public function getHookAreaPrefix()
    {
        return 'munewsmodule.ui_hooks.images';
    }
    
    /**
     * Returns an array of all related objects that need to be persisted after clone.
     * 
     * @param array $objects Objects that are added to this array
     * 
     * @return array List of entity objects
     */
    public function getRelatedObjectsToPersist(&$objects = [])
    {
        return [];
    }
    
    /**
     * ToString interceptor implementation.
     * This method is useful for debugging purposes.
     *
     * @return string The output string for this entity
     */
    public function __toString()
    {
        return 'Image ' . $this->getKey() . ': ' . $this->getCaption();
    }
    
    /**
     * Clone interceptor implementation.
     * This method is for example called by the reuse functionality.
     * Performs a quite simple shallow copy.
     *
     * See also:
     * (1) http://docs.doctrine-project.org/en/latest/cookbook/implementing-wakeup-or-clone.html
     * (2) http://www.php.net/manual/en/language.oop5.cloning.php
     * (3) http://stackoverflow.com/questions/185934/how-do-i-create-a-copy-of-an-object-in-php
     */
    public function __clone()
    {
        // if the entity has no identity do nothing, do NOT throw an exception
        if (!$this->id) {
            return;
        }
    
        // otherwise proceed
    
        // unset identifier
        $this->setId(0);
    
        // reset workflow
        $this->setWorkflowState('initial');
    
        // reset upload fields
        $this->setTheFile(null);
    
        $this->setCreatedBy(null);
        $this->setCreatedDate(null);
        $this->setUpdatedBy(null);
        $this->setUpdatedDate(null);
    }
}
