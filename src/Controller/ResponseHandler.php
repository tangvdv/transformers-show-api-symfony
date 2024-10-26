<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class ResponseHandler extends Controller
{
    public function createResponse(int $statusCode, mixed $data = [], array $normalizers = []): Response
    {
        if(count($normalizers) > 0){
            $serializer = new Serializer($normalizers);
            $data = $serializer->normalize($data, "json");
        }

        $arr = [
            "code" => $statusCode,
            "total" => $data["total"] ?? null,
            "limit" => $data["limit"] ?? null,
            "items" => $data["items"] ?? []
        ];

        if(!array_key_exists("total", $data) || !$data["total"]){
            unset($arr["total"]);
        }

        if(!array_key_exists("limit", $data) || !$data["limit"]){
            unset($arr["limit"]);
        }

        $json = $this->serializer->serialize($arr, 'json');

        return new Response($json, $statusCode, ['Content-Type', 'application/json']);
    }

    public function createErrorResponse(int $statusCode, string $message = ''): Response
    {
        $json = json_encode([
            "code" => $statusCode,
            "message" => $message
        ]);

        return new Response($json, $statusCode, ['Content-Type', 'application/json']);
    }

}