<?php

namespace App\Domain\Course\Form;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\User\Model\User;
use App\Domain\User\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseMembersType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Course $course */
        $course = $options['data'];
        $courseRoles = $course->getCourseRoles();

        $userChoices = array_filter($this->userRepository->findBy([], array('email' => 'ASC')), function (User $user) use ($courseRoles) {
            // only students
            if (!$user->isStudent()) {
                return false;
            }
            // skip users that are already in the course
            $exists = $courseRoles->exists(fn($i, CourseRole $courseRole) => $courseRole->getUser() === $user);
            return !$exists;
        });

        $builder
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choices' => $userChoices,
                'required' => false,
                'choice_label' => 'userName',
                'multiple' => true,
                'expanded' => true,
                'label' => 'course.labels.students',
                'help' => 'course.help.students',
                'translation_domain' => 'DegreeBase',
                'mapped' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'course.labels.addMember', 'translation_domain' => 'DegreeBase']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
