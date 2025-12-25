<?php

declare(strict_types = 1);

namespace Drupal\drupal_ckeditor5_filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Provides a filter to lazy load iframes and images.
 */
#[Filter(
  id: "filter_iframe_image_lazy_load",
  title: new TranslatableMarkup("Lazy load iframes and images"),
  description: new TranslatableMarkup("Instruct browsers to lazy load iframes and images. Results can be overridden by <code>&lt;iframe loading=&quot;eager&quot;&gt;</code> and <code>&lt;img loading=&quot;eager&quot;&gt;</code>."),
  type: FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
  weight: 15
)]
final class FilterIframeImageLazyLoad extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    $result = new FilterProcessResult($text);

    // If there are no iframes or images, return early.
    if (stripos($text, '<img ') === FALSE || stripos($text, '<iframe') === FALSE) {
      return $result;
    }

    return $result->setProcessedText($this->transformIframeImages($text));
  }

  /**
   * Transform markup of iframe and images to include loading="lazy".
   *
   * @param string $text
   *   The markup to transform.
   *
   * @return string
   *   The transformed text with loading attribute added.
   */
  private function transformIframeImages(string $text): string {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    // Only set loading="lazy" if no existing loading attribute is specified.
    foreach ($xpath->query('//img[not(@loading="eager")]') as $element) {
      assert($element instanceof \DOMElement);
      $element->setAttribute('loading', 'lazy');
    }
    // Only set loading="lazy" if no existing loading attribute is specified.
    foreach ($xpath->query('//iframe[not(@loading="eager")]') as $element) {
      assert($element instanceof \DOMElement);
      $element->setAttribute('loading', 'lazy');
    }
    return Html::serialize($dom);
  }

}
