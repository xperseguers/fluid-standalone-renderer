# Fluid Standalone Rendering Engine

As a designer/integrator, this package allows you to easily render templates and partials before their actual
integration into a full-blown TYPO3 website.

This is especially useful when the designer team works on template files (HTML/CSS/JS) and provide ready-to-use
assets to the TYPO3 integration team.  


## Installation

1. Include as composer dependency using `composer require causal/fluid-standalone-renderer`
2. Run `composer install` to generate the vendor class autoloader


## Dispatcher

You then need a simple `index.php` (or whichever name you want) script within your design project:

```
<?php
require __DIR__ . '/vendor/autoload.php';

$htmlPath = __DIR__ . '/Resources/Private/';
$dataPath = __DIR__ . '/Resources/Private/Samples/';

$server = new \Causal\FluidStandaloneRenderer\Server(
    basename(__FILE__),
    $htmlPath,
    $dataPath
);

echo $server->run();
```

Executing this script from your browser will show you the various available templates and partials available
under `Resources/Private/Templates/` and `Resources/Private/Partials/`.


## Sample Data

Say, you have following partial `Resources/Private/Partials/Foo/Bar.html`:

```
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
      xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
      data-namespace-typo3-fluid="true">

<span class="label label-default">
    {title}
    <f:if condition="{subtitle}">
        <small>({subtitle})</small>
    </f:if>
</span>

</html>
```

You may then create a file with sample data `Resources/Private/Samples/Partials/Foo/Bar.json`:

```
{
  "title": "My sample title",
  "subtitle": "My sample subtitle"
}
```

The rule is that a sample data is stored in the exact same directory structure and name as its corresponding
"template", but instead of ending in `.html`, it ends in `.json`.
