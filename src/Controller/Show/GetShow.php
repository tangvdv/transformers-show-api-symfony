<?php
// src/Controller/Show/GetShow.php
namespace App\Controller\Show;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Show\ShowNormalizer;

class GetShow extends ShowController
{
    #[Route(
        '/api/show/{id}',
        name: 'get_show_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function getShowByID(int $id): Response
    {
        $show = $this->showRepository->findById($id);
        return $this->response($show);
    }

    #[Route(
        '/api/show/{name}',
        name: 'get_show_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    public function getShowByName(string $name): Response
    {
        $show = $this->showRepository->findByName($name);
        return $this->response($show);
    }

    private function response(mixed $show): Response
    {
        if($show){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $show], [new ShowNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }
    }
}