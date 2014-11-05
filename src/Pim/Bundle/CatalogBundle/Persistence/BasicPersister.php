<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Synchronize product with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BasicPersister implements ProductPersister
{
    /** @var ObjectManager */
    protected $manager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param ManagerRegistry     $registry
     * @param CompletenessManager $completenessManager
     * @param VersionManager      $versionManager
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        VersionManager $versionManager
    ) {
        $this->registry            = $registry;
        $this->completenessManager = $completenessManager;
        $this->versionManager      = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $manager = $this->registry->getManagerForClass(get_class($product));
        $manager->persist($product);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if ($options['versioning']) {
            $changeset = [];
            $versions = $this->versionManager->buildVersion($product, $changeset);
            foreach ($versions as $version) {
                $manager->persist($version);
            }
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }
}
