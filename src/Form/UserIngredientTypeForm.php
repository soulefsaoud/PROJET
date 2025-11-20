<?php
// src/Form/UserIngredientType.php

namespace App\Form;

use App\Entity\UserIngredient;
use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un ingrédient',
                'label' => 'Ingrédient',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                ],
            ])
            ->add('unite_mesure', ChoiceType::class, [
                'label' => 'Unité',
                'choices' => [
                    'Grammes' => 'g',
                    'Kilogrammes' => 'kg',
                    'Millilitres' => 'ml',
                    'Litres' => 'l',
                    'Pièces' => 'pièce',
                    'Cuillères à soupe' => 'c. à soupe',
                    'Cuillères à café' => 'c. à café',
                    'Tasses' => 'tasse',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter au garde-manger',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserIngredient::class,
        ]);
    }
}
