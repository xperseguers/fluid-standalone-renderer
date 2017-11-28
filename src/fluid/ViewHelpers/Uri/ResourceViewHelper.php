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

namespace TYPO3Fluid\Fluid\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ResourceViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'The path and filename of the resource (relative to Public resource directory of the extension)', true);
        $this->registerArgument('extensionName', 'string', 'Target extension name. If not set, the current extension name will be used', false);
        $this->registerArgument('absolute', 'bool', 'If set, an absolute URI is rendered', false);
    }

    /**
     * @return string
     */
    public function render() : string
    {
        return 'TODO/link/to/resource';
    }

}
