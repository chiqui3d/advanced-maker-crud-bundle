<?php

namespace Charles\AdvancedMakerCrudBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Charles\AdvancedMakerCrudBundle\DependencyInjection\AdvancedMakerCrudExtension;

/**
 * @author Jose Carlos
 */
class AdvancedMakerCrudBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
    }
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension()

    {
        if (null === $this->extension) {
            $this->extension = new AdvancedMakerCrudExtension();
        }
        return $this->extension;
    }
}
