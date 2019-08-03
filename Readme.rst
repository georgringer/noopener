TYPO3 Extension `noopener`
==========================

This extension adds `rel="noopener noreferrer" to any links which are external links or defined with target '_blank'. This improves the security of the site:

- **noopener**: Instructs the browser to open the link without granting the new browsing context access to the document that opened it — by not setting the Window.opener property on the opened window (it returns null).
- **noreferrer**: Prevents the browser, when navigating to another page, to send this page address, or any other value, as referrer via the `Referer:` HTTP header.

Installation
------------

1) Get the extension by using composer `composer require georgringer/noopener` 
2) Install the extension.
3) Done

Requirements
------------

- TYPO3 LTS 8.7 or 9.5

*TYPO3 10.1 will ship with such setting by default!*
