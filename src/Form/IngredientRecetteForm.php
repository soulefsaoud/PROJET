<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Ingredient;
use App\Entity\IngredientRecette;

class IngredientRecetteForm extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder
->add('ingredient', EntityType::class, [
'class' => Ingredient::class,
'choice_label' => 'nom',
])
->add('quantite')
->add('uniteMesure');
}

public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults([
'data_class' => IngredientRecette::class,
]);
}
}
