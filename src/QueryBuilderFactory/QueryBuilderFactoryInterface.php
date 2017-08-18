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

namespace Sylius\Bundle\SearchBundle\QueryBuilderFactory;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

/**
 * Interface QueryBuilderFactoryInterface.
 */
interface QueryBuilderFactoryInterface
{
    /**
     * @param TaxonInterface $taxon
     *
     * @return QueryBuilder
     */
    public function create(TaxonInterface $taxon = null);
}
