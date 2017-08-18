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

namespace Sylius\Bundle\SearchBundle\Accessor;

use Sylius\Component\Product\Model\ProductInterface;
use Symfony\Component\PropertyAccess\Exception;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class ProductAccessor.
 */
class ProductAccessor extends PropertyAccessor
{
    /**
     * {@inheritdoc}
     */
    public function getValue($product, $propertyPath)
    {
        try {
            return parent::getValue($product, $propertyPath);
        } catch (Exception\NoSuchPropertyException $e) {
            $tags = [];
            if (!$product instanceof ProductInterface) {
                return $tags;
            }

            $propertyPath = strtolower((string)$propertyPath);
            foreach ($product->getVariants() as $variant) {
                foreach ($variant->getOptionValues() as $option) {
                    if ($propertyPath === strtolower($option->getOptionCode())) {
                        $tags[] = $option->getValue();
                    }
                }
            }

            foreach ($product->getAttributes() as $attribute) {
                if ($propertyPath === strtolower($attribute->getCode())) {
                    $tags[] = $attribute->getValue();
                }
            }

            return array_values(array_unique($tags));
        }
    }
}
