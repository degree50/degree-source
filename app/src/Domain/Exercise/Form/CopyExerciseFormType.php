<?php

namespace App\Domain\Exercise\Form;

use App\Domain\Course\Model\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Exercise\Dto\CopyExerciseFormDto;
use App\Domain\User\Model\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CopyExerciseFormType extends AbstractType
{
    public function __construct(
        private readonly Security         $security,
        private readonly CourseRepository $courseRepository,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $courseChoices = $this->courseRepository->findAllForUserWithCriteria($user);

        $builder
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choices' => $courseChoices,
                'choice_label' => 'name',
                'multiple' => false,
                'required' => true,
                'expanded' => true,
                'label' => 'Zu Kurs hinzufügen',
                'translation_domain' => 'DegreeBase',
            ])
            ->add('copyPhases', CheckboxType::class, [
                'label' => 'Aufgabe mit Phasen kopieren',
                'required' => false,
                'translation_domain' => 'DegreeBase',
            ])
            ->add('save', SubmitType::class, ['label' => 'exercise.copy.title', 'translation_domain' => 'DegreeBase']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CopyExerciseFormDto::class,
        ]);
    }
}
