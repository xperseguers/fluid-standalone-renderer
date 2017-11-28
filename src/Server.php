<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

declare(strict_types=1);
namespace Causal\FluidStandaloneRenderer;

use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class Server
{

    /**
     * @var string
     */
    protected $htmlPath;

    /**
     * @var string
     */
    protected $dataPath;

    /**
     * @var TemplateView
     */
    protected $view;

    /**
     * @var TemplateView
     */
    protected $projectView;

    /**
     * @var array
     */
    protected $projectStylesheets = [];

    /**
     * @var array
     */
    protected $projectJavaScripts = [];

    /**
     * @var array
     */
    protected $projectJavaScriptsFooter = [];

    /**
     * Server constructor.
     *
     * @param string $scriptName
     * @param string $htmlPath Absolute path to the directory containing Fluid templates, partials and layouts
     * @param string|null $dataPath Absolute path to the directory containing JSON sample data files for the Fluid templates and partials
     */
    public function __construct(string $scriptName, string $htmlPath, string $dataPath = null)
    {
        $this->htmlPath = realpath($htmlPath) . '/';
        $this->dataPath = realpath($dataPath) . '/';

        $resources = __DIR__ . '/../resources/';
        $this->view = $this->createView(
            $resources . 'Templates/',
            $resources . 'Partials/',
            $resources . 'Layouts/'
        );
        $this->view->assign('script', $scriptName);

        $this->projectView = $this->createView(
            $this->htmlPath . 'Templates/',
            $this->htmlPath . 'Partials/',
            $this->htmlPath . 'Layouts/'
        );
    }

    /**
     * @param string $relativeFileName
     * @return $this
     */
    public function addProjectStylesheet(string $relativeFileName)
    {
        $this->projectStylesheets[] = $relativeFileName;
        return $this;
    }

    /**
     * @param string $relativeFileName
     * @param bool $allowMoveToFooter
     * @return $this
     */
    public function addProjectJavaScript(string $relativeFileName, bool $allowMoveToFooter = false)
    {
        if ($allowMoveToFooter) {
            $this->projectJavaScriptsFooter[] = $relativeFileName;
        } else {
            $this->projectJavaScripts[] = $relativeFileName;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function run() : string
    {
        if (!isset($_GET['file'])) {
            $out = $this->showAvailableTemplates();
        } else {
            if ($_GET['standalone'] ?? false) {
                $out = $this->renderTemplate($_GET['file']);
            } else {
                $out = $this->showTemplate($_GET['file']);
            }
        }

        return $out;
    }

    /**
     * @return string
     */
    protected function showAvailableTemplates() : string
    {
        $dir = new \RecursiveDirectoryIterator($this->htmlPath);
        $iterator = new \RecursiveIteratorIterator($dir);
        $regex = new \RegexIterator($iterator, '/^.+\.html$/i', \RecursiveRegexIterator::GET_MATCH);

        $templates = [];
        $partials = [];

        foreach($regex as $name => $object) {
            $name = substr($name, strlen($this->htmlPath), -5);
            list($type, $file) = explode('/', $name, 2);
            switch ($type) {
                case 'Templates':
                    $templates[] = $file;
                    break;
                case 'Partials':
                    $partials[] = $file;
                    break;
            }
        }

        // Sort files
        sort($templates);
        sort($partials);

        $this->view->assignMultiple([
            'templates' => $templates,
            'partials' => $partials,
        ]);
        $out = $this->view->render('List');
        return $out;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function showTemplate(string $fileName) : string
    {
        $jsonFileName = $this->dataPath . $fileName . '.json';
        $data = is_file($jsonFileName) ? json_decode(file_get_contents($jsonFileName), true) : [];

        $this->view->assignMultiple([
            'file' => $fileName,
            'data' => json_encode($data, JSON_PRETTY_PRINT),
            'template' => file_get_contents($this->htmlPath . $fileName . '.html'),
        ]);

        $out = $this->view->render('Show');
        return $out;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function renderTemplate(string $fileName) : string
    {
        $jsonFileName = $this->dataPath . $fileName . '.json';
        $data = is_file($jsonFileName) ? json_decode(file_get_contents($jsonFileName), true) : [];

        if (substr($fileName, 0, 9) === 'Partials/') {
            $html = $this->projectView->renderPartial(substr($fileName, 9), null, $data);
        } else {
            $html = $this->projectView->render(substr($fileName, 10), null, $data);
        }

        $this->view->assignMultiple([
            'css' => $this->projectStylesheets,
            'js' => $this->projectJavaScripts,
            'jsFooter' => $this->projectJavaScriptsFooter,
            'file' => $fileName,
            'html' => $html,
        ]);
        $out = $this->view->render('Render');

        return $out;
    }

    /**
     * @param string $templateRootPath
     * @param string|null $partialRootPath
     * @param string|null $layoutRootPath
     * @return TemplateView
     */
    protected function createView(string $templateRootPath, string $partialRootPath = null, string $layoutRootPath = null)
    {
        $paths = new TemplatePaths();
        $paths->setTemplateRootPaths([$templateRootPath]);
        if (!empty($partialRootPath)) {
            $paths->setPartialRootPaths([$partialRootPath]);
        }
        if (!empty($layoutRootPath)) {
            $paths->setLayoutRootPaths([$layoutRootPath]);
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setTemplatePaths($paths);

        return $view;
    }

}
