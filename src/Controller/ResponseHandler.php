<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class ResponseHandler extends Controller
{
    public function createResponse(int $statusCode, mixed $data, array $normalizers = []): Response
    {
        if(count($normalizers) > 0){
            $serializer = new Serializer($normalizers);
            $data = $serializer->normalize($data, "json");
        }

        $json = json_encode([
            "StatusCode" => $statusCode,
            "Data" => [$this->serializer->serialize($data, "json")]
        ]);

        return new Response($json, $statusCode, ['Content-Type', 'application/json']);
    }

    public function createErrorResponse(int $statusCode, string $message = ''): Response
    {
        $json = json_encode([
            "StatusCode" => $statusCode,
            "Message" => $message
        ]);

        return new Response($json, $statusCode, ['Content-Type', 'application/json']);
    }

}