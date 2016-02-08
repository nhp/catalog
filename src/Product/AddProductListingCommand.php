<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

class AddProductListingCommand implements Command
{
    /**
     * @var ProductListing
     */
    private $productListing;

    public function __construct(ProductListing $productListing)
    {
        $this->productListing = $productListing;
    }

    /**
     * @return ProductListing
     */
    public function getProductListing()
    {
        return $this->productListing;
    }
}
