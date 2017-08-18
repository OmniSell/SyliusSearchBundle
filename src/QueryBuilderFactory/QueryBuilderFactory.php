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

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

/**
 * Class QueryBuilderFactory.
 */
class QueryBuilderFactory implements QueryBuilderFactoryInterface
{
    /**
     * @var object
     */
    private $repository;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var LocaleContextInterface
     */
    private $localeContext;

    /**
     * @var string
     */
    private $taxonMethod;

    /**
     * @var string
     */
    private $searchMethod;

    /**
     * QueryBuilderFactory constructor.
     *
     * @param object                  $repository
     * @param string                  $taxonMethod
     * @param string                  $searchMethod
     * @param ChannelContextInterface $channelContext
     * @param LocaleContextInterface  $localeContext
     */
    public function __construct(
        $repository,
        $taxonMethod,
        $searchMethod,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext
    ) {
        $this->repository = $repository;
        $this->taxonMethod = $taxonMethod;
        $this->searchMethod = $searchMethod;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TaxonInterface $taxon = null)
    {
        if (null === $taxon) {
            return $this->repository->{$this->searchMethod}(
                $this->channelContext->getChannel(),
                $this->localeContext->getLocaleCode()
            );
        }

        return $this->repository->{$this->taxonMethod}(
            $this->channelContext->getChannel(),
            $taxon->getSlug(),
            $this->localeContext->getLocaleCode()
        );
    }
}
