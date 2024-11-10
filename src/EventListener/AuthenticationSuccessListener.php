<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Log;
use App\Entity\User;
use App\Entity\Stat;
use App\Entity\Application;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationSuccessListener
{
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    private Request $request;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager) 
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->request = $this->requestStack->getCurrentRequest();
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        if($event->getUser() instanceof User){
            $this->createUserLog($event->getUser());
        }
        else if($event->getUser() instanceof Application){
            $this->createApplicationLog($event->getUser());
        }
    }

    private function createUserLog(User $user){
        try{
            $log = new Log();

            if($this->request->headers->has("user-agent")){
                $log->setUserAgent($this->request->headers->get("user-agent"));
            }
            $log->setEndpoint($this->request->getRequestUri());
            $log->setMethod($this->request->getMethod());
            $log->setRequestAt(new \DateTimeImmutable());
            $log->setUser($user);

            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    private function createApplicationLog(Application $application){
        try{
            $stat = new Stat();

            $stat->setEndpoint($this->request->getRequestUri());
            $stat->setMethod($this->request->getMethod());
            $stat->setRequestAt(new \DateTimeImmutable());
            $stat->setApplication($application);

            $this->entityManager->persist($stat);
            $this->entityManager->flush();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }
}