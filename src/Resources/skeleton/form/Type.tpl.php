<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

<?php if ($entity_class_exists): ?>
use <?= $entity_full_class_name ?>;
<?php endif; ?>
<?php foreach ($fieldsEntity as $fieldWithnamespace): ?>
<?php if ($fieldWithnamespace["type"] && $fieldWithnamespace["fieldName"]!="id"){?>
use <?= $helperFields[$fieldWithnamespace["type"]]["namespace"] ?>;
<?php } ?>
<?php endforeach; ?>
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
     <?php foreach ($fieldsEntity as $fieldWithnamespace): ?>
        <?php if ($fieldWithnamespace["type"] 
        && $fieldWithnamespace["fieldName"]!="id" 
        && $helperFields[$fieldWithnamespace["type"]]){?>

        <?php if ($fieldWithnamespace["type"]=="entity"){?>

            $builder->add('<?=$fieldWithnamespace["fieldName"]?>', <?=$helperFields[$fieldWithnamespace["type"]]["type"]?>::class, array(
                'label'       => '<?=ucfirst($fieldWithnamespace["fieldName"])?>',
                'placeholder' => 'Elige un <?=$fieldWithnamespace["fieldName"]?>',
                'class'       => <?=get_class($fieldWithnamespace["targetEntity"])?>,
                'label_attr'  => array(
                    'class'   => 'control-label'
                ),
                'attr' => array(
                    'class' => 'form-control <?=$helperFields[$fieldWithnamespace["type"]]["addClass"]?>',
                )
            ));
        <?php }else{?>
   
        $builder->add('<?=$fieldWithnamespace["fieldName"]?>', <?=$helperFields[$fieldWithnamespace["type"]]["type"]?>::class, array(
            'label' => '<?=ucfirst($fieldWithnamespace["fieldName"])?>',
            'label_attr' => array(
                'class' => 'control-label'
                ),
            'attr' => array(
                'class' => 'form-control <?=$helperFields[$fieldWithnamespace["type"]]["addClass"]?>',
                )
        ));
        <?php } ?>
        <?php } ?>
        <?php endforeach; ?>
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
<?php if ($entity_class_exists): ?>
            'data_class' => <?= $entity_class_name ?>::class,
<?php endif; ?>
        ]);
    }
}
