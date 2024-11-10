<?php

namespace App\EntityListener;

use App\Entity\Application;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApplicationListener
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    public function prePersist(Application $application)
    {
        $this->encodePassword($application);
    }

    public function preUpdate(Application $application)
    {
        $this->encodePassword($application);
    }

    /**
     *  Encode client secret based on plain client secret
     * 
     *  @param Application $application
     *  @return void
     */
    public function encodePassword(Application $application)
    {
        if($application->getPlainClientSecret() === null) return;

        $application->setPassword(
            $this->hasher->hashPassword(
                $application,
                $application->getPlainClientSecret()
            )
        );
    }
}