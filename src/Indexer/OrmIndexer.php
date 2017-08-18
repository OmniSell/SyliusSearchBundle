<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\SearchBundle\Indexer;

use Doctrine\DBAL\Schema\Index;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Sylius\Bundle\SearchBundle\Model\SearchIndex;
use Sylius\Bundle\SearchBundle\Model\SearchTagAwareInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Model\ToggleableInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Traversable;

/**
 * Orm Indexer
 *
 * @author Argyrios Gounaris <agounaris@gmail.com>
 */
class OrmIndexer implements IndexerInterface
{
    const TAG_SEPARATOR = '|';

    /**
     * @var array
     */
    private $config;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $output;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    const SPACER = ' ';

    /**
     * @param array                     $config
     * @param PropertyAccessorInterface $accessor
     */
    public function __construct(array $config, PropertyAccessorInterface $accessor)
    {
        $this->config = $config;
        $this->accessor = $accessor;
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param $message
     */
    private function addToOutput($message)
    {
        $this->output .= $message . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(EntityManager $em)
    {
        $this->setEntityManager($em);

        $this->addToOutput('Resetting index');

        $connection = $em->getConnection();

        $sm = $connection->getSchemaManager();

        $tableIndexes = array_keys($sm->listTableIndexes('sylius_search_index'));

        if (in_array('fulltext_search_idx', array_values($tableIndexes))) {
            $sm->dropIndex('fulltext_search_idx', 'sylius_search_index');
        }

        $dbPlatform = $connection->getDatabasePlatform();
        $connection->executeUpdate($dbPlatform->getTruncateTableSQL('sylius_search_index'));

        foreach ($this->config['orm_indexes'] as $index) {
            $this->createIndex($index['class'], $index['mappings']);
        }

        $index = new Index('fulltext_search_idx', ['value'], false, false, ['fulltext']);
        $sm->createIndex($index, 'sylius_search_index');

        return $this;
    }

    /**
     * @param string $entity
     * @param array  $fields
     *
     * @internal param $table
     */
    private function createIndex($entity, array $fields)
    {
        $fieldNames = array_keys($fields);
        foreach ($fieldNames as &$value) {
            $value = 'u.' . $value;
        }

        $this->addToOutput(sprintf('Populating index table with "%s" data', $entity));

        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from($entity, 'u');

        $result = $queryBuilder->getQuery()->getResult();
        foreach ($result as $element) {
            $this->createIndexForEntity($entity, $fields, $element);
        }

        $this->addToOutput(sprintf('Index created successfully (%d items indexed)', count($result)));
    }

    /**
     * @param array $entities
     */
    public function insertMany(array $entities)
    {
        foreach ($entities as $entity) {
            $class = get_class($entity);

            $indexName = $this->nestedValues($this->config['orm_indexes'], $class);

            $this->createIndexForEntity($class, $this->config['orm_indexes'][$indexName]['mappings'], $entity);
        }
    }

    /**
     * @param array $entities
     */
    public function removeMany(array $entities)
    {
        foreach ($entities as $entity) {
            $this->removeIndexForEntity($entity);
        }
    }

    /**
     * @param string $entityName
     * @param array  $fields
     * @param object $entity
     */
    public function createIndexForEntity($entityName, $fields, $entity)
    {
        $content = $this->compileSearchableContent($fields, $entity);

        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(SearchIndex::class, 'u')
            ->where('u.itemId = :item_id')
            ->andWhere('u.entity = :entity_namespace')
            ->setParameter(':item_id', $entity->getId())
            ->setParameter(':entity_namespace', get_class($entity));

        try {
            $searchIndex = $queryBuilder->getQuery()->getSingleResult();
            $searchIndex->setValue($content);
        } catch (NoResultException $e) {
            $searchIndex = new SearchIndex();
            $searchIndex->setItemId($entity->getId());
            $searchIndex->setEntity($entityName);
            $searchIndex->setValue($content);
        }

        $this->getTagsForElementAndSave($entity, $searchIndex);
    }

    /**
     * @param $entity
     */
    public function removeIndexForEntity($entity)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->delete(SearchIndex::class, 'u')
            ->where('u.itemId = :item_id')
            ->andWhere('u.entity = :entity_namespace')
            ->setParameter(':item_id', $entity['id'])
            ->setParameter(':entity_namespace', get_class($entity['entity']));

        $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param object      $element
     * @param SearchIndex $searchIndex
     */
    public function getTagsForElementAndSave($element, $searchIndex)
    {
        $data = $this->extractTags($element, $this->config['filters']['facets']);

        $searchIndex->setTags(serialize($data));
        if ($element instanceof SearchTagAwareInterface) {
            $element->setTagIndex($this->buildTagIndex($data));
            $this->em->persist($element);
        }

        $this->em->persist($searchIndex);
        $this->em->flush();
    }

    /**
     * @param array  $fields
     * @param object $element
     *
     * @return string
     */
    public function compileSearchableContent(array $fields, $element)
    {
        // TODO maybe I can use the property accessor here
        $content = '';
        foreach (array_keys(array_slice($fields, 1)) as $field) {
            $func = 'get' . ucfirst($field);

            if (method_exists($element, $func)) {
                $content .= $element->$func() . self::SPACER;
            }
        }

        return $content;
    }

    /**
     * Checks if the object is index'able or not.
     *
     * @param object $object
     *
     * @return bool
     */
    public function isObjectIndexable($object)
    {
        $class = get_class($object);

        foreach ($this->config['orm_indexes'] as $index) {
            if ($index['class'] === $class && 'orm' === $this->config['engine']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $node
     *
     * @return string
     */
    public function nestedValues($node)
    {
        if (is_array($node)) {
            $ret = '';
            foreach ($node as $key => $val) {
                $ret = $this->nestedValues($key, $val);
            }

            return $ret;
        }

        return $node;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function buildTagIndex($data)
    {
        $result = '';
        foreach ($data as $key => $values) {
            $values = is_array($values) ? $values : [$values];
            foreach ($values as $value) {
                $result .= sprintf('%s%s:%s%s', self::TAG_SEPARATOR, $key, (string)$value, self::TAG_SEPARATOR);
            }
        }

        return $result;
    }

    /**
     * @param object   $element
     * @param string[] $tags
     *
     * @return array
     */
    private function extractTags($element, $tags)
    {
        $result = [];
        foreach ($tags as $tag => $config) {
            if (!empty($config['taxon_root'])) {
                $taxons = $this->extractTaxons($element, $config['taxon_root'], $config['taxon_leaf_only']);
                if (count($taxons)) {
                    $result[$tag] = $taxons;
                }

                continue;
            }

            $values = $this->accessor->getValue($element, $tag);
            if (is_array($values) || $values instanceof Traversable) {
                foreach ($values as $value) {
                    $result[$tag][] = trim($value);
                }
            } elseif (null !== $values) {
                $result[$tag] = trim($values);
            }
        }

        return $result;
    }

    /**
     * @param object $element
     * @param string $root
     * @param bool   $leafOnly
     *
     * @return array
     */
    private function extractTaxons($element, $root, $leafOnly)
    {
        if (!$element instanceof ProductInterface) {
            return [];
        }

        $taxons = [];
        foreach ($element->getProductTaxons() as $productTaxon) {
            $taxon = $productTaxon->getTaxon();
            if ($taxon->isRoot()
                || $root !== $taxon->getRoot()->getCode()
                || ($leafOnly && $taxon->getChildren()->count())
            ) {
                continue;
            }

            if ($taxon instanceof ToggleableInterface && !$taxon->isEnabled()) {
                continue;
            }

            $taxons[] = trim($taxon->getName());
        }

        return $taxons;
    }
}
