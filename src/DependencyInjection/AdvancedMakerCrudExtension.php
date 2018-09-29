<?php

namespace Charles\AdvancedMakerCrudBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Jose Carlos
 */
class AdvancedMakerCrudExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        /*$configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        //dd($config);

        $definition = $container->getDefinition('charles_custom_maker_crud.custom_maker_crud');
        $definition->setArgument(0, $config['base_template']);*/
    }

    public function getAlias()

    {
        return 'advanced_maker_crud';
    }
}
