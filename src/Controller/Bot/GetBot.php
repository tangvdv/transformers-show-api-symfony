<?php
// src/Controller/Bot/GetBot.php
namespace App\Controller\Bot;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Bot\BotNormalizer;
use Symfony\Component\HttpFoundation\Request;

class GetBot extends BotController
{
    #[Route(
        '/api/bots/{id}',
        name: 'get_bot_id',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function getBotByID(int $id): Response
    {
        $bot = $this->botRepository->findOneWithParams(array("id" => $id));
        return $this->response($bot);
    }

    #[Route(
        '/api/bots/{name}',
        name: 'get_bot_name',
        methods: ['GET'],
        requirements: ['name' => '\w+']
    )]
    public function getBotByName(string $name, Request $request): Response
    {
        $show = null;
        if($request->query->get('show') !== null && !empty($request->query->get('show'))){
            $show = filter_var($request->query->get('show'), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        $bot = $this->botRepository->findOneWithParams(array("name" => $name, "show" => $show));
        return $this->response($bot);
    }

    private function response(mixed $bot): Response
    {
        if($bot){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $bot], [new BotNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }
    }
}