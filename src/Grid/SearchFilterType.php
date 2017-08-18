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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchFilterType.
 */
class SearchFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'tag' => null, // Grid parameter.
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_grid_filter_search';
    }
}
