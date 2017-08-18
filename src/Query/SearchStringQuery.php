<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\SearchBundle\Query;

use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Argyrios Gounaris <agounaris@gmail.com>
 */
class SearchStringQuery extends Query
{
    /**
     * @var string
     */
    protected $searchTerm;

    /**
     * @var string
     */
    protected $searchParam;

    /**
     * @var bool
     */
    protected $dropdownFilterEnabled;

    /**
     * @var string
     */
    protected $remoteAddress;

    /**
     * @var TaxonInterface|null
     */
    private $taxon;

    /**
     * @param Request $request
     * @param bool    $dropDownFilterEnabled
     */
    public function __construct(Request $request, $dropDownFilterEnabled = false, TaxonInterface $taxon = null)
    {
        $requestBag = $request->isMethod('GET') ? $request->query : $request->request;

        $this->taxon = $taxon;
        $this->appliedFilters = $this->trimFilters($requestBag->get('criteria', []));
        $this->searchTerm = isset($this->appliedFilters['search']) ? $this->appliedFilters['search'] : null;
        $this->searchParam = $requestBag->get('search_param');
        $this->dropdownFilterEnabled = (bool) $dropDownFilterEnabled;
        $this->remoteAddress = $request->getClientIp();
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchParam
     */
    public function setSearchParam($searchParam)
    {
        $this->searchParam = $searchParam;
    }

    /**
     * @return string
     */
    public function getSearchParam()
    {
        return $this->searchParam;
    }

    /**
     * @return bool
     */
    public function isDropdownFilterEnabled()
    {
        return $this->dropdownFilterEnabled;
    }

    /**
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @return null|TaxonInterface
     */
    public function getTaxon()
    {
        return $this->taxon;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    protected function trimFilters(array $filters)
    {
        foreach ($filters as &$appliedFilter) {
            if (!is_array($appliedFilter)) {
                $appliedFilter = trim($appliedFilter);
                continue;
            }
            foreach ($appliedFilter as &$value) {
                $value = trim($value);
            }
        }

        return $filters;
    }
}
