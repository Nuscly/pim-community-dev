<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeFilterInterface extends FilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $operator  the used operator
     * @param string|array       $value     the value(s) to filter
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     *
     * @return AttributeFilterInterface
     */
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, $locale = null, $scope = null);

    /**
     * This filter supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
