<?php


namespace App\Domain;


class SortedArticleNumberGenerator
{

    /**
     * Modify the article number to give us something to sort by. This field will be used
     * in the background to provide more natural sorting because MySQL does not support it
     * out of the box.
     *
     * 1. Migration to add 'sortable_article_number'
     * 2. code:models (don't add this field to fillable)
     * 3. Observer needs to use this class to populate sortable_article_number
     * 4. Trick RequestCriteria into using 'sortable_article_number' insteda of 'article_number' using $paginatedFetch's mapping
     *
     * @param $articleNumber
     * @return mixed|string
     */
    public static function convert($articleNumber)
    {
        $patterns = [
            '/^([a-z]{2,4})(\d+)(.*)$/i'
        ];

        if (preg_match($patterns[0], $articleNumber, $matches)) {
            $numbers = str_pad($matches[2], 8, '0', STR_PAD_LEFT);
            return $matches[1] . $numbers . $matches[3];
        }

        return $articleNumber;
    }
}