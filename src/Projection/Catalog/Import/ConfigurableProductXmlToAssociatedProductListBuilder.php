<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Utils\XPathParser;

class ConfigurableProductXmlToAssociatedProductListBuilder
{
    /**
     * @var ProductXmlToProductBuilderLocator
     */
    private $productXmlToProductBuilderLocator;

    public function __construct(ProductXmlToProductBuilderLocator $productXmlToProductBuilderLocator)
    {
        $this->productXmlToProductBuilderLocator = $productXmlToProductBuilderLocator;
    }

    /**
     * @param XPathParser $parser
     * @return AssociatedProductListBuilder
     */
    public function createAssociatedProductListBuilder(XPathParser $parser)
    {
        $productBuilders = array_map(function ($associatedProductXml) {
            return $this->productXmlToProductBuilderLocator->createProductBuilderFromXml($associatedProductXml);
        }, $parser->getXmlNodesRawXmlArrayByXPath('/product/associated_products/product'));
        return new AssociatedProductListBuilder(...$productBuilders);
    }
}
