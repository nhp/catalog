<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Price\Price;

class FilterNavigationPriceRangesBuilder
{
    /**
     * @return FacetFilterRange[]
     */
    public static function getPriceRanges() : array
    {
        $base = pow(10, Price::DEFAULT_DECIMAL_PLACES);
        $rangeStep = 20 * $base;
        $rangesTo = 500 * $base;

        $priceRanges = [FacetFilterRange::create(null, $rangeStep - 1)];
        for ($i = $rangeStep; $i < $rangesTo; $i += $rangeStep) {
            $priceRanges[] = FacetFilterRange::create($i, $i + $rangeStep - 1);
        }
        $priceRanges[] = FacetFilterRange::create($rangesTo, null);

        return $priceRanges;
    }
}
