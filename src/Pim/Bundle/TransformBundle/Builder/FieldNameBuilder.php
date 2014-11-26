<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldNameBuilder
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $assocTypeClass;

    /** @var string */
    protected $attributeClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $assocTypeClass
     * @param string          $attributeClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $assocTypeClass, $attributeClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->assocTypeClass  = $assocTypeClass;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function getAssociationFieldNames()
    {
        $fieldNames = [];
        $assocTypes = $this->getRepository($this->assocTypeClass)->findAll();
        foreach ($assocTypes as $assocType) {
            $fieldNames[] = $assocType->getCode() .'-groups';
            $fieldNames[] = $assocType->getCode() .'-products';
        }

        return $fieldNames;
    }

    /**
     * Extract attribute field name informations with attribute code, locale code, scope code
     * and optionally price currency
     *
     * Returned array like:
     * [
     *     "attribute"   => AbstractAttribute,
     *     "locale_code" => <locale_code>|null,
     *     "scope_code"  => <scope_code>|null,
     *     "price_currency" => <currency_code> // this key is optional
     * ]
     *
     * Return null if the field name does not match an attribute.
     *
     * @param string $fieldName
     *
     * @return array|null
     */
    public function extractAttributeFieldNameInfos($fieldName)
    {
        $explodedFieldName = explode("-", $fieldName);
        $attributeCode = $explodedFieldName[0];

        $attribute = $this->getRepository($this->attributeClass)->findByReference($attributeCode);

        if (null !== $attribute) {
            return $this->extractAttributeInfos($attribute, $explodedFieldName);
        }

        return null;
    }

    /**
     * Extract information from an attribute and exploded field name
     * This method is used from extractAttributeFieldNameInfo and can be redefine to add new rules
     *
     * @param AbstractAttribute $attribute
     * @param array             $explodedFieldName
     *
     * @return array
     */
    protected function extractAttributeInfos(AbstractAttribute $attribute, array $explodedFieldName)
    {
        $this->checkForLocaleSpecificValue($attribute, $explodedFieldName);

        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = $explodedFieldName[2];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[3] : null;
        } elseif ($attribute->isLocalizable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } elseif ($attribute->isScopable()) {
            $localeCode = null;
            $scopeCode  = $explodedFieldName[1];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } else {
            $localeCode = null;
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[1] : null;
        }

        $priceArray = (null === $priceCurrency) ? [] : ['price_currency' => $priceCurrency];

        return [
            'attribute'   => $attribute,
            'locale_code' => $localeCode,
            'scope_code'  => $scopeCode,
        ] + $priceArray;
    }

    /**
     * Extract field name informations from a potential association field name
     *
     * Returned array like:
     * [
     *     "assoc_type_code"   => <assoc_type_code>,
     *     "part" => "groups"|"products",
     * ]
     *
     * @param string $fieldName
     *
     * @return string[]|null
     */
    public function extractAssociationFieldNameInfos($fieldName)
    {
        $matches = [];
        $regex = '/^([a-zA-Z0-9_]+)-(groups|products)$/';
        if (preg_match($regex, $fieldName, $matches)) {
            return ['assoc_type_code' => $matches[1], 'part' => $matches[2]];
        }
    }

    /**
     * @param string $entityClass
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->managerRegistry->getRepository($entityClass);
    }

    /**
     * Check if provided locales for an locale specific attribute exist
     *
     * @param AbstractAttribute $attribute
     * @param array             $explodedFieldName
     */
    protected function checkForLocaleSpecificValue(AbstractAttribute $attribute, array $explodedFieldName)
    {
        $availableLocales = [];

        if ($attribute->isLocaleSpecific()) {
            foreach ($attribute->getAvailableLocales() as $locale) {
                $availableLocales[] = $locale->getCode();
            }
            if (!in_array($explodedFieldName[1], $availableLocales)) {
                throw new \LogicException(
                    sprintf(
                        'The provided specific locale "%s" does not exist for "%s" attribute ',
                        $explodedFieldName[1],
                        $explodedFieldName[0]
                    )
                );
            }
        }
    }
}
