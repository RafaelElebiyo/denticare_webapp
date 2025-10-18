<?php

namespace App\Form;

use App\Entity\Rendezvous;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class RendezvousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0 datetimepicker-input',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
                'label' => false,
                'widget' => 'single_text',
            ])

            ->add('service', ChoiceType::class, [
                'choices' => [
                    'Service' => '',
                    'Révision' => 'Révision',
                    'Orthodontie' => 'Orthodontie',
                    'Implantes Dentaires' => 'Implantes Dentaires',
                    'Ponts Dentaires' => 'Ponts Dentaires',
                    'Blanchissement Dentaire' => 'Blanchissement Dentaire',
                    'Extraction dentaire' => 'Extraction dentaire',
                ],
                'label' => 'Service',
                'choice_attr' => function ($choice, $key, $value) {
                    if ($value === '') {
                        return ['disabled' => 'disabled'];
                    }
                    return [];
                },
                'attr' => ['class' => 'form-select bg-light border-0'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rendezvous::class,
        ]);
    }
}
