<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\SearchBundle\Controller;

use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\SearchBundle\Query\SearchStringQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search landing page controller.
 *
 * @author Argyrios Gounaris <agounaris@gmail.com>
 */
class SearchController extends ResourceController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function filterAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $facetGroup = $request->get('facet_group', 'all_set');
        $taxonSlug = $request->get('slug');
        $locale = $this->container->get('sylius.context.locale')->getLocaleCode();
        $taxon = $taxonSlug ? $this->container->get('sylius.repository.taxon')->findOneBySlug($taxonSlug, $locale) : null;

        $finder = $this->container->get('sylius_search.finder')
            ->addTargetType('product')
            ->setFacetGroup($facetGroup)
            ->find(
                new SearchStringQuery(
                    $request,
                    $this->container->getParameter('sylius_search.pre_search_filter.enabled'),
                    $taxon
                )
            );

        $searchConfig = $this->container->getParameter('sylius_search.config');

        $view = View::create()
            ->setTemplate('SyliusSearchBundle::filter_form.html.twig')
            ->setData(
                [
                    'facets' => $finder->getFacets(),
                    'facetTags' => $searchConfig['filters']['facets'],
                    'filters' => $finder->getFilters(),
                    'searchTerm' => $this->container->get('sylius_search.request_handler')->getQuery(),
                    'searchParam' => $this->container->get('sylius_search.request_handler')->getSearchParam(),
                    'requestUri' => null,
                    'requestMethod' => $this->container->getParameter('sylius_search.request.method'),
                ]
            );

        return $this->viewHandler->handle($configuration, $view);
    }
}
