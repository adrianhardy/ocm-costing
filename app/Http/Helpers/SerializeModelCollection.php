<?php


namespace App\Http\Helpers;


use App\Fractal\DataArraySerializer;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class SerializeModelCollection implements \JsonSerializable
{
    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var TransformerAbstract
     */
    private $transformer;

    public function __construct(Collection $collection, TransformerAbstract $transformer)
    {
        $this->collection = $collection;
        $this->transformer = $transformer;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'items' => fractal(
                $this->collection,
                $this->transformer,
                new DataArraySerializer()
            )
        ];
    }


}
