<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for the variant navigation data of the given entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantNavigationNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormat = ['internal_api'];

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var NormalizerInterface */
    private $entityWithFamilyVariantNormalizer;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $entityWithFamilyVariantNormalizer
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $entityWithFamilyVariantNormalizer
    ) {
        $this->localeRepository = $localeRepository;
        $this->entityWithFamilyVariantNormalizer = $entityWithFamilyVariantNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        if (!$entity instanceof ProductModelInterface && !$entity instanceof ProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" or "%s" expected, "%s" received',
                ProductModelInterface::class,
                ProductInterface::class,
                get_class($entity)
            ));
        }

        $navigationData = [];

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($entity->getFamilyVariant()->getVariantAttributeSets() as $attributeSet) {
            foreach ($localeCodes as $localeCode) {
                $labels = $attributeSet->getAxesLabels($localeCode);
                $navigationData[$attributeSet->getLevel()]['axes'][$localeCode] = implode(', ', $labels);
            }
        }

        $currentEntity = $entity;
        $reversedTree = [];
        $level = 0;

        do {
            $reversedTree[$level]['selected'] = $this->entityWithFamilyVariantNormalizer
                ->normalize($currentEntity, $format, $context);

            $currentEntity = $currentEntity->getParent();
            $level++;
        } while (null !== $currentEntity);

        $tree = array_reverse($reversedTree);
        $navigationData = array_replace_recursive($navigationData, $tree);
        ksort($navigationData);

        return $navigationData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithFamilyVariantInterface && in_array($format, $this->supportedFormat);
    }
}
