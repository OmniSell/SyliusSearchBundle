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

namespace Sylius\Bundle\SearchBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemId', TextType::class, [
                'label' => 'sylius_search.form.search.item_id',
            ])
            ->add('entity', TextType::class, [
                'label' => 'sylius_search.form.search.entity',
            ])
            ->add('value', TextType::class, [
                'label' => 'sylius_search.form.search.value',
            ])
            ->add('tags', TextType::class, [
                'label' => 'sylius_search.form.search.tags',
            ])
        ;
    }
}
