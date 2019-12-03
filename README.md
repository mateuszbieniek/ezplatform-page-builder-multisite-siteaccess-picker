# ezplatform-page-builder-multisite-siteaccess-picker
## Description
The bundle provides an alternative way of selecting SiteAccess for PageBuilder when Landing Page
is edited by clicking on the "Edit" button.
By default last used SiteAccess is taken and this Bundle changes this behavior so it
is better suited for the Multisite setup.

If Landing Page is outside of last used SiteAccess Subtree, first SiteAccess that shares 
Subtree with Landing Page will be used.

## Installation
**Requires eZ Platform Enterprise Edition 2.5 LTS**

### 1. Enable `EzPlatformPageBuilderMultisiteSiteaccessPickerBundle`
Edit `app/AppKernel.php`, and add 
```
new MateuszBieniek\EzPlatformPageBuilderMultisiteSiteaccessPickerBundle\EzPlatformPageBuilderMultisiteSiteaccessPickerBundle(),
```
at the end of the `$bundles` array.
### 2. Install `mateuszbieniek/ezplatform-page-builder-multisite-siteaccess-picker`
```
composer require mateuszbieniek/ezplatform-page-builder-multisite-siteaccess-picker
```
