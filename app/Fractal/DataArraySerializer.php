<?php


namespace App\Fractal;

/**
 * Strip 'data' from all the serializations. Although this is
 * in theory "good" because it lets us namespace out meta data
 * it means that Vue's parsing of the objects is all wrong.ZZ
 */
class DataArraySerializer extends \League\Fractal\Serializer\DataArraySerializer
{
    /**
     * @param string $resourceKey
     * @param array $data
     * @return array|array[]
     */
    public function collection($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * @param string $resourceKey
     * @param array $data
     * @return array|array[]
     */
    public function item($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * @return array|array[]
     */
    public function null()
    {
        return [];
    }


}
