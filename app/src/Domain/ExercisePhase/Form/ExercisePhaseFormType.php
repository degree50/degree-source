<?php

namespace App\Domain\ExercisePhase\Form;

use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExercisePhaseFormType extends AbstractType
{
    public function __construct(
        private readonly ExercisePhaseRepository $exercisePhaseRepository,
        private readonly ExercisePhaseService    $exercisePhaseService,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $options['data'];

        $phasesFromSameCourse = $this->exercisePhaseRepository
            ->findBy(['belongsToExercise' => $exercisePhase->belongsToExercise]);
        $exercisePhaseChoices = array_filter(
            $phasesFromSameCourse,
            function (ExercisePhase $phaseDependingOn) use ($exercisePhase) {
                return $this->exercisePhaseService
                    ->isValidDependingOnExerciseCombination($phaseDependingOn, $exercisePhase);
            }
        );

        $isReflexionPhase = $exercisePhase->getType() === ExercisePhaseType::REFLEXION;

        $builder
            ->add('dependsOnExercisePhase', EntityType::class, [
                'class' => ExercisePhase::class,
                'choices' => $exercisePhaseChoices,
                'choice_label' => function (ExercisePhase $exercisePhase) {
                    $name = $exercisePhase->getName();
                    $type = $this->exercisePhaseService->getPhaseTypeTitle($exercisePhase->getType());
                    return "$name ($type)";
                },
                'placeholder' => 'exercisePhase.labels.dependsOnPreviousPhaseNone',
                'multiple' => false,
                'required' => $isReflexionPhase,
                'disabled' => $exercisePhase->getSorting() === 0 || $exercisePhase->getHasSolutions(),
                'label' => "exercisePhase.labels.dependsOnPreviousPhase",
                'translation_domain' => 'DegreeBase',
                'help' => $isReflexionPhase
                    ? "exercisePhase.help.dependsOnPreviousPhase.reflexion"
                    : "exercisePhase.help.dependsOnPreviousPhase.regular",
            ])
            ->add('isGroupPhase', CheckboxType::class, [
                'required' => false,
                'label' => "exercisePhase.labels.isGroupPhase",
                'disabled' => $exercisePhase->getHasSolutions() || $isReflexionPhase,
                'translation_domain' => 'DegreeBase',
                'block_prefix' => 'toggleable_button_checkbox',
                'help' => "exercisePhase.help.isGroupPhase",
            ])
            ->add('name', TextType::class, ['label' => "exercisePhase.labels.name", 'translation_domain' => 'DegreeBase'])
            // NOTE: there seems to be a bug inside CKEditor where the editor does not respect the 'required' attribute
            // We currently work around this by falling back to symfony's own validation via Annotations on the ExercisePhase.php entity.
            // Therefore we added an @Assert\NotBlank to the entity field and also made the field setter property optional.
            ->add('task', TextType::class, [
                'label' => "exercisePhase.labels.task",
                'translation_domain' => 'DegreeBase',
                'required' => true,
                'attr' => [
                    'data-controller' => 'ckeditor',
                    'aria-hidden' => "true",
                    'style' => "height: 1px; width: 1px; padding: 0; margin: 0; border: none; background-color: transparent;",
                    'tabIndex' => '-1'
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'exercisePhase.labels.submit', 'translation_domain' => 'DegreeBase']);

        // We can't just simply omit fields inside the twig template, because they will be appended to the form,
        // even if we "turn them off" inside the template.
        // Therefore we add them conditionally here
        if (!$isReflexionPhase) {
            $builder
                ->add('otherSolutionsAreAccessible', CheckboxType::class, [
                    'required' => false,
                    'disabled' => false,
                    'label' => "exercisePhase.labels.otherSolutionsAreAccessible",
                    'translation_domain' => 'DegreeBase',
                    'block_prefix' => 'toggleable_button_checkbox',
                    'help' => "exercisePhase.help.otherSolutionsAreAccessible",
                ])
                // ReflexionPhase is always a group phase
                ->add('isGroupPhase', CheckboxType::class, [
                    'required' => false,
                    'label' => "exercisePhase.labels.isGroupPhase",
                    'disabled' => $exercisePhase->getHasSolutions(),
                    'translation_domain' => 'DegreeBase',
                    'block_prefix' => 'toggleable_button_checkbox',
                    'help' => "exercisePhase.help.isGroupPhase",
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExercisePhase::class,
        ]);
    }
}
