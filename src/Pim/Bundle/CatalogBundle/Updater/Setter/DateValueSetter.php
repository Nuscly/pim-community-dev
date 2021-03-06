<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Sets a date value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateValueSetter extends AbstractValueSetter
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ProductBuilder $productBuilder
     * @param array          $supportedTypes
     */
    public function __construct(ProductBuilder $productBuilder, array $supportedTypes)
    {
        $this->productBuilder = $productBuilder;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        $this->checkData($attribute, $data);

        foreach ($products as $product) {
            $this->setData($attribute, $product, $data, $locale, $scope);
        }
    }

    /**
     * Check if data are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_string($data)) {
            throw InvalidArgumentException::stringExpected($attribute->getCode(), 'setter', 'date');
        }

        $dateValues = explode('-', $data);

        if (
            count($dateValues) !== 3
            || (!is_numeric($dateValues[0]) || !is_numeric($dateValues[1]) || !is_numeric($dateValues[2]))
            || !checkdate($dateValues[1], $dateValues[2], $dateValues[0])
        ) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'a string with the format yyyy-mm-dd',
                'setter',
                'date'
            );
        }
    }

    /**
     * Set the data into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setData(AttributeInterface $attribute, ProductInterface $product, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }
        $value->setData(new \DateTime($data));
    }
}
