<?php

namespace App\Administration\Controller;

use App\Domain\User\Model\User;
use App\Domain\User\Service\UserService;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserService $userService
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): array
    {
        $id = IdField::new('id');
        $name = TextField::new('email');

        /**
         * Encrypting is handled by
         * @see EasyAdminSubscriber::encodePassword()
         */
        $password = TextField::new('plain_password', Crud::PAGE_NEW === $pageName ? 'Password' : 'Change password')->setFormType(PasswordType::class);

        $isAdmin = BooleanField::new('isAdmin');
        $isStudent = BooleanField::new('isStudent');
        $isDozent = BooleanField::new('isDozent');

        switch ($pageName) {
            case Crud::PAGE_INDEX:
            case Crud::PAGE_DETAIL:
                return [$id, $name, $isAdmin, $isStudent, $isDozent];
            case Crud::PAGE_NEW:
            {
                $name->setRequired(true);
                $password->setRequired(true);
                return [$name, $password, $isAdmin, $isStudent, $isDozent];
            }
            case Crud::PAGE_EDIT:
                return [$name, $password, $isAdmin, $isStudent, $isDozent];
            default:
                return [];
        }
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->userService->removeUser($entityInstance);
        } else {
            parent::deleteEntity($entityManager, $entityInstance);
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $entityInstance->setIsVerified(true);
            parent::persistEntity($entityManager, $entityInstance);
        }
    }
}
