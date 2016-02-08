<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductListingTitleSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_title';

    const TITLE_ATTRIBUTE_CODE = 'meta_title';

    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(SnippetKeyGenerator $keyGenerator, ContextBuilder $contextBuilder)
    {
        $this->keyGenerator = $keyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        if (!$productListing->hasAttribute(self::TITLE_ATTRIBUTE_CODE)) {
            return [];
        }

        $context = $this->contextBuilder->createContext($productListing->getContextData());
        $contextData = [PageMetaInfoSnippetContent::URL_KEY => $productListing->getUrlKey()];
        $key = $this->keyGenerator->getKeyForContext($context, $contextData);
        $content = $productListing->getAttributeValueByCode(self::TITLE_ATTRIBUTE_CODE);

        return [Snippet::create($key, $content)];
    }
}
