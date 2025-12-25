# drupal_ckeditor5_filter — Lazy-load Images & Iframes

This module provides a custom Text Filter that automatically adds:
```bash
loading="lazy"
```
to:

- Images ( &lt;img&gt; )
- Iframes ( &lt;iframe&gt; )

It only applies the attribute when it is not already present, so you can still explicitly control behavior using:
```bash
loading="eager"
```
Works for:

- Images added in the CKEditor
- Embedded iframes

This was built and tested on **Drupal 11** with **CKEditor 5**.

I created it for a project that had around **20,000 pages** with images uploaded by many editors. We needed a **global solution** that didn’t require editing every page manually.

---

## Why not use Drupal Core “Lazy-load images”?

Drupal core has a text filter called:

Lazy-load images

However:

- Core adds lazy loading only when width and height exist
- It works only for images
- Behavior can be inconsistent across embeds

This module:

- Adds loading="lazy" to all &lt;img&gt; and &lt;iframe&gt;
- Works even when width/height are missing
- Skips anything explicitly marked loading="eager"

---

## IMPORTANT — disable Drupal core Lazy-load filter

To avoid conflicts:

1. Go to Configuration → Content authoring → Text formats and editors
2. Edit each text format
3. Uncheck:

    - Lazy-load images

This module replaces that behavior.

---

## Installation

Place the module in:
```bash
/modules/custom/drupal_ckeditor5_filter
```
Enable it:
```bash
drush en drupal_ckeditor5_filter -y
```
Clear cache:
```bash
drush cr
```
---

## Enable the filter

Go to:

Configuration → Content authoring → Text formats and editors

Then:

1. Scroll to Filters
2. Enable:

    - Lazy load iframes and images

3. Save

---

## How it works

The filter adds:
```bash
loading="lazy"
```
to:

- &lt;img&gt; elements without loading attribute
- &lt;iframe&gt; elements without loading attribute

If loading="eager" is already present, it is left as-is.

---

## Example

Input:
```bash
<img src="/example.jpg">
<iframe src="https://example.com"></iframe>
```
Output:
```bash
<img src="/example.jpg" loading="lazy">
<iframe src="https://example.com" loading="lazy"></iframe>
```
Unchanged example:
```bash
loading="eager"
```
---

## Compatibility

- Drupal 11 (tested)
- CKEditor 5

---

## Optional: Using data-src + JavaScript (alternative approach)

Instead of adding loading="lazy", you can also control loading manually.

You can store the real URL in **data-src** and replace it with JavaScript when you want to load it.

Updated version — convert src → data-src (for JavaScript-controlled lazy loading)

This version moves the real URL into data-src so loading can be handled fully by JavaScript.

```php
$dom = Html::load($text);
$xpath = new \DOMXPath($dom);

// Handle <img>
foreach ($xpath->query('//img[not(@loading="eager")]') as $element) {
  assert($element instanceof \DOMElement);

  $src = $element->getAttribute('src');

  if ($src && !$element->getAttribute('data-src')) {
    $element->setAttribute('data-src', $src);
    $element->removeAttribute('src'); // optional — or set placeholder
  }
}

// Handle <iframe>
foreach ($xpath->query('//iframe[not(@loading="eager")]') as $element) {
  assert($element instanceof \DOMElement);

  $src = $element->getAttribute('src');

  if ($src && !$element->getAttribute('data-src')) {
    $element->setAttribute('data-src', $src);
    $element->removeAttribute('src');
  }
}

return Html::serialize($dom);

```


You can then pair this with JavaScript to load elements only when they become visible:

```javascript
// Lazy-load any element with data-src when it enters the viewport
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;

    const el = entry.target;
    const realSrc = el.getAttribute("data-src");

    if (realSrc) {
      el.setAttribute("src", realSrc);
      el.removeAttribute("data-src");
    }

    observer.unobserve(el);
  });
});

// Attach to all images/iframes that use data-src
document.querySelectorAll("[data-src]").forEach(el => observer.observe(el));
```

## Contributing

Suggestions and improvements are welcome.
