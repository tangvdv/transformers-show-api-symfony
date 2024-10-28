<?php
// src/Controller/User/Signup.php
namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use App\Normalizer\User\UserNormalizer;

class Signup extends UserController
{
    #[Route(
        '/auth/signup',
        name: 'signup',
        methods: ['POST']
    )]
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payload = $request->getPayload();

        $params = [
            "username" => [
                "value" => $payload->get("username"),
                "type" => "string",
                "nullable" => false
            ],
            "email" => [
                "value" =>  $payload->get("email"),
                "type" => "string",
                "nullable" => false
            ],
            "password" => [
                "value" =>  $payload->get("password"),
                "type" => "string",
                "nullable" => false
            ]
        ];

        foreach($params as $key => &$value){
            if($value["value"] === null){
                if(!$value["nullable"]){
                    return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND,"Parameter `{$key}` is missing");
                }
            }

            if(gettype($value["value"]) != $value["type"]){
                return $this->responseHandler->createErrorResponse(Response::HTTP_BAD_REQUEST, "Parameter `{$key}` is in incorrect type format, `{$value["type"]}` is needed");
            }
        }

        $email = filter_var($params["email"]["value"], FILTER_VALIDATE_EMAIL);

        if($this->userRepository->findOneBy(array("email" => $params["email"]))){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "A user with this email already exist");
        }

        $uuid = Uuid::v7();

        $user = new User();
        $user->setUuid($uuid)
            ->setUsername($params["username"]["value"])
            ->setEmail($email)
            ->setPlainPassword($params["password"]["value"]);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->responseHandler->createResponse(Response::HTTP_CREATED, ["items" => $user], [new UserNormalizer]);
    }
}