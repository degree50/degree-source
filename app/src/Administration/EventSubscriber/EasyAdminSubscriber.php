<?php

namespace App\Administration\EventSubscriber;

use App\Domain\User\Model\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['encodePassword'],
            BeforeEntityUpdatedEvent::class => ['encodePassword'],
        ];
    }

    /**
     * If the entity contained by the event is a user we encrypt its plain password
     * and write it to the user::password property.
     */
    public function encodePassword($event): void
    {
        $entity = $event->getEntityInstance();
        if ($entity instanceof User && $entity->getPlainPassword()) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $entity->getPlainPassword()));
        }
    }
}
