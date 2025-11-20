<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\IngredientRecette;
use App\Entity\Menu;
use App\Entity\Recette;
use App\Entity\Regime;
use App\Form\IngredientRecetteForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('instructions')
            ->add('tempsPreparation')
            ->add('difficulte')
            ->add('dateCreation')
            ->add('nombreDePortions')
            ->add('ingredientRecettes', CollectionType::class, [
                'entry_type' => IngredientRecetteForm::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('regimes', EntityType::class, [
                'class' => Regime::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('menus', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
