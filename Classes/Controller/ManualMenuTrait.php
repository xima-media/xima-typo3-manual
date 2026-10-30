<?php

declare(strict_types=1);

namespace Xima\XimaTypo3Manual\Controller;

use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\Components\Menu\MenuItem;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Shared by the backend modules that document a single manual at a time: a doc header menu to switch between
 * manuals, shown only once the installation actually has more than one. Each menu item links to the current
 * controller's own index action, so switching manuals keeps you in the module you were in.
 */
trait ManualMenuTrait
{
    protected function registerManualMenu(ModuleTemplate $moduleTemplate, int $activeManualRoot): void
    {
        if (!$this->manualRegistry->hasSeveralManuals()) {
            return;
        }

        $menu = GeneralUtility::makeInstance(Menu::class);
        $menu->setIdentifier('_manualSelector');
        $menu->setLabel($this->translate('menu.manual'));

        foreach ($this->manualRegistry->getAccessibleManuals() as $manual) {
            $menuItem = GeneralUtility::makeInstance(MenuItem::class);
            $menuItem
                ->setTitle($manual->title !== '' ? $manual->title : '#' . $manual->uid)
                ->setHref($this->uriBuilder->uriFor('index', ['id' => $manual->uid]));
            if ($manual->uid === $activeManualRoot) {
                $menuItem->setActive(true);
            }
            $menu->addMenuItem($menuItem);
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }
}
