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

    public function toResponse($request): \Symfony\Component\HttpFoundation\Response
    {
        $response = [];
        if ($this->data) {
            $response['data'] = $this->data;
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
