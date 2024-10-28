<?php
// src/Controller/Bot/DeleteBot.php
namespace App\Controller\Bot;

use App\Repository\MembershipRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteBot extends BotController
{
    #[Route(
        '/api/bot/{id}',
        name: 'delete_bot',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager, MembershipRepository $membershipRepository): Response
    {
        $bot = $this->botRepository->findOneWithParams(array("id" => $id));

        if($bot){
            foreach($bot->getVoiceActors() as $voice_actor){
                $bot->removeVoiceActor($voice_actor);
            }
            foreach($bot->getAlts() as $alt){
                $bot->removeAlt($alt);
            }
            
            $membership = $membershipRepository->findOneBy(array("bot" => $bot));
            $entityManager->remove($membership);

            $entityManager->remove($bot);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Bot not found");
        }
    }
}