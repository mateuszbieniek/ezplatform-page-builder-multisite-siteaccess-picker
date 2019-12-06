<?php

declare(strict_types=1);

namespace MateuszBieniek\EzPlatformPageBuilderMultisiteSiteaccessPicker\PageBuilder\Menu\EventListener;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent;
use EzSystems\EzPlatformPageBuilder\PageBuilder\ConfigurationResolverInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use EzSystems\EzPlatformPageBuilderBundle\Menu\EventListener\ConfigureMainMenuListener as PageBuilderConfigureMainMenuListener;
use EzSystems\EzPlatformPageBuilder\Siteaccess\SiteaccessService;

class ConfigureMainMenuListener implements TranslationContainerInterface
{
    /** @var SiteaccessService */
    private $siteaccessService;

    /** @var PageBuilderConfigureMainMenuListener */
    private $configureMainMenuListener;

    /** @var \EzSystems\EzPlatformPageBuilder\PageBuilder\ConfigurationResolverInterface */
    private $pageBuilderConfigurationResolver;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var bool */
    private $overrideSiteaccessList;

    public function __construct(
        PageBuilderConfigureMainMenuListener $configureMainMenuListener,
        SiteaccessService $siteaccessService,
        ConfigurationResolverInterface $pageManagerConfigurationResolver,
        ContentService $contentService,
        bool $overrideSiteaccessList
    ) {
        $this->configureMainMenuListener = $configureMainMenuListener;
        $this->siteaccessService = $siteaccessService;
        $this->pageBuilderConfigurationResolver = $pageManagerConfigurationResolver;
        $this->contentService = $contentService;
        $this->overrideSiteaccessList = $overrideSiteaccessList;
    }

    /**
     * @param \EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $event->stopPropagation();

        if(!$this->overrideSiteaccessList) {
            return $this->configureMainMenuListener->onMenuConfigure($event);
        }

        $root = $event->getMenu();
        $options = $event->getOptions();

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $options['location'] ?? null;

        $siteaccesses = $this->pageBuilderConfigurationResolver->getSiteaccessList();

        if (empty($siteaccesses)) {
            return;
        }

        $root->addChild(PageBuilderConfigureMainMenuListener::ITEM_PAGE, [
            'extras' => [
                'icon' => 'content-list',
                'translation_domain' => 'ezplatform_page_builder_menu',
                'routes' => [
                    'ezplatform.page_builder.location_preview',
                    'ezplatform.page_builder.url_preview',
                ],
            ],
        ]);

        foreach ($siteaccesses as $siteaccess) {
            try {
                $currentLocation = $this->resolveLocation($siteaccess, $location);
                if(!$currentLocation) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            dump($siteaccess);

            $currentContent = $this->contentService->loadContent($currentLocation->contentId);
            $root[PageBuilderConfigureMainMenuListener::ITEM_PAGE]->addChild($siteaccess, [
                'route' => 'ezplatform.page_builder.location_preview',
                'routeParameters' => [
                    'locationId' => $currentLocation->id,
                    'languageCode' => $currentContent->prioritizedFieldLanguageCode ?? $currentContent->contentInfo->mainLanguageCode,
                    'siteaccessName' => $siteaccess,
                    'versionNo' => $currentContent->getVersionInfo()->versionNo,
                ],
                'extras' => [
                    'label' => $siteaccess,
                    'translation_domain' => 'ezplatform_siteaccess',
                ],
            ]);
        }
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
     * @return null|\eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(string $siteaccess, Location $location): ?Location
    {
        $rootLocation = $this->siteaccessService->getRootLocation($siteaccess);

        return false !== strpos($location->pathString, $rootLocation->pathString)
            ? $location
            : null;
    }
}
