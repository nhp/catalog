<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Product\ProductAttributeList;

class ProductImage implements \JsonSerializable
{
    const FILE = 'file';
    const LABEL = 'label';
    
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    public function __construct(ProductAttributeList $attributeList)
    {
        $this->attributeList = $attributeList;
    }

    /**
     * @param array[] $imageAttributeArray
     * @return ProductImage
     */
    public static function fromArray(array $imageAttributeArray) : ProductImage
    {
        return new self(ProductAttributeList::fromArray($imageAttributeArray));
    }

    public function getFileName() : string
    {
        return $this->attributeList->getAttributesWithCode(self::FILE)[0]->getValue();
    }

    public function getLabel() : string
    {
        if (! $this->attributeList->hasAttribute(self::LABEL)) {
            return '';
        }
        return $this->attributeList->getAttributesWithCode(self::LABEL)[0]->getValue();
    }

    /**
     * @return array[]
     */
    public function jsonSerialize() : array
    {
        return $this->attributeList->jsonSerialize();
    }
}
