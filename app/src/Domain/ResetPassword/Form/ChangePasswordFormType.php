<?php

namespace App\Domain\ResetPassword\Form;

use App\Domain\User\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'translation_domain' => 'UserPasswordReset',
                'invalid_message' => $this->translator
                    ->trans('form.validation.password-mismatch', [], 'UserPasswordReset'),
                'first_options' => [
                    'label' => 'form.label.password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'form.label.password-confirmation',
                    'attr' => ['autocomplete' => 'new-password'],
                    'help' => $this->translator->trans('form.validation.password-too-short', ['{{ limit }}' => User::MIN_PASSWORD_LENGTH], 'UserRegistration'),
                ],
                'required' => true,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,

                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator
                            ->trans('form.validation.password-blank', [], 'UserPasswordReset')
                    ]),
                    new Length([
                        'min' => User::MIN_PASSWORD_LENGTH,
                        'minMessage' => $this->translator
                            ->trans('form.validation.password-too-short', ['{{ limit }}'], 'UserPasswordReset'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
