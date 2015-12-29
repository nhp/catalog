<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelations;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\SnippetKeyGenerator;

class BrandAndGenderProductRelations implements ProductRelations
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonSnippetKeyGenerator;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        Context $context
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->context = $context;
    }

    /**
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(ProductId $productId)
    {
        $key = $this->productJsonSnippetKeyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        $productData = json_decode($this->dataPoolReader->getSnippet($key), true);

        $criteria = $this->createCriteria($productData);
        $sortBy = $this->createSortOrderConfig();
        $rowsPerPage = 5;
        $pageNumber = 1;

        return $this->dataPoolReader->getProductIdsMatchingCriteria(
            $criteria,
            $this->context,
            $sortBy,
            $rowsPerPage,
            $pageNumber
        );
    }

    /**
     * @param mixed[] $productData
     * @return CompositeSearchCriterion
     */
    private function createCriteria(array $productData)
    {
        return CompositeSearchCriterion::createAnd(
            $this->getBrandCriteria($productData),
            $this->getGenderCriteria($productData),
            SearchCriterionNotEqual::create('product_id', $productData['product_id'])
        );
    }

    /**
     * @return SortOrderConfig
     */
    private function createSortOrderConfig()
    {
        return SortOrderConfig::create(
            AttributeCode::fromString('created_at'),
            SortOrderDirection::create(SortOrderDirection::ASC)
        );
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriterion
     */
    private function getBrandCriteria(array $productData)
    {
        return SearchCriterionEqual::create('brand', $productData['attributes']['brand']);
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriterion
     */
    private function getGenderCriteria(array $productData)
    {
        if (is_array($productData['attributes']['gender'])) {
            return CompositeSearchCriterion::createOr(...array_map(function ($gender) {
                return $this->createGenderCriterion($gender);
            }, $productData['attributes']['gender']));
        }

        return $this->createGenderCriterion($productData['attributes']['gender']);
    }

    /**
     * @param string $gender
     * @return SearchCriterion
     */
    private function createGenderCriterion($gender)
    {
        return SearchCriterionEqual::create('gender', $gender);
    }
}
