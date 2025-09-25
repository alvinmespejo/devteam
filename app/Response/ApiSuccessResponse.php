<?php

namespace App\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiSuccessResponse implements Responsable
{
    public function __construct(
        protected mixed $data = null,
        protected array $metadata = [],
        protected int $code = Response::HTTP_OK,
        protected array $headers = []
    ) {
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): \Symfony\Component\HttpFoundation\Response
    {
        $response = [];
        if ($this->data) {
            if (!is_array($this->data) && $this->data->collection) {
                $response = $this->data;
            } else {
                $response['data'] = $this->data;
            }
        }

        if ($this->metadata) {
            $response['metadata'] = $this->metadata;
        }

        return response()->json(
            $response,
            $this->code,
            $this->headers
        );
    }
}
