<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $minDate = (new \DateTime())->modify('-18 years')->format('Y-m-d');
        $builder

            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'placeholder' => 'Nom complet'
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom complet',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre nom doit comporter au moins 5 caractères',
                    ]),
                ],
            ])

            ->add('email', EmailType::class, [

                'attr' => ['class' => 'form-control bg-light border-0', 'placeholder' => 'Adresse email'],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email',
                    ]),
                    new Email([
                        'message' => 'S\'il vous plaît, mettez une adresse email valide',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre email doit comporter au moins 8 caractères',
                        'max' => 50,
                    ]),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control bg-light border-0', 'placeholder' => 'Mot de passe'],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),

                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit comporter au moins 8 caractères',
                        'max' => 12,
                    ]),
                ],

            ])

            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Mâle' => true,
                    'Femelle' => false,
                ],
                'label' => false,
                'attr' => ['class' => 'form-select bg-light border-0']
            ])
            ->add('ddn', DateType::class, [
                'attr' => [
                    'max' => $minDate,
                    'class' => 'form-control bg-light border-0 datetimepicker-input',
                ],
                'label' => false,
                'widget' => 'single_text',
            ])

            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'placeholder' => 'Adresse'
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre adresse',
                    ]),
                ],
            ])

            ->add('ville', ChoiceType::class, [
                'choices' => [
                    'Al Hoceïma' => 'al_hoceima',
                    'Casablanca' => 'casablanca',
                    'Fès' => 'fes',
                    'Kénitra' => 'kenitra',
                    'Marrakech' => 'marrakech',
                    'Meknès' => 'meknes',
                    'Rabat' => 'rabat',
                    'Salé' => 'sale',
                    'Tanger' => 'tanger',
                    'Tétouan' => 'tetouan'

                ],
                'label' => false,
                'attr' => ['class' => 'form-select bg-light border-0']
            ])

            ->add('cin', TextType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'placeholder' => 'CIN'
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre CIN',
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'placeholder' => '+212612345678',
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre numero de téléphone',
                    ]),
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
