TYPO3 Extension `noopener`
==========================

This extension adds `rel="noopener noreferrer"` to any links which are external
links or defined with target '_blank'. This improves the security of the site:

- **noopener**: Instructs the browser to open the link without granting the new browsing context access to the document that opened it — by not setting the Window.opener property on the opened window (it returns null).
- **noreferrer**: Prevents the browser, when navigating to another page, to send this page address, or any other value, as referrer via the `Referer:` HTTP header.

Installation
------------

1) Get the extension by using composer `composer require georgringer/noopener` 
2) Install the extension.
3) Done

Usage
-----
If it's enough that the extension is adding only the code ``rel="noopener noreferrer"``
to external links, then nothing has to be done after installation, it's done
automatically.

Nevertheless the extension can be used to manipulate the ``rel``-attribute further:

Simple extended usage
---------------------
To switch off that the default values ``noopener noreferrer`` are added to the
``rel-``-attribute , use this TypoScript code:  
::
    config.tx_noopener {
      useDefaultRelAttribute = false
    }

with the code ``relAttribute = nofollow`` another ``rel``-value can be added,
assumed the defaut values shall still be removed the code looks then like this:  
::
    config.tx_noopener {
      useDefaultRelAttribute = false
      relAttribute = nofollow
    }

Advanced extended usage
-----------------------
If for some link one or several CSS-classes can be added, then CSS-classes
with the prefix ``rel-`` can be configured being parsed and either copied or 
shifted to the ``rel-``-attribute.  
Assum a link with the following CSS-classes:
::
    `rel-nofollow rel-something col-right kunterbunt`.

In combination with the following setup the part ``rel-nofollow`` will be used
by the extension to add the value `nofollow` to the ``rel-``-attribute:  
::
    config.tx_noopener {
      useCssClass = 1
      keepCssRelClass = 0
    }

Due to the setting ``keepCssRelClass = 0`` will remove the corresponding part
from the CSS-classes, so the final HTML will be clean and never shows the
logical relation between the attribute ``class`` and ``rel-`` which exists only
in this extension.  
So the notation of the ``rel`` and ``class``-attributes will look like that:  
::
    rel="nofollow" class="rel-something col-right kunterbunt"

The reason why the css-class ``rel-something`` was not moved to the
``rel-``-attribute is that the value `something` never has any standardized
meaning and therefor is not included in the list of allowed values.  
A list of allowed values can be seen here:  
    https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types  

All mentioned expressions on that page can be used, everything else will be
ignored and stay in the definition for the class-attribute.  
As ``rel`` is often used for image-galleries this filter might look disadvantageous,
but keep in mind that the extension is only handling external links and images
are usuually served locally.  
Also keep in mind that the settings can be various on diffenet pages, as
the settings are defined by ``TypoScript``, so with a bit nifty code settings
could be different even on the same page but in differen cols.

Requirements
------------

- TYPO3 LTS 8.7 or 9.5

*TYPO3 10.1 will add the basic setting `rel="noopener noreferrer"` for external
links by default!*
