# AMP Picture compatibility

The Mini Plugin to add support for `<picture>` element

## Notes

- It will convert <picture> element into amp-img
- Just Like img tag it will determine image dimensions automatically. 
- You can remove plugin once the [#6676](https://github.com/ampproject/amp-wp/issues/6676) issue is fixed and released.

## Plugin Structure

```markdown
.
├── sanitizers
│   ├── class-sanitizer.php
└── amp-skeleton-compat.php
```
## Sanitizers

The plugin uses `amp_content_sanitizers` filter to add custom sanitizers and convert `<picture>` element to `<amp-img>`.

### Need a feature in plugin?
Feel free to create a issue and will add more examples.