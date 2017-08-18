<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\SearchBundle\Request;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Request handling
 *
 * @author Argyrios Gounaris <agounaris@gmail.com>
 */
class RequestHandler
{
    /**
     * @var ParameterBag
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return ParameterBag
     */
    private function getRequest()
    {
        $current = $this->requestStack->getCurrentRequest();

        return $current->isMethod('GET') ? $current->query : $current->request;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->getRequest()->get('page', 1);
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        $criteria = $this->getRequest()->get('criteria', []);

        return isset($criteria['search']) ? $criteria['search'] : null;
    }

    /**
     * @return mixed
     */
    public function getSearchParam()
    {
        return $this->getRequest()->get('search_param');
    }
}
