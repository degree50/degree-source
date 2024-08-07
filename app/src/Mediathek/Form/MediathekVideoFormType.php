<?php

namespace App\Mediathek\Form;

use App\Domain\Course\Model\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediathekVideoFormType extends AbstractType
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
            ->add('title', TextType::class, ['label' => "video.labels.title", 'translation_domain' => 'DegreeBase'])
            ->add('description', TextareaType::class, ['label' => "video.labels.description", 'translation_domain' => 'DegreeBase', 'required' => false])
            ->add('personalNotes', TextareaType::class, [
                'label' => "video.labels.personalNotes",
                'translation_domain' => 'DegreeBase',
                'required' => false,
            ])
            ->add('dataPrivacyAccepted', CheckboxType::class, ['label' => "video.labels.dataPrivacyAccepted", 'translation_domain' => 'DegreeBase', 'required' => true])
            ->add('dataPrivacyPermissionsAccepted', CheckboxType::class, ['label' => "video.labels.dataPrivacyPermissionsAccepted", 'translation_domain' => 'DegreeBase', 'required' => true])
            ->add('courses', EntityType::class, [
                'class' => Course::class,
                'choices' => $courseChoices,
                'required' => false,
                'choice_label' => function (Course $choice) {
                    return $choice->getCreationDateYear() . ' - ' . $choice->getName();
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'video.labels.courses',
                'translation_domain' => 'DegreeBase',
                'help' => 'video.help.courses',
            ])
            ->add('save', SubmitType::class, ['label' => "video.labels.submit", 'translation_domain' => 'DegreeBase']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
