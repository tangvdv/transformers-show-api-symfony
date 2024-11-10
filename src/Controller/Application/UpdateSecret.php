<?php
// src/Controller/Application/UpdateSecret.php
namespace App\Controller\Application;

use App\Normalizer\Application\CreateApplicationNormalizer;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\UserRepository;
use DateTime;

class UpdateSecret extends ApplicationController
{
    #[Route(
        '/api/applications/secret/{id}',
        name: 'update_application_secret',
        methods: ['PUT'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository): Response
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        $user = $userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));
        if(!$user){
            return $this->responseHandler->createErrorResponse(Response::HTTP_FORBIDDEN, "Forbidden");
        }

        $application = $this->applicationRepository->findOneBy(array("id" => $id, "user" => $user));

        if($application){
            $clientSecret = bin2hex(random_bytes(32));
        
            $application->setPlainClientSecret($clientSecret)
                ->setUpdatedAt(new DateTime());

            $entityManager->persist($application);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $application], [new CreateApplicationNormalizer()]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Application not found");
        }
    }
}