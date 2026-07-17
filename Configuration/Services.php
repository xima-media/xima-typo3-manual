<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Dashboard\Widgets\CtaWidget;
use Xima\XimaTypo3Manual\EventListener\ContentUsedOnPageEventListener;
use Xima\XimaTypo3Manual\EventListener\ModifyButtonBarEventListener;
use Xima\XimaTypo3Manual\EventListener\PageContentPreviewEventListener;
use Xima\XimaTypo3Manual\Widgets\Provider\ManualButtonProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->load('Xima\\XimaTypo3Manual\\', '../Classes/*')
        ->exclude('../Classes/Domain/Model/*');

    $services->set(ModifyButtonBarEventListener::class)
        ->tag('event.listener', [
            'identifier' => 'xima-typo3-manual/event-listener/modify-button-bar',
        ]);

    $services->set(ContentUsedOnPageEventListener::class)
        ->tag('event.listener', [
            'identifier' => 'xima-typo3-manual/event-listener/content-used-on-page',
        ]);

    $services->set(PageContentPreviewEventListener::class)
        ->tag('event.listener', [
            'identifier' => 'xima-typo3-manual/event-listener/page-content-preview',
        ]);

    $sitesFolder = Environment::getConfigPath() . '/sites';
    $siteFolders = glob($sitesFolder . '/*', GLOB_ONLYDIR);
    foreach ($siteFolders as $siteFolder) {
        $siteConfig = Yaml::parseFile($siteFolder . '/config.yaml');
        $siteIdentifier = basename($siteFolder);
        if (in_array('xima/xima-typo3-manual', $siteConfig['dependencies'] ?? [], true)) {
            $services->set('xima_typo3_manual_widget.button.cta_' . $siteIdentifier)
                ->class(ManualButtonProvider::class)
                ->arg('$pageId', $siteConfig['rootPageId'] ?? 1)
                ->arg('$title', 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:cta_widget.button_label');

            $services->set('dashboard.widget.xima.xima_typo3_manual.cta_' . $siteIdentifier)
                ->class(CtaWidget::class)
                ->arg('$buttonProvider', service('xima_typo3_manual_widget.button.cta_' . $siteIdentifier))
                ->arg('$options', [
                    'text' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:cta_widget.bodytext',
                ])
                ->tag('dashboard.widget', [
                    'identifier' => 'xima.xima_typo3_manual.cta_' . $siteIdentifier,
                    'groupNames' => 'documentation,xima_typo3_manual',
                    'title' => 'Shortcut: ' . ($siteConfig['websiteTitle'] ?? $siteIdentifier),
                    'description' => 'LLL:EXT:xima_typo3_manual/Resources/Private/Language/locallang.xlf:cta_widget.description',
                    'iconIdentifier' => 'actions-lightbulb-on',
                    'height' => 'small',
                    'width' => 'small',
                ]);
        }
    }
};
