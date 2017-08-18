<?php

/**
 * @copyright C UAB NFQ Technologies 2016
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 */

namespace Sylius\Bundle\SearchBundle\Grid;

use Sylius\Bundle\SearchBundle\Indexer\OrmIndexer;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

/**
 * Class SearchFilter.
 */
class SearchFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options)
    {
        $tag = isset($options['tag']) ? $options['tag'] : $name;

        $query = $this->buildTagQuery($tag, $data, $dataSource->getExpressionBuilder());

        $dataSource->restrict($query);
    }

    /**
     * @param string                     $tag
     * @param string[]                   $values
     * @param ExpressionBuilderInterface $builder
     *
     * @return ExpressionBuilderInterface
     */
    private function buildTagQuery($tag, $values, $builder)
    {
        $or = [];
        foreach ($values as $value) {
            $or[] = $builder->like(
                'o.tagIndex',
                sprintf(
                    '%%%s%s:%s%s%%',
                    OrmIndexer::TAG_SEPARATOR,
                    $tag,
                    trim($value),
                    OrmIndexer::TAG_SEPARATOR
                )
            );
        }

        return $builder->orX(...$or);
    }
}
