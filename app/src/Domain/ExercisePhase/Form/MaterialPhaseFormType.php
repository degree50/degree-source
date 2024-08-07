<?php

namespace App\Domain\ExercisePhase\Form;

use App\Domain\ExercisePhase\Model\MaterialPhase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialPhaseFormType extends ExercisePhaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('material', TextType::class, [
                'label' => "exercisePhase.labels.material",
                'translation_domain' => 'DegreeBase',
                'required' => true,
                'attr' => [
                    'data-controller' => 'ckeditor',
                    'aria-hidden' => "true",
                    'style' => "height: 1px; width: 1px; padding: 0; margin: 0; border: none; background-color: transparent;",
                    'tabIndex' => '-1'
                ],
            ])
            ->add('reviewRequired', CheckboxType::class, [
                'required' => false,
                'label' => "Muss von Ersteller:in überprüft werden",
                'block_prefix' => 'toggleable_button_checkbox',
                'help' => "Wenn aktiv, dann muss die Dokumentationsphase erst von einer lehrenden Person überprüft werden, bevor die Phase beendet werden kann und das Material auf dem Schreibtisch der Lernenden erscheint.",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MaterialPhase::class,
        ]);
    }
}

