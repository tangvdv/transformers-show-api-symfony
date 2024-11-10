<?php
// src/Controller/Scene/UpdateScene.php
namespace App\Controller\Scene;

use App\Normalizer\Scene\SceneNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class UpdateScene extends SceneController
{
    #[Route(
        '/api/scenes/{id}',
        name: 'update_scene',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $scene = $this->sceneRepository->findOneBy(array("id" => $id));

        if(!$scene){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Scene not found");
        }
        $payload = $request->getPayload();
        $params = [
            "description" => [
                "value" =>  $payload->get("description"),
                "type" => "string",
                "nullable" => true,
                "method" => "setDescription"
            ],
            "start_time" => [
                "value" => $payload->get("start_time"),
                "type" => "string",
                "nullable" => true
            ],
            "end_time" => [
                "value" => $payload->get("end_time"),
                "type" => "string",
                "nullable" => true
            ]
        ];

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Parameter `{$key}` is missing");
                }
                else{
                    if(array_key_exists("default", $value)){
                        $value["value"] = $value["default"];
                    }
                }
            }
            else{
                if(gettype($value["value"]) != $value["type"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                }
                else{
                    if(array_key_exists("method", $value)){
                        $method = $value["method"];
                        $scene->$method($value["value"]);
                    }
                }
            }
        }

        $modified = false;
        if($params["start_time"]["value"] !== null){
            if(!$this->isTimeFormat($params["start_time"]["value"])){
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Paramater `start_time` is in incorrect time format, `hh:mm:ss` is needed");
            }
            else{
                $scene->setStartTime($params["start_time"]["value"]);
                $modified = true;
            }
        }

        if($params["end_time"]["value"] !== null){
            if($params["end_time"]["value"] && !$this->isTimeFormat($params["end_time"]["value"])){
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Paramater `end_time` is in incorrect time format, `hh:mm:ss` is needed");
            }
            else{
                $scene->setEndTime($params["end_time"]["value"]);
                $modified = true;
            }
        }

        if($modified) $scene->calculateDuration();
    
        $entityManager->persist($scene);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $scene], [new SceneNormalizer]);
    }

    private function isTimeFormat(string $time): bool 
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $time) === 1;
    }
}