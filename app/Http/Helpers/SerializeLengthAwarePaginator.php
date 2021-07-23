<?php


namespace App\Http\Helpers;


use App\Fractal\DataArraySerializer;
use App\Transformers\ShipmentTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\TransformerAbstract;

class SerializeLengthAwarePaginator implements \JsonSerializable
{
    /**
     * @var LengthAwarePaginator
     */
    private $paginator;
    /**
     * @var TransformerAbstract
     */
    private $transformerAbstract;

    /**
     * SerializeLengthAwarePaginator constructor.
     * @param LengthAwarePaginator $paginator
     * @param TransformerAbstract $transformerAbstract
     */
    public function __construct (
        LengthAwarePaginator  $paginator,
        TransformerAbstract $transformerAbstract
    ) {
        $this->paginator = $paginator;
        $this->transformerAbstract = $transformerAbstract;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'items' => fractal()
                ->collection($this->paginator->items())
                ->transformWith($this->transformerAbstract)
                ->serializeWith(new DataArraySerializer()),
            'current_page' => $this->paginator->currentPage(),
            'total' => $this->paginator->total(),
            'per_page' => $this->paginator->perPage()
        ];
    }
}
