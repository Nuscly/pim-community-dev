<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Copy a multi select value attribute in other multi select value attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectValueCopier extends AbstractValueCopier
{
    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        AttributeUtility::validateLocale($fromAttribute, $fromLocale);
        AttributeUtility::validateScope($fromAttribute, $fromScope);
        AttributeUtility::validateLocale($toAttribute, $toLocale);
        AttributeUtility::validateScope($toAttribute, $toScope);

        foreach ($products as $product) {
            $this->copySingleValue(
                $fromAttribute,
                $toAttribute,
                $fromLocale,
                $toLocale,
                $fromScope,
                $toScope,
                $product
            );
        }
    }

    /**
     * Copy single value
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     * @param string             $product
     */
    protected function copySingleValue(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope,
        $product
    ) {
        $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }

            $this->removeOptions($toValue);
            $this->copyOptions($fromValue, $toValue);
        }
    }

    /**
     * Remove options from attribute
     *
     * @param ProductValueInterface $toValue
     */
    protected function removeOptions(ProductValueInterface $toValue)
    {
        foreach ($toValue->getOptions() as $attributeOption) {
            $toValue->removeOption($attributeOption);
        }
    }

    /**
     * Copy attribute options into a multi select attribute
     *
     * @param ProductValueInterface $fromValue
     * @param ProductValueInterface $toValue
     */
    protected function copyOptions(ProductValueInterface $fromValue, ProductValueInterface $toValue)
    {
        foreach ($fromValue->getOptions() as $attributeOption) {
            $toValue->addOption($attributeOption);
        }
    }
}
