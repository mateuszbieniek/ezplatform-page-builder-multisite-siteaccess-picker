<?php

declare(strict_types=1);

namespace MateuszBieniek\EzPlatformPageBuilderMultisiteSiteaccessPickerBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformAdminUi\View\ContentTranslateView;
use EzSystems\EzPlatformAdminUiBundle\Controller\Controller;
use EzSystems\EzPlatformPageBuilder\PageBuilder;
use EzSystems\EzPlatformPageBuilder\Siteaccess\SiteaccessService;
use EzSystems\EzPlatformPageBuilder\View\PageView;
use EzSystems\EzPlatformPageBuilderBundle\Controller\PageController as PageBuilderPageController;
use EzSystems\EzPlatformPageBuilderBundle\DependencyInjection\EzPlatformPageBuilderExtension;
use EzSystems\RepositoryForms\Content\View\ContentCreateView;
use EzSystems\RepositoryForms\Content\View\ContentEditView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class PageController extends Controller
{
    /** @var PageBuilderPageController */
    private $pageController;

    /** @var \EzSystems\EzPlatformPageBuilder\PageBuilder\PermissionAwareConfigurationResolver */
    private $pageBuilderPermissionAwareConfigurationResolver;

    /** @var \Symfony\Component\HttpFoundation\Session\Session */
    private $session;

    /** @var \EzSystems\EzPlatformPageBuilder\Siteaccess\SiteaccessService */
    private $siteaccessService;

    public function __construct(
        PageBuilderPageController $pageController,
        PageBuilder\ConfigurationResolverInterface $pageBuilderPermissionAwareConfigurationResolver,
        Session $session,
        SiteaccessService $siteaccessService
    ) {
        $this->pageController = $pageController;
        $this->pageBuilderPermissionAwareConfigurationResolver = $pageBuilderPermissionAwareConfigurationResolver;
        $this->session = $session;
        $this->siteaccessService = $siteaccessService;
    }

    public function previewAction(Request $request)
    {
        return $this->pageController->previewAction($request);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \Exception
     */
    public function locationViewAction(
        Request $request,
        int $locationId,
        ?int $versionNo = null,
        ?string $siteaccessName = null
    ): PageView {
        return $this->pageController->locationViewAction($request, $locationId, $versionNo, $siteaccessName);
    }

    /**
     * @throws \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @throws \Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Exception
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function urlViewAction(Request $request, string $url): PageView
    {
        return $this->pageController->urlViewAction($request, $url);
    }

    /**
     * @throws \Exception
     */
    public function editAction(Request $request, ContentEditView $view): ContentEditView
    {
        $language = $view->getLanguage();
        $location = $view->getLocation();
        $siteaccesses = $this->pageBuilderPermissionAwareConfigurationResolver->getSiteaccessList();
        $currentSiteaccess = $this->session->get(EzPlatformPageBuilderExtension::SESSION_KEY_SITEACCESS, reset($siteaccesses));

        if($location) {
            $currentSiteaccess = $this->getAvailableSiteaccess($currentSiteaccess, $siteaccesses, $location, $language);
        }

        if (!$currentSiteaccess) {
            throw new \RuntimeException('No SiteAccess available for this Page');
        }

        $this->session->set(EzPlatformPageBuilderExtension::SESSION_KEY_SITEACCESS, $currentSiteaccess);

        return $this->pageController->editAction($request, $view);
    }

    /**
     * @throws \Exception
     */
    public function createAction(Request $request, ContentCreateView $view): ContentCreateView
    {
        $language = $view->getLanguage();
        $location = $view->getLocation();
        $siteaccesses = $this->pageBuilderPermissionAwareConfigurationResolver->getSiteaccessList();
        $currentSiteaccess = $this->session->get(EzPlatformPageBuilderExtension::SESSION_KEY_SITEACCESS, reset($siteaccesses));

        if($location) {
            $currentSiteaccess = $this->getAvailableSiteaccess($currentSiteaccess, $siteaccesses, $location, $language);
        }

        if (!$currentSiteaccess) {
            throw new \RuntimeException('No SiteAccess available for this Root Location');
        }

        $this->session->set(EzPlatformPageBuilderExtension::SESSION_KEY_SITEACCESS, $currentSiteaccess);

        return $this->pageController->createAction($request, $view);
    }

    public function createDraftAction(
        Request $request,
        int $locationId,
        ?string $siteaccessName = null
    ): Response {
        return $this->pageController->createDraftAction($request, $locationId, $siteaccessName);
    }

    /**
     * @throws \Exception
     */
    public function translateAction(ContentTranslateView $view, Request $request): ContentTranslateView
    {
        return $this->pageController->translateAction($view, $request);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function isLocationInSiteaccessSubTree(string $siteaccess, Location $location): bool
    {
        $rootLocation = $this->siteaccessService->getRootLocation($siteaccess);

        return false !== strpos($location->pathString, $rootLocation->pathString)
            ? true
            : false;
    }

    private function isLanguageSuportedBySiteaccess(string $siteaccess, Language $language): bool
    {
        return \in_array($language->languageCode, $this->siteaccessService->getLanguages($siteaccess));
    }

    /**
     * @param string[] $siteaccesses
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getAvailableSiteaccess(
        string $currentSiteaccess,
        array $siteaccesses,
        Location $location,
        Language $language
    ): ?string {
        if (!$this->isLocationInSiteaccessSubTree($currentSiteaccess, $location)) {
            $currentSiteaccess = null;

            foreach ($siteaccesses as $availableSiteaccess) {
                if (
                $this->isLocationInSiteaccessSubTree($availableSiteaccess, $location)
                ) {
                    $currentSiteaccess = $availableSiteaccess;

                    break;
                }
            }
        }

        if (!$this->isLanguageSuportedBySiteaccess($currentSiteaccess, $language)) {
            foreach ($siteaccesses as $availableSiteaccess) {
                if (
                    $this->isLocationInSiteaccessSubTree($availableSiteaccess, $location) &&
                    $this->isLanguageSuportedBySiteaccess($availableSiteaccess, $language)
                ) {
                    $currentSiteaccess = $availableSiteaccess;

                    break;
                }
            }
        }

        return $currentSiteaccess;
    }
}
