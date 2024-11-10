<?php
// src/Controller/Application/CreateApplication.php
namespace App\Controller\Application;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use App\Normalizer\Application\CreateApplicationNormalizer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;
use App\Repository\UserRepository;

class CreateApplication extends ApplicationController
{
    #[Route(
        '/api/applications',
        name: 'create_application',
        methods: ['POST']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository): Response
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        $user = $userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));

        if(!$user){
            return $this->responseHandler->createErrorResponse(Response::HTTP_FORBIDDEN, "Forbidden");
        }

        $payload = $request->getPayload();

        $params = [
            "name" => [
                "value" => $payload->get("name"),
                "type" => "string",
                "nullable" => false
            ],
            "description" => [
                "value" =>  $payload->get("description"),
                "type" => "string",
                "nullable" => true
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
            }
        }

        if($this->applicationRepository->findOneBy(array("user" => $user, "name" => $params["name"]["value"]))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "You already have an application with this name");
        }

        $application = new Application();

        if($params["description"]["value"] !== null){
            $application->setDescription($params["description"]["value"]);    
        }

        $clientId = bin2hex(random_bytes(16));
        $clientSecret = bin2hex(random_bytes(32));
        
        $application->setApplicationName($params["name"]["value"])
            ->setClientId($clientId)
            ->setPlainClientSecret($clientSecret);
        $application->setUser($user);

        $entityManager->persist($application);
        $entityManager->flush();
        
        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $application], [new CreateApplicationNormalizer]);
    }
}