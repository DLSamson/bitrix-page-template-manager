<?php declare(strict_types=1);

use PageTemplateManager\Exceptions\TemplateFileNotFoundException;
use PageTemplateManager\Manager;
use PageTemplateManager\Templater;
use PHPUnit\Framework\TestCase;

final class ManagerTest extends TestCase {
    private function getTemplater() {
        $templateDir = __DIR__ . '/templates';
        return new Templater($templateDir);
    }

    private function getConfigString() {
        return __DIR__ . '/testManagerConfig.php';
    }

    private function getConfigArray() {
        return require $this->getConfigString();
    }

    public function testCanAutoDetectTemplate() {
        $templater = $this->getTemplater();
        $config = $this->getConfigString();
        
        // main
        $currentPageUrl = '/'; // In real use expects something like: $APPLICATION->GetCurPage();
        $manager = new Manager($currentPageUrl, $templater, $config);

        ob_start();
        $manager->autoDetectHeaderTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'main.headerTemplate');

        ob_start();
        $manager->autoDetectFooterTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'main.footerTemplate');

        // list
        $currentPageUrl = '/articles';
        $manager = new Manager($currentPageUrl, $templater, $config);

        ob_start();
        $manager->autoDetectHeaderTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.headerTemplate');

        ob_start();
        $manager->autoDetectFooterTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.footerTemplate');

        // list.item
        $currentPageUrl = '/articles/article1';
        $manager = new Manager($currentPageUrl, $templater, $config);

        ob_start();
        $manager->autoDetectHeaderTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.item.headerTemplate');

        ob_start();
        $manager->autoDetectFooterTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.item.footerTemplate');

        // list.item.uniquePage
        $currentPageUrl = '/articles/article1/unique';
        $manager = new Manager($currentPageUrl, $templater, $config);

        ob_start();
        $manager->autoDetectHeaderTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.item.uniquePage.headerTemplate');

        ob_start();
        $manager->autoDetectFooterTemplate();
        $content = ob_get_clean();
        $this->assertEquals($content, 'list.item.uniquePage.footerTemplate');
    }

    public function testCanPassValuesToTemplate() {
        $currentPageUrl = '/passValue';
        $templater = $this->getTemplater();
        $config = $this->getConfigString();

        $values = [
            'name' => 'John',
        ];
        
        $manager = new Manager($currentPageUrl, $templater, $config);

        ob_start();
        $manager->autoDetectPassValueTemplate($values);
        $content = ob_get_clean();
        $this->assertEquals($content, 'Hello John');
    }

    public function testThrownTemplateNotFound() : void {
        $currentPageUrl = '/';
        $templater = $this->getTemplater();
        $config = $this->getConfigString();
        
        $manager = new Manager($currentPageUrl, $templater, $config);

        $this->expectException(TemplateFileNotFoundException::class);
        $manager->autoDetectWhateverTypeTemplate();
    }

//    method setExceptionSilentMode does not exists
//    public function testNotThrownTemplateNotFound() : void {
//        $currentPageUrl = '/';
//        $templater = $this->getTemplater();
//        $config = $this->getConfigString();
//
//        $manager = new Manager($currentPageUrl, $templater, $config);
//        $manager->setExceptionSilentMode(true);
//
//        $manager->autoDetectWhateverTypeTemplate();
//        $this->assertNull(null);
//    }
}