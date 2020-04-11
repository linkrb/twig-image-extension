<?php

namespace Umanit\TwigImage;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * Class Runtime
 */
class Runtime
{
    /** @var CacheManager */
    private $cacheManager;

    /** @var array */
    private $filters;

    /**
     * AppExtension constructor.
     *
     * @param CacheManager  $cacheManager
     * @param FilterManager $filterManager
     */
    public function __construct(CacheManager $cacheManager, FilterManager $filterManager)
    {
        $this->cacheManager = $cacheManager;
        $this->filters      = $filterManager->getFilterConfiguration()->all();
    }

    /**
     * @param string $path
     * @param string $placeholderFilter
     * @param string $srcFilter
     * @param array  $srcsetFilters
     * @param string $alt
     * @param string $class
     * @param string $sizes
     *
     * @return string
     */
    public function getUmanitImageFigure(
        string $path,
        string $placeholderFilter,
        string $srcFilter,
        array $srcsetFilters = [],
        string $alt = '',
        string $class = '',
        string $sizes = '100vw'
    ): string {
        $srcsetHtml      = $this->getUmanitImageSrcset($path, $srcsetFilters);
        $placeholderPath = $this->cacheManager->getBrowserPath($path, $placeholderFilter);
        $srcPath         = $this->cacheManager->getBrowserPath($path, $srcFilter);

        return <<<HTML
<figure>
  <img 
    alt="$alt"
    class="lazy lazy-placeholder $class"
    src="$placeholderPath"
    data-src="$srcPath"
    sizes="$sizes"
    data-srcset="$srcsetHtml"
  >
  <noscript>
    <img
      class="$class"
      alt="$alt"
      src="$srcPath"
      sizes="$sizes"
      srcset="$srcsetHtml"
    >
  </noscript>
</figure>
HTML;
    }

    /**
     * @param string $imgPath
     * @param string $imgFilter
     * @param array  $sources
     * @param string $alt
     * @param string $class
     *
     * @return string
     */
    public function getUmanitImagePicture(
        string $imgPath,
        string $imgFilter,
        array $sources = [],
        string $alt = '',
        string $class = ''
    ): string {
        $src         = $this->cacheManager->getBrowserPath($imgPath, $imgFilter);
        $sourcesHtml = [];

        foreach ($sources as $sourcePath => $sourceDataset) {
            $sourceFilters = $sourceDataset['filters'] ?? $sourceDataset;
            $media         = '';
            $srcSet        = $this->getUmanitImageSrcset($sourcePath, $sourceFilters);

            if (isset($sourceDataset['media'])) {
                $media = sprintf('media="%s"', $sourceDataset['media']);
            }

            $sourcesHtml[] = <<<HTML
<source $media srcset="$srcSet">
HTML;
        }

        $sourcesHtml = implode("\n", $sourcesHtml);

        return <<<HTML
<picture>
  $sourcesHtml
  <img
    class="$class"
    src="$src"
    alt="$alt"
  >
</picture>
HTML;

    }

    /**
     * @param string $path
     * @param array  $filters
     *
     * @return string
     */
    public function getUmanitImageSrcset(string $path, array $filters): string
    {
        $srcset = [];

        foreach ($filters as $filter) {
            $filterConfig = $this->filters[$filter];
            $width        = null;

            if (isset($filterConfig['filters']['relative_resize']['widen'])) {
                $width = $filterConfig['filters']['relative_resize']['widen'];
            } elseif (isset($filterConfig['filters']['thumbnail']['size'])) {
                $width = current($filterConfig['filters']['thumbnail']['size']);
            }

            if (null === $width) {
                throw new \LogicException(
                    sprintf('Can not determine the width to use for the filter "%s"', $filter)
                );
            }

            $srcset[] = sprintf('%s %uw', $this->cacheManager->getBrowserPath($path, $filter), $width);
        }

        return implode(', ', $srcset);
    }
}
