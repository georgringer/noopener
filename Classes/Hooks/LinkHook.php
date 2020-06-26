<?php
declare(strict_types=1);

namespace GeorgRinger\Noopener\Hooks;

/**
 * This file is part of the "noopener" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook into ContentObjectRenderer to add noopener noreferrer to links
 */
class LinkHook
{
    /**
     * @param array $params
     */
    public function run(array &$params)
    {
        $relAttribute = 'noopener noreferrer';
        $target = $params['tagAttributes']['target'];
        $url = $params['finalTagParts']['url'];

        if ($target === '_blank' || !$this->isInternalUrl($url)) {
            if (!isset($params['tagAttributes']['rel'])) {
                $params['tagAttributes']['rel'] = $relAttribute;
                $params['finalTag'] = str_replace('<a ', '<a rel="' . $relAttribute . '" ', $params['finalTag']);
            } else {
                $params['tagAttributes']['rel'] = implode(' ', array_unique(array_merge(
                    GeneralUtility::trimExplode(' ', $relAttribute),
                    GeneralUtility::trimExplode(' ', $params['tagAttributes']['rel'])
                )));
                $params['finalTag'] = preg_replace('/rel=\"(.*?)\"/', 'rel="' . $params['tagAttributes']['rel'] . '"', $params['finalTag']);
            }
        }
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function isInternalUrl(string $url): bool
    {
        return $this->isRelativeUrl($url) || $this->isInCurrentDomain($url) || $this->isInLocalDomain($url);
    }

    /**
     * Determines whether the URL is relative to the
     * current TYPO3 installation.
     *
     * @param string $url URL which needs to be checked
     * @return bool Whether the URL is considered to be relative
     */
    protected function isRelativeUrl($url): bool
    {
        $url = GeneralUtility::sanitizeLocalUrl($url);
        if (!empty($url)) {
            $parsedUrl = @parse_url($url);
            if ($parsedUrl !== false && !isset($parsedUrl['scheme']) && !isset($parsedUrl['host'])) {
                // If the relative URL starts with a slash, we need to check if it's within the current site path
                return $parsedUrl['path'][0] !== '/' || GeneralUtility::isFirstPartOfStr($parsedUrl['path'], GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'));
            }
        }
        return false;
    }

    /**
     * Determines whether the URL is on the current host and belongs to the
     * current TYPO3 installation. The scheme part is ignored in the comparison.
     *
     * @param string $url URL to be checked
     * @return bool Whether the URL belongs to the current TYPO3 installation
     */
    protected function isInCurrentDomain($url): bool
    {
        $urlWithoutSchema = preg_replace('#^https?://#', '', $url);
        $siteUrlWithoutSchema = preg_replace('#^https?://#', '', GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
        return strpos($urlWithoutSchema . '/', GeneralUtility::getIndpEnv('HTTP_HOST') . '/') === 0
            && strpos($urlWithoutSchema, $siteUrlWithoutSchema) === 0;
    }

    /**
     * Determines whether the URL matches a domain
     * in the sys_domain database table.
     *
     * @param string $url Absolute URL which needs to be checked
     * @return bool Whether the URL is considered to be local
     */
    protected function isInLocalDomain($url): bool
    {
        if (GeneralUtility::isValidUrl($url)) {
            $parsedUrl = parse_url($url);
            if ($parsedUrl['scheme'] === 'http' || $parsedUrl['scheme'] === 'https') {
                $host = $parsedUrl['host'];

                if (version_compare(TYPO3_version, '9.0', '<')) {
                    return $this->isSysDomain($parsedUrl);
                }

                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                foreach ($siteFinder->getAllSites() as $site) {
                    if ($site->getBase()->getHost() === $host) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param array $parsedUrl
     * @return bool
     */
    protected function isSysDomain($parsedUrl): bool
    {
        // Removes the last path segment and slash sequences like /// (if given):
        $path = preg_replace('#/+[^/]*$#', '', $parsedUrl['host'] ?? '');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_domain');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $localDomains = $queryBuilder->select('domainName')
            ->from('sys_domain')
            ->execute()
            ->fetchAll();

        if (is_array($localDomains)) {
            foreach ($localDomains as $localDomain) {
                // strip trailing slashes (if given)
                $domainName = rtrim($localDomain['domainName'], '/');
                if ($domainName === $path) {
                    return true;
                }
            }
        }
        return false;
    }
}
