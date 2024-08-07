<?php

namespace App\Domain\ExercisePhase\Form;

use App\Domain\ExercisePhase\Model\ReflexionPhase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReflexionPhaseFormType extends ExercisePhaseFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReflexionPhase::class,
        ]);
    }
}
