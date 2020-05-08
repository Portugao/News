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

namespace MU\NewsModule\Entity\Repository\Base;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use MU\NewsModule\Entity\ImageEntity;
use MU\NewsModule\Helper\CollectionFilterHelper;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the base repository class for image entities.
 */
abstract class AbstractImageRepository extends SortableRepository
{
    /**
     * @var string The main entity class
     */
    protected $mainEntityClass = ImageEntity::class;

    /**
     * @var string The default sorting field/expression
     */
    protected $defaultSortingField = 'sortNumber';

    /**
     * @var CollectionFilterHelper
     */
    protected $collectionFilterHelper;

    /**
     * Retrieves an array with all fields which can be used for sorting instances.
     *
     * @return string[] List of sorting field names
     */
    public function getAllowedSortingFields()
    {
        return [
            'theFile',
            'caption',
            'sortNumber',
            'message',
            'createdBy',
            'createdDate',
            'updatedBy',
            'updatedDate',
        ];
    }
    
    /**
     * Returns the default sorting field.
     *
     * @return string
     */
    public function getDefaultSortingField()
    {
        return $this->defaultSortingField;
    }
    
    /**
     * Sets the default sorting field.
     *
     * @param string $defaultSortingField
     *
     * @return void
     */
    public function setDefaultSortingField($defaultSortingField = null)
    {
        if ($this->defaultSortingField !== $defaultSortingField) {
            $this->defaultSortingField = $defaultSortingField;
        }
    }
    
    /**
     * Returns the collection filter helper.
     *
     * @return CollectionFilterHelper
     */
    public function getCollectionFilterHelper()
    {
        return $this->collectionFilterHelper;
    }
    
    /**
     * Sets the collection filter helper.
     *
     * @param CollectionFilterHelper $collectionFilterHelper
     *
     * @return void
     */
    public function setCollectionFilterHelper(CollectionFilterHelper $collectionFilterHelper = null)
    {
        if ($this->collectionFilterHelper !== $collectionFilterHelper) {
            $this->collectionFilterHelper = $collectionFilterHelper;
        }
    }
    
    /**
     * Updates the creator of all objects created by a certain user.
     *
     * @param int $userId
     * @param int $newUserId
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param CurrentUserApiInterface $currentUserApi
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function updateCreator(
        $userId,
        $newUserId,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CurrentUserApiInterface $currentUserApi
    ) {
        if (
            0 === $userId || !is_numeric($userId)
            || 0 === $newUserId || !is_numeric($newUserId)
        ) {
            throw new InvalidArgumentException($translator->__('Invalid user identifier received.'));
        }
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update($this->mainEntityClass, 'tbl')
           ->set('tbl.createdBy', $newUserId)
           ->where('tbl.createdBy = :creator')
           ->setParameter('creator', $userId);
        $query = $qb->getQuery();
        $query->execute();
    
        $logArgs = [
            'app' => 'MUNewsModule',
            'user' => $currentUserApi->get('uname'),
            'entities' => 'images',
            'userid' => $userId
        ];
        $logger->debug('{app}: User {user} updated {entities} created by user id {userid}.', $logArgs);
    }
    
    /**
     * Updates the last editor of all objects updated by a certain user.
     *
     * @param int $userId
     * @param int $newUserId
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param CurrentUserApiInterface $currentUserApi
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function updateLastEditor(
        $userId,
        $newUserId,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CurrentUserApiInterface $currentUserApi
    ) {
        if (
            0 === $userId || !is_numeric($userId)
            || 0 === $newUserId || !is_numeric($newUserId)
        ) {
            throw new InvalidArgumentException($translator->__('Invalid user identifier received.'));
        }
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update($this->mainEntityClass, 'tbl')
           ->set('tbl.updatedBy', $newUserId)
           ->where('tbl.updatedBy = :editor')
           ->setParameter('editor', $userId);
        $query = $qb->getQuery();
        $query->execute();
    
        $logArgs = [
            'app' => 'MUNewsModule',
            'user' => $currentUserApi->get('uname'),
            'entities' => 'images',
            'userid' => $userId
        ];
        $logger->debug('{app}: User {user} updated {entities} edited by user id {userid}.', $logArgs);
    }
    
    /**
     * Deletes all objects created by a certain user.
     *
     * @param int $userId
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param CurrentUserApiInterface $currentUserApi
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function deleteByCreator(
        $userId,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CurrentUserApiInterface $currentUserApi
    ) {
        if (0 === $userId || !is_numeric($userId)) {
            throw new InvalidArgumentException($translator->__('Invalid user identifier received.'));
        }
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->mainEntityClass, 'tbl')
           ->where('tbl.createdBy = :creator')
           ->setParameter('creator', $userId);
        $query = $qb->getQuery();
        $query->execute();
    
        $logArgs = [
            'app' => 'MUNewsModule',
            'user' => $currentUserApi->get('uname'),
            'entities' => 'images',
            'userid' => $userId
        ];
        $logger->debug('{app}: User {user} deleted {entities} created by user id {userid}.', $logArgs);
    }
    
    /**
     * Deletes all objects updated by a certain user.
     *
     * @param int $userId
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param CurrentUserApiInterface $currentUserApi
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function deleteByLastEditor(
        $userId,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CurrentUserApiInterface $currentUserApi
    ) {
        if (0 === $userId || !is_numeric($userId)) {
            throw new InvalidArgumentException($translator->__('Invalid user identifier received.'));
        }
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->mainEntityClass, 'tbl')
           ->where('tbl.updatedBy = :editor')
           ->setParameter('editor', $userId);
        $query = $qb->getQuery();
        $query->execute();
    
        $logArgs = [
            'app' => 'MUNewsModule',
            'user' => $currentUserApi->get('uname'),
            'entities' => 'images',
            'userid' => $userId
        ];
        $logger->debug('{app}: User {user} deleted {entities} edited by user id {userid}.', $logArgs);
    }

    /**
     * Adds an array of id filters to given query instance.
     *
     * @param array $idList List of identifiers to use to retrieve the object
     * @param QueryBuilder $qb Query builder to be enhanced
     *
     * @return QueryBuilder Enriched query builder instance
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    protected function addIdListFilter(array $idList, QueryBuilder $qb)
    {
        $orX = $qb->expr()->orX();
    
        foreach ($idList as $key => $id) {
            if (0 === $id) {
                throw new InvalidArgumentException('Invalid identifier received.');
            }
    
            $orX->add($qb->expr()->eq('tbl.id', ':idListFilter_' . $key));
            $qb->setParameter('idListFilter_' . $key, $id);
        }
    
        $qb->andWhere($orX);
    
        return $qb;
    }
    
    /**
     * Selects an object from the database.
     *
     * @param mixed $id The id (or array of ids) to use to retrieve the object (optional) (default=0)
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return array|ImageEntity Retrieved data array or imageEntity instance
     */
    public function selectById(
        $id = 0,
        $useJoins = true,
        $slimMode = false
    ) {
        $results = $this->selectByIdList(is_array($id) ? $id : [$id], $useJoins, $slimMode);
    
        return null !== $results && 0 < count($results) ? $results[0] : null;
    }
    
    /**
     * Selects a list of objects with an array of ids
     *
     * @param array $idList The array of ids to use to retrieve the objects (optional) (default=0)
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return array Retrieved ImageEntity instances
     */
    public function selectByIdList(
        array $idList = [0],
        $useJoins = true,
        $slimMode = false
    ) {
        $qb = $this->genericBaseQuery('', '', $useJoins, $slimMode);
        $qb = $this->addIdListFilter($idList, $qb);
    
        if (null !== $this->collectionFilterHelper) {
            $qb = $this->collectionFilterHelper->applyDefaultFilters('image', $qb);
        }
    
        $query = $this->getQueryFromBuilder($qb);
    
        $results = $query->getResult();
    
        return 0 < count($results) ? $results : null;
    }

    /**
     * Adds where clauses excluding desired identifiers from selection.
     *
     * @param QueryBuilder $qb Query builder to be enhanced
     * @param array $exclusions List of identifiers to be excluded from selection
     *
     * @return QueryBuilder Enriched query builder instance
     */
    protected function addExclusion(QueryBuilder $qb, array $exclusions = [])
    {
        if (0 < count($exclusions)) {
            $qb->andWhere('tbl.id NOT IN (:excludedIdentifiers)')
               ->setParameter('excludedIdentifiers', $exclusions);
        }
    
        return $qb;
    }

    /**
     * Returns query builder for selecting a list of objects with a given where clause.
     *
     * @param string $where The where clause to use when retrieving the collection (optional) (default='')
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return QueryBuilder Query builder for the given arguments
     */
    public function getListQueryBuilder(
        $where = '',
        $orderBy = '',
        $useJoins = true,
        $slimMode = false
    ) {
        $qb = $this->genericBaseQuery($where, $orderBy, $useJoins, $slimMode);
        if (null !== $this->collectionFilterHelper) {
            $qb = $this->collectionFilterHelper->addCommonViewFilters('image', $qb);
        }
    
        return $qb;
    }
    
    /**
     * Selects a list of objects with a given where clause.
     *
     * @param string $where The where clause to use when retrieving the collection (optional) (default='')
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return array List of retrieved imageEntity instances
     */
    public function selectWhere(
        $where = '',
        $orderBy = '',
        $useJoins = true,
        $slimMode = false
    ) {
        $qb = $this->getListQueryBuilder($where, $orderBy, $useJoins, $slimMode);
    
        $query = $this->getQueryFromBuilder($qb);
    
        return $this->retrieveCollectionResult($query);
    }

    /**
     * Returns query builder instance for retrieving a list of objects with a given
     * where clause and pagination parameters.
     *
     * @param QueryBuilder $qb Query builder to be enhanced
     * @param int $currentPage Where to start selection
     * @param int $resultsPerPage Amount of items to select
     *
     * @return Query Created query instance
     */
    public function getSelectWherePaginatedQuery(
        QueryBuilder $qb,
        $currentPage = 1,
        $resultsPerPage = 25
    ) {
        if (1 > $currentPage) {
            $currentPage = 1;
        }
        if (1 > $resultsPerPage) {
            $resultsPerPage = 25;
        }
        $query = $this->getQueryFromBuilder($qb);
        $offset = ($currentPage - 1) * $resultsPerPage;
    
        $query->setFirstResult($offset)
              ->setMaxResults($resultsPerPage);
    
        return $query;
    }
    
    /**
     * Selects a list of objects with a given where clause and pagination parameters.
     *
     * @param string $where The where clause to use when retrieving the collection (optional) (default='')
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     * @param int $currentPage Where to start selection
     * @param int $resultsPerPage Amount of items to select
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return array Retrieved collection and the amount of total records affected
     */
    public function selectWherePaginated(
        $where = '',
        $orderBy = '',
        $currentPage = 1,
        $resultsPerPage = 25,
        $useJoins = true,
        $slimMode = false
    ) {
        $qb = $this->getListQueryBuilder($where, $orderBy, $useJoins, $slimMode);
        $query = $this->getSelectWherePaginatedQuery($qb, $currentPage, $resultsPerPage);
    
        return $this->retrieveCollectionResult($query, true);
    }

    /**
     * Selects entities by a given search fragment.
     *
     * @param string $fragment The fragment to search for
     * @param array $exclude List of identifiers to be excluded from search
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     * @param in $currentPage Where to start selection
     * @param in $resultsPerPage Amount of items to select
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     *
     * @return array Retrieved collection and (for paginated queries) the amount of total records affected
     */
    public function selectSearch(
        $fragment = '',
        array $exclude = [],
        $orderBy = '',
        $currentPage = 1,
        $resultsPerPage = 25,
        $useJoins = true
    ) {
        $qb = $this->getListQueryBuilder('', $orderBy, $useJoins);
        if (0 < count($exclude)) {
            $qb = $this->addExclusion($qb, $exclude);
        }
    
        if (null !== $this->collectionFilterHelper) {
            $qb = $this->collectionFilterHelper->addSearchFilter('image', $qb, $fragment);
        }
    
        $query = $this->getSelectWherePaginatedQuery($qb, $currentPage, $resultsPerPage);
    
        return $this->retrieveCollectionResult($query, true);
    }

    /**
     * Performs a given database selection and post-processed the results.
     *
     * @param Query $query The Query instance to be executed
     * @param bool $isPaginated Whether the given query uses a paginator or not (optional) (default=false)
     *
     * @return array Retrieved collection and (for paginated queries) the amount of total records affected
     */
    public function retrieveCollectionResult(
        Query $query,
        $isPaginated = false
    ) {
        $count = 0;
        if (!$isPaginated) {
            $result = $query->getResult();
        } else {
            $paginator = new Paginator($query, true);
    
            $count = count($paginator);
            $result = $paginator;
        }
    
        if (!$isPaginated) {
            return $result;
        }
    
        return [$result, $count];
    }

    /**
     * Returns query builder instance for a count query.
     *
     * @param string $where The where clause to use when retrieving the object count (optional) (default='')
     * @param bool $useJoins Whether to include joining related objects (optional) (default=false)
     *
     * @return QueryBuilder Created query builder instance
     */
    public function getCountQuery($where = '', $useJoins = false)
    {
        $selection = 'COUNT(tbl.id) AS numImages';
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($selection)
           ->from($this->mainEntityClass, 'tbl');
    
        if (true === $useJoins) {
            $this->addJoinsToFrom($qb);
        }
    
        if (!empty($where)) {
            $qb->andWhere($where);
        }
    
        return $qb;
    }

    /**
     * Selects entity count with a given where clause.
     *
     * @param string $where The where clause to use when retrieving the object count (optional) (default='')
     * @param bool $useJoins Whether to include joining related objects (optional) (default=false)
     * @param array $parameters List of determined filter options
     *
     * @return int Amount of affected records
     */
    public function selectCount($where = '', $useJoins = false, array $parameters = [])
    {
        $qb = $this->getCountQuery($where, $useJoins);
    
        if (null !== $this->collectionFilterHelper) {
            $qb = $this->collectionFilterHelper->applyDefaultFilters('image', $qb, $parameters);
        }
    
        $query = $qb->getQuery();
    
        return (int)$query->getSingleScalarResult();
    }

    /**
     * Checks for unique values.
     *
     * @param string $fieldName The name of the property to be checked
     * @param string $fieldValue The value of the property to be checked
     * @param int $excludeId Identifier of images to exclude (optional)
     *
     * @return bool Result of this check, true if the given image does not already exist
     */
    public function detectUniqueState($fieldName, $fieldValue, $excludeId = 0)
    {
        $qb = $this->getCountQuery();
        $qb->andWhere('tbl.' . $fieldName . ' = :' . $fieldName)
           ->setParameter($fieldName, $fieldValue);
    
        if ($excludeId > 0) {
            $qb = $this->addExclusion($qb, [$excludeId]);
        }
    
        $query = $qb->getQuery();
    
        $count = (int)$query->getSingleScalarResult();
    
        return 1 > $count;
    }

    /**
     * Builds a generic Doctrine query supporting WHERE and ORDER BY.
     *
     * @param string $where The where clause to use when retrieving the collection (optional) (default='')
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     * @param bool $useJoins Whether to include joining related objects (optional) (default=true)
     * @param bool $slimMode If activated only some basic fields are selected without using any joins
     *                       (optional) (default=false)
     *
     * @return QueryBuilder Query builder instance to be further processed
     */
    public function genericBaseQuery(
        $where = '',
        $orderBy = '',
        $useJoins = true,
        $slimMode = false
    ) {
        // normally we select the whole table
        $selection = 'tbl';
    
        if (true === $slimMode) {
            // but for the slim version we select only the basic fields, and no joins
    
            $selection = 'tbl.id';
            $selection .= ', tbl.sortNumber';
            $selection .= ', tbl.caption';
            $useJoins = false;
        }
    
        if (true !== $useJoins) {
            $orderByField = $orderBy;
            if (false !== mb_strpos($orderByField, ' ')) {
                list($orderByField, $direction) = explode(' ', $orderByField, 2);
            }
            if (
                in_array($orderByField, ['message'], true)
            ) {
                $useJoins = true;
            }
        }
    
        if (true === $useJoins) {
            $selection .= $this->addJoinsToSelection();
        }
    
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($selection)
           ->from($this->mainEntityClass, 'tbl');
    
        if (true === $useJoins) {
            $this->addJoinsToFrom($qb);
        }
    
        if (!empty($where)) {
            $qb->andWhere($where);
        }
    
        $this->genericBaseQueryAddOrderBy($qb, $orderBy);
    
        return $qb;
    }

    /**
     * Adds ORDER BY clause to given query builder.
     *
     * @param QueryBuilder $qb Given query builder instance
     * @param string $orderBy The order-by clause to use when retrieving the collection (optional) (default='')
     *
     * @return QueryBuilder Query builder instance to be further processed
     */
    protected function genericBaseQueryAddOrderBy(QueryBuilder $qb, $orderBy = '')
    {
        if ('RAND()' === $orderBy) {
            // random selection
            $qb->addSelect('MOD(tbl.id, ' . mt_rand(2, 15) . ') AS HIDDEN randomIdentifiers')
               ->orderBy('randomIdentifiers');
    
            return $qb;
        }
    
        if (empty($orderBy)) {
            $orderBy = $this->defaultSortingField;
        }
    
        if (empty($orderBy)) {
            return $qb;
        }
    
        $orderBy = $this->resolveOrderByForRelation($orderBy);
    
        // add order by clause
        if (false === strpos($orderBy, '.')) {
            $orderBy = 'tbl.' . $orderBy;
        }
        foreach (['theFile'] as $uploadField) {
            $orderBy = str_replace('tbl.' . $uploadField, 'tbl.' . $uploadField . 'FileName', $orderBy);
        }
        if (false !== strpos($orderBy, 'tbl.createdBy')) {
            $qb->addSelect('tblCreator')
               ->leftJoin('tbl.createdBy', 'tblCreator');
            $orderBy = str_replace('tbl.createdBy', 'tblCreator.uname', $orderBy);
        }
        if (false !== strpos($orderBy, 'tbl.updatedBy')) {
            $qb->addSelect('tblUpdater')
               ->leftJoin('tbl.updatedBy', 'tblUpdater');
            $orderBy = str_replace('tbl.updatedBy', 'tblUpdater.uname', $orderBy);
        }
        $qb->add('orderBy', $orderBy);
    
        return $qb;
    }
    
    /**
     * Resolves a given order by field to the corresponding relationship expression.
     *
     * @param string $orderBy
     *
     * @return string
     */
    protected function resolveOrderByForRelation($orderBy)
    {
        if (false !== mb_strpos($orderBy, ' ')) {
            list($orderBy, $direction) = explode(' ', $orderBy, 2);
        } else {
            $direction = 'ASC';
        }
    
        switch ($orderBy) {
            case 'message':
                $orderBy = 'tblMessage.title';
                break;
        }
    
        return $orderBy . ' ' . $direction;
    }

    /**
     * Retrieves Doctrine query from query builder.
     *
     * @param QueryBuilder $qb Query builder instance
     *
     * @return Query Query instance to be further processed
     */
    public function getQueryFromBuilder(QueryBuilder $qb)
    {
        $query = $qb->getQuery();
    
        return $query;
    }

    /**
     * Helper method to add join selections.
     *
     * @return string Enhancement for select clause
     */
    protected function addJoinsToSelection()
    {
        $selection = ', tblMessage';
    
        return $selection;
    }
    
    /**
     * Helper method to add joins to from clause.
     *
     * @param QueryBuilder $qb Query builder instance used to create the query
     *
     * @return QueryBuilder The query builder enriched by additional joins
     */
    protected function addJoinsToFrom(QueryBuilder $qb)
    {
        $qb->leftJoin('tbl.message', 'tblMessage');
    
        return $qb;
    }
}
