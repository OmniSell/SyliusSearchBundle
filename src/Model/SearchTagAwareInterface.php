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

namespace Sylius\Bundle\SearchBundle\Model;

/**
 * SearchTagAwareInterface interface.
 *
 * @author Argyrios Gounaris <agounaris@gmail.com>
 */
interface SearchTagAwareInterface
{
    /**
     * Set tag index.
     *
     * @param string $itemId
     *
     * @return SearchIndexInterface
     */
    public function setTagIndex($tagIndex);

    /**
     * Get tag index.
     *
     * @return string
     */
    public function getTagIndex();
}
