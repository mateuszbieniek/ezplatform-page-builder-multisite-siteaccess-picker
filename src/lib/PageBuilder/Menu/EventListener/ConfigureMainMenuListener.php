<?php

declare(strict_types=1);

namespace MateuszBieniek\EzPlatformPageBuilderMultisiteSiteaccessPicker\PageBuilder\Menu\EventListener;

use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use EzSystems\EzPlatformPageBuilderBundle\Menu\EventListener\ConfigureMainMenuListener as PageBuilderConfigureMainMenuListener;

class ConfigureMainMenuListener implements TranslationContainerInterface
{
    /**
     * @var PageBuilderConfigureMainMenuListener
     */
    private $configureMainMenuListener;

    public function __construct(
        PageBuilderConfigureMainMenuListener $configureMainMenuListener
    ) {
        $this->configureMainMenuListener = $configureMainMenuListener;
    }

    /**
     * @param \EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $this->configureMainMenuListener->onMenuConfigure($event);
    }

    /**
     * Returns an array of messages.
     *
     * @return array<Message>
     */
    public static function getTranslationMessages()
    {
        return PageBuilderConfigureMainMenuListener::getTranslationMessages();
    }

    /**
     * @param string $siteaccess
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(string $siteaccess, Location $location): Location
    {
        return $this->resolveLocation($siteaccess, $location);
    }
}
