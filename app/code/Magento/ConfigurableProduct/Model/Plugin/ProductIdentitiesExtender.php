<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Catalog\Model\Product\Type as ProductTypes;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 *  Extender of product identities for child of configurable products
 */
class ProductIdentitiesExtender implements ResetAfterRequestInterface
{
    /**
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $cacheParentIdsByChild = [];

    /**
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ConfigurableType $configurableType, ProductRepositoryInterface $productRepository)
    {
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
    }

    /**
     * Add parent identities to product identities
     *
     * @param Product $subject
     * @param array $identities
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIdentities(Product $subject, array $identities): array
    {
        if ($subject->getTypeId() !== ProductTypes::TYPE_SIMPLE) {
            return $identities;
        }
        $parentProductsIdentities = [];
        foreach ($this->getParentIdsByChild($subject->getId()) as $parentId) {
            $parentProduct = $this->productRepository->getById($parentId);
            $parentProductsIdentities[] = $parentProduct->getIdentities();
        }
        $identities = array_merge($identities, ...$parentProductsIdentities);

        return array_unique($identities);
    }

    /**
     * Get parent ids by child with cache use
     *
     * @param int $childId
     * @return array
     */
    private function getParentIdsByChild($childId)
    {
        if (!isset($this->cacheParentIdsByChild[$childId])) {
            $this->cacheParentIdsByChild[$childId] = $this->configurableType->getParentIdsByChild($childId);
        }

        return $this->cacheParentIdsByChild[$childId];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->cacheParentIdsByChild = [];
    }
}
