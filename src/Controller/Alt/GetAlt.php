<?php
// src/Controller/Alt/GetAlt.php
namespace App\Controller\Alt;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Alt\AltNormalizer;

class GetAlt extends AltController
{
    #[Route(
        '/api/alt/{id}',
        name: 'get_alt_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function getAltByID(int $id): Response
    {
        $alt = $this->altRepository->findOneWithParams(array("id" => $id));
        return $this->response($alt);
    }

    #[Route(
        '/api/alt/{name}',
        name: 'get_alt_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    public function getAltByName(string $name): Response
    {
        $alt = $this->altRepository->findOneWithParams(array("name" => $name));
        return $this->response($alt);
    }

    private function response(mixed $alt): Response
    {
        if($alt){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $alt], [new AltNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Alt not found");
        }
    }
}