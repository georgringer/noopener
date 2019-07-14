<?php
defined('TYPO3_MODE') or die();


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc']['noopener']
    = \GeorgRinger\Noopener\Hooks\LinkHook::class . '->run';
