<?php

namespace App\Form;

use App\Entity\Temoinage;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;


class TemoinageType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'attr' => ['class' => 'form-control border-0 bg-light px-4 py-3 w-100 h-30'],
                'label' => false,
                'constraints' => [
                    new Length([
                        'min' => 30,
                        'minMessage' => 'Le message doit comporter au moins 50 caractères.',
                    ]),
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Temoinage::class,
        ]);
    }
}
