<?php

namespace App\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Throwable;

class ApiErrorResponse implements Responsable
{
    public function __construct(
        protected Throwable $e,
        protected string $message = 'An error occured while processing request. Please try again!',
        protected int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        protected array $headers = []
    ) {
    }

    public function toResponse($request): \Symfony\Component\HttpFoundation\Response
    {
        $response = ['message' => $this->message];
        // Detailed error reporting response for local development only.
        if ($this->e && config('app.debug') &&  !in_array(config('app.name'), ['production', 'staging'])) {
            $response = [
                'message' => $this->e->getMessage(),
                'file' => $this->e->getFile(),
                'line' => $this->e->getLine(),
            ];
        }

        return response()->json(
            ['error' => $response],
            $this->code,
            $this->headers
        );
    }
}
