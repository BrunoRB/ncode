<?php

namespace App\Models;

/**
 * Should be implemented by models that use MoneyCast
 */
interface FetchIsoCodeInterface
{
    /**
     * Gets the currency iso key for the model based on the casted attribute
     */
    public function getIsoCodeForKey($key): string;
}
