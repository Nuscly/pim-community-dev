<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Pim\Bundle\EnrichBundle\Form\Subscriber\FilterLocaleValueSubscriber;
use Pim\Bundle\EnrichBundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber;

/**
 * Localized collection type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizedCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(
            new FilterLocaleValueSubscriber(
                $options['currentLocale'],
                $options['comparisonLocale']
            )
        );
        $builder->addEventSubscriber(
            new FilterLocaleSpecificValueSubscriber(
                $options['currentLocale']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'currentLocale'    => null,
                'comparisonLocale' => null,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_localized_collection';
    }
}
