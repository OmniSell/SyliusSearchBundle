<?php

/*
 * @copyright C UAB NFQ Technologies
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

namespace Sylius\Bundle\SearchBundle\Model\Traits;

trait SearchTagAwareTrait
{
    /**
     * @var string
     */
    private $tagIndex;

    /**
     * @return string
     */
    public function getTagIndex()
    {
        return $this->tagIndex;
    }

    /**
     * @param string $tagIndex
     */
    public function setTagIndex($tagIndex)
    {
        $this->tagIndex = $tagIndex;
    }
}
