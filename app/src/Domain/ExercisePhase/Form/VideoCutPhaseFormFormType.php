<?php

namespace App\Domain\ExercisePhase\Form;

use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class VideoCutPhaseFormFormType extends ExercisePhaseFormType
{
    public function __construct(
        ExercisePhaseRepository          $exercisePhaseRepository,
        ExercisePhaseService             $exercisePhaseService,
        private readonly VideoRepository $videoRepository,
    )
    {
        parent::__construct($exercisePhaseRepository, $exercisePhaseService);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $options['data'];

        $videoChoices = $this->videoRepository->findByCourse($exercisePhase->getBelongsToExercise()->getCourse());

        $phaseHasVideoConfigured = $exercisePhase->getVideos()->count() > 0;

        $builder
            ->add('videos', EntityType::class, [
                'class' => Video::class,
                'choices' => $videoChoices,
                'required' => true,
                // Video selection is disabled if the exercise phase has solutions AND there is already a video selected
                'disabled' => $exercisePhase->getHasSolutions() && $phaseHasVideoConfigured,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'block_prefix' => 'video_entity'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VideoCutPhase::class,
        ]);
    }
}
