<?php
namespace App\Listener;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface{

private $hasher;

public function __construct(UserPasswordHasherInterface $hasher)
{
$this->hasher = $hasher;
}

public static function getSubscribedEvents()
{
return [
BeforeEntityUpdatedEvent::class => ['setUpdatedAt'],
BeforeEntityPersistedEvent::class => ['setCreatedAt'],
];
}

public function setUpdatedAt(BeforeEntityUpdatedEvent $event){

$entity = $event->getEntityInstance();

if(!($entity instanceof Post) && !($entity instanceof User) && !($entity instanceof Comment)){
return;
}

$entity->setUpdatedAt(new DateTimeImmutable());

}

public function setCreatedAt(BeforeEntityPersistedEvent $event){

$entity = $event->getEntityInstance();

if(!($entity instanceof Post) && !($entity instanceof User) && !($entity instanceof Comment)){
return;
}

if ($entity instanceof User){
$entity->setPassword(
$this->hasher->hashPassword($entity, $entity->getPassword())
);
}

$entity->setCreatedAt(new DateTimeImmutable());

}

}