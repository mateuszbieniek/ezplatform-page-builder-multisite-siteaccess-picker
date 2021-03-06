<?php

declare(strict_types=1);

namespace MateuszBieniek\EzPlatformPageBuilderMultisiteSiteaccessPickerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzPlatformPageBuilderMultisiteSiteaccessPickerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $core = $container->getExtension('ezpublish');
        $core->addDefaultSettings(__DIR__ . '/Resources/config', ['default_settings.yml']);
    }
}
