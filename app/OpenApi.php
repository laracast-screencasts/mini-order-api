<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Mini Order API",
    description: "API Documentation"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Local API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum"
)]
class OpenApi
{
}
