<?php
// src/Controller/Show/DeleteShow.php
namespace App\Controller\Show;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteShow extends ShowController
{
    #[Route(
        '/api/show/{id}',
        name: 'delete_show',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $show = $this->showRepository->findOneBy(array("id" => $id));
        
        if($show){
            foreach($show->getCreators() as $creator){
                $show->removeCreator($creator);
            }
            $entityManager->persist($show);

            $entityManager->persist($show);
            foreach($show->getHumans() as $human){
                $human->removeShow();
                $entityManager->persist($human);
            }
            foreach($show->getVoiceLines() as $voice_line){
                $voice_line->removeShow();
                $entityManager->persist($voice_line);
            }
            foreach($show->getConceptArts() as $concept_art){
                $concept_art->removeShow();
                $entityManager->persist($concept_art);
            }
            foreach($show->getArtefacts() as $artefact){
                $artefact->removeShow();
                $entityManager->persist($artefact);
            }
            foreach($show->getBots() as $bot){
                $bot->removeShow();
                $entityManager->persist($bot);
            }

            $entityManager->remove($show);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }
    }
}