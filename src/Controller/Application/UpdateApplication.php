<?php
// src/Controller/Application/UpdateApplication.php
namespace App\Controller\Application;

use App\Normalizer\Application\ApplicationNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class UpdateApplication extends ApplicationController
{
    #[Route(
        '/api/applications/{id}',
        name: 'update_application',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository, Request $request): Response
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        $user = $userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));
        if(!$user){
            return $this->responseHandler->createErrorResponse(Response::HTTP_FORBIDDEN, "Forbidden");
        }

        $application = $this->applicationRepository->findOneBy(array("id" => $id, "user" => $user));

        if($application){
            $payload = $request->getPayload();
            $modified = false;

            $params = [
                "name" => [
                    "value" => $payload->get("name"),
                    "type" => "string",
                    "nullable" => true
                ],
                "description" => [
                    "value" =>  $payload->get("description"),
                    "type" => "string",
                    "nullable" => true,
                    "method" => "setDescription"
                ]
            ];

            foreach($params as $key => &$value){
                if($value["value"] === null){
                    if(!$value["nullable"]){
                        return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND,"Parameter `{$key}` is missing");
                    }
                }
                else{
                    if(gettype($value["value"]) != $value["type"]){
                        return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
                    }
                    else{
                        if(array_key_exists("method", $value)){
                            $method = $value["method"];
                            $application->$method($value["value"]);
                            $modified = true;
                        }
                    }
                }
            }

            if($params["name"]["value"] !== null){
                if($this->applicationRepository->findOneBy(array("user" => $user, "name" => $params["name"]["value"]))){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "You already have an application with this name");
                }
                $application->setName($params["name"]["value"]);
                $modified = true;
            }

            if($modified){
                $application->setUpdatedAt(new DateTime());
            }

            $entityManager->persist($application);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $application], [new ApplicationNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Application not found");
        }
    }
}