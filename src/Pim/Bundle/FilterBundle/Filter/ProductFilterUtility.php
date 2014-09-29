<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Product filter utility
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFilterUtility extends BaseFilterUtility
{
    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ProductManager $manager
     */
    public function __construct(ProductManager $manager)
    {
        $this->productManager = $manager;
    }

    /**
     * @return ProductManager
     */
    public function getProductManager()
    {
        return $this->productManager;
    }

    /**
     * @return ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->productManager->getProductRepository();
    }

    /**
     * TODO : could be drop if we change choice filter
     *
     * @param string $code
     *
     * @return AbstractAttribute
     */
    public function getAttribute($code)
    {
        $attributeRepo = $this->productManager->getAttributeRepository();
        $attribute     = $attributeRepo->findOneByCode($code);

        return $attribute;
    }

    /**
     * TODO : could be renamed, caution to BC break
     *
     * Applies filter to query by attribute
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param mixed                            $value
     * @param string                           $operator
     */
    public function applyFilterByAttribute(FilterDatasourceAdapterInterface $ds, $field, $value, $operator)
    {
        $productQueryBuilder = $this->getProductRepository()->getProductQueryBuilder($ds->getQueryBuilder());
        $productQueryBuilder->addFilter($field, $operator, $value);
    }
}
