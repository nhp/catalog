<?php

namespace Brera\Product;

use Brera\InvalidSnippetKeyIdentifierException;
use Brera\Context\Context;
use Brera\PoCUrlPathKeyGenerator;
use Brera\SnippetKeyGenerator;

class ProductDetailViewSnippetKeyGenerator extends PoCUrlPathKeyGenerator implements SnippetKeyGenerator
{
    const KEY_PREFIX = 'product_detail_view';

    /**
     * @param mixed|ProductId $productId
     * @param Context $context
     * @throws InvalidSnippetKeyIdentifierException
     * @return string
     */
    public function getKeyForContext($productId, Context $context)
    {
        if (!($productId instanceof ProductId)) {
            throw new InvalidSnippetKeyIdentifierException(sprintf(
                'Expected instance of ProductId, but got "%s"',
                is_scalar($productId) ? $productId : gettype($productId)
            ));
        }

        return $this->getKeyForProductIdInContext($productId, $context);
    }

    /**
     * @param ProductId $productId
     * @param Context $context
     * @return string
     */
    private function getKeyForProductIdInContext(ProductId $productId, Context $context)
    {
        return sprintf('%s_%s_%s', self::KEY_PREFIX, $productId, $context->getId());
    }
}
