<?php

namespace Charles\AdvancedMakerCrudBundle\Maker;

use Charles\AdvancedMakerCrudBundle\GeneratorHelper;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\Column;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Console\Question\Question;



/**
 * @author Jose Carlos
 * https://github.com/symfony/maker-bundle/blob/ae12045bc50897d3c3c63dbd780d1600fec3da53/src/Maker/MakeCrud.php

 */
final class MakeAdvancedCrud extends AbstractMaker
{
    private $entityManager;
    private $doctrineHelper;

    public function __construct(EntityManagerInterface $entityManager, DoctrineHelper $doctrineHelper)
    {
        $this->entityManager = $entityManager;
        $this->doctrineHelper = $doctrineHelper;

    }

    public static function getCommandName(): string
    {
        return 'make:advancedcrud';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates CRUD for Doctrine entity class')
            ->addArgument('entity-class', InputArgument::REQUIRED, sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('singular-name', InputArgument::REQUIRED, 'Singular Name in templates')
            ->addArgument('plural-name', InputArgument::REQUIRED, 'Plural Name in templates')
            ->addArgument('type', InputArgument::OPTIONAL, 'Admin, Front or Root')
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCrud.txt'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
        $inputConfig->setArgumentAsNonInteractive('type');

    }


    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (!$input->getArgument('type')) {
            $argument = $command->getDefinition()->getArgument('type');
            $directories = ['root','admin','front'];
            $question = new ChoiceQuestion(
                $argument->getDescription(),
                $directories,
                '0'
            );
            $value = $io->askQuestion($question);
            $input->setArgument('type', $value);
        }

        if (!$input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');
            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();
            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);
            $value = $io->askQuestion($question);
            $input->setArgument('entity-class', $value);
        }
    }

    private function helperFormType(){

        return [
            "string" => [
                "type"      => "TextType",
                "namespace" => "Symfony\Component\Form\Extension\Core\Type\TextType",
                "addClass"  => ""
            ],
            "integer" => [
                "type"      => "IntegerType",
                "namespace" => "Symfony\Component\Form\Extension\Core\Type\IntegerType",
                "addClass"  => ""
            ],
            "text" => [
                "type"      => "TextareaType",
                "namespace" => "Symfony\Component\Form\Extension\Core\Type\TextareaType",
                "addClass"  => ""
            ],
            "datetime" => [
                "type"      => "DateType",
                "namespace" => "Symfony\Component\Form\Extension\Core\Type\DateType",
                "addClass"  => "material_datepicker"
            ],
            "entity" => [
                "type"      => "EntityType",
                "namespace" => "Symfony\Bridge\Doctrine\Form\Type\EntityType",
                "addClass"  => "chosen-select"
            ],
        ];
    }

    private function arrayFieldList($classNameS){
        
        $cmf           = $this->entityManager->getMetadataFactory();
        $class         = $cmf->getMetadataFor("App\\Entity\\".$classNameS);
        $fieldsEntity  = (array) $class->fieldMappings;
        $fieldsMapping = (array) $class->associationMappings;

        foreach ($fieldsMapping as $fieldName => $relation) {
             if($relation["inversedBy"]){
                $fieldsEntity[$fieldName] = $fieldsMapping[$fieldName];
                $fieldsEntity[$fieldName]["type"] = "entity";
             }
        }

        return $fieldsEntity;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $baseDirectory = strtolower($input->getArgument('type'));

        if($baseDirectory != "root"){
             $io->text('Different to Root');
        }

        $entityClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('entity-class'),
            'Entity\\'
        );

        $controllerClassNameDetails = $generator->createClassNameDetails(
            $entityClassNameDetails->getRelativeNameWithoutSuffix(),
            'Controller\\',
            'Controller'
        );

        $formClassNameDetails = $generator->createClassNameDetails(
            $entityClassNameDetails->getRelativeNameWithoutSuffix(),
            'Form\\',
            'Type'
        );
        /**
         * New Crate for Search Index Template
         */
        $searchFormClassNameDetails = $generator->createClassNameDetails(
            $entityClassNameDetails->getRelativeNameWithoutSuffix(),
            'Form\\Search\\',
            'SearchType'
        );


        $metadata = $this->entityManager->getClassMetadata($entityClassNameDetails->getFullName());

        $entityVarPlural = lcfirst(Inflector::pluralize($entityClassNameDetails->getShortName()));
        $entityVarSingular = lcfirst(Inflector::singularize($entityClassNameDetails->getShortName()));
        
        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);

        $routeName = Str::asRouteName($controllerClassNameDetails->getRelativeNameWithoutSuffix());

        $path = __DIR__.'/../Resources/skeleton/';

        /**
         * Get List Field from Entity with MetadataFactory
         * Get New Fields Arguments
         */

        $singularName   = $input->getArgument('singular-name');
        $pluralName     = $input->getArgument('plural-name');
        $shortEntity    = strtolower($entityVarSingular[0]);

        $classNameS     = ucfirst($entityVarSingular);

        $fieldsEntity   = $this->arrayFieldList($classNameS);
        $helperFields   = $this->helperFormType();


        $generator->generateClass(
            $controllerClassNameDetails->getFullName(),
            $path.'controller/Controller.tpl.php',
            [
                'entity_full_class_name' => $entityClassNameDetails->getFullName(),
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'form_full_class_name' => $formClassNameDetails->getFullName(),
                'form_class_name' => $formClassNameDetails->getShortName(),
                'form_search_full_class_name' => $searchFormClassNameDetails->getFullName(),
                'form_search_class_name' => $searchFormClassNameDetails->getShortName(),
                'route_path' => Str::asRoutePath($controllerClassNameDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $routeName,
                'entity_var_plural' => $entityVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $metadata->identifier[0],
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                'shortEntity' => $shortEntity,
            ]
        );

        $helper = new GeneratorHelper();

         $generator->generateClass(
            $formClassNameDetails->getFullName(),
            $path.'form/Type.tpl.php',
            [
                'entity_class_exists' => true,
                'entity_full_class_name' => $entityClassNameDetails->getFullName(),
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'form_fields' => $this->getFormFieldsFromEntity($metadata),
                'shortEntity' => $shortEntity,
                'fieldsEntity' =>$fieldsEntity,
                'helperFields' =>$helperFields
            ]
        );

        /**
         * Generate Form For Search Form in Index Template
         */
        $generator->generateClass(
            $searchFormClassNameDetails->getFullName(),
            $path.'form/Search.tpl.php',
            [
                'entity_class_exists' => true,
                'entity_full_class_name' => $entityClassNameDetails->getFullName(),
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'form_fields' => $this->getFormFieldsFromEntity($metadata),
                'shortEntity' => $shortEntity,
                'fieldsEntity' =>$fieldsEntity,
                'helperFields' =>$helperFields
            ]
        );


        $baseLayoutExists = true;
        $templatesPath = Str::asFilePath($controllerClassNameDetails->getRelativeNameWithoutSuffix());

        /**
         * Templates for Generator
         */
        $templates = [
            'search_form' => [
                'helper' => $helper,
                'base_layout_exists' => $baseLayoutExists,
                'entity_var_plural' => $entityVarPlural,
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $metadata->identifier[0],
                'form_fields' => $this->getFormFieldsFromEntity($metadata),
                'route_name' => $routeName,
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                'shortEntity' => $shortEntity,
            ],
            'index' => [
                'helper' => $helper,
                'base_layout_exists' => $baseLayoutExists,
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'entity_var_plural' => $entityVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $metadata->identifier[0],
                'entity_fields' => $metadata->fieldMappings,
                'route_name' => $routeName,
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                 'shortEntity' => $shortEntity,
            ],
            'new' => [
                'helper' => $helper,
                'base_layout_exists' => $baseLayoutExists,
                'entity_var_plural' => $entityVarPlural,
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'route_name' => $routeName,
                'form_fields' => $this->getFormFieldsFromEntity($metadata),
                'form_full_class_name' => $formClassNameDetails->getFullName(),
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                'shortEntity' => $shortEntity,
            ],
            'edit' => [
                'helper' => $helper,
                'base_layout_exists' => $baseLayoutExists,
                'entity_var_plural' => $entityVarPlural,
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $metadata->identifier[0],
                'route_name' => $routeName,
                'form_fields' => $this->getFormFieldsFromEntity($metadata),
                'form_full_class_name' => $formClassNameDetails->getFullName(),
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                 'shortEntity' => $shortEntity,
            ],
            'show' => [
                'helper' => $helper,
                'base_layout_exists' => $baseLayoutExists,
                'entity_var_plural' => $entityVarPlural,
                'entity_class_name' => $entityClassNameDetails->getShortName(),
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $metadata->identifier[0],
                'entity_fields' => $metadata->fieldMappings,
                'route_name' => $routeName,
                'singularName' => $singularName,
                'pluralName' => $pluralName,
                 'shortEntity' => $shortEntity,
            ],
        ];

        foreach ($templates as $template => $variables) {
            $generator->generateFile(
                'templates/'.$templatesPath.'/'.$template.'.html.twig',
                $path.'templates/'.$template.'.tpl.php',
                $variables
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text('Next: Check your new CRUD!');
    }

        /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );
        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );
        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );
        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );
        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );
        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );
        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );

    }

    private function getFormFieldsFromEntity(ClassMetadataInfo $metadata): array
    {
        $fields = (array) $metadata->fieldNames;
        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }
        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if (ClassMetadataInfo::ONE_TO_MANY !== $relation['type']) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
}
