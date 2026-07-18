# Grok Imagine

Grok Imagine adds xAI image generation directly to ProcessWire image fields, so editors can generate, preview, select and save images from the page editor.

![Grok Imagine](assets/grok-imagine-illustration.png)

It is made for editorial sites, product catalogs, landing pages, portfolios, directories and content teams that need fast AI-assisted image creation without a separate media workflow.

**Author:** Maxim Semenov<br>
**Website:** [smnv.org](https://smnv.org)<br>
**Email:** [maxim@smnv.org](mailto:maxim@smnv.org)

If this project helps your work, consider supporting future development: [GitHub Sponsors](https://github.com/sponsors/mxmsmnv) or [smnv.org/sponsor](https://smnv.org/sponsor/).

## What Grok Imagine Does

- Adds an image-generation interface below selected ProcessWire image fields.
- Generates one to four variations from a single prompt.
- Loads results progressively as provider requests complete.
- Adds subtle prompt variations for more diverse batches.
- Supports reusable system prompts with `%fieldname%` placeholders.
- Resolves placeholders from the page being edited, such as `%title%` or `%summary%`.
- Supports 16:9, 1:1, 9:16 and 4:3 aspect ratios.
- Supports 1K and 2K output resolution.
- Lets editors preview and select results before saving.
- Stores selected results as native ProcessWire `Pageimage` items.
- Recognizes enabled image fields inside RepeaterMatrix items.

## Image Model

Grok Imagine uses the current `grok-imagine-image-quality` model. Existing settings that reference `grok-imagine-image` or `grok-imagine-image-pro` are migrated automatically to the Quality model.

## Editorial Workflow

1. An editor opens a ProcessWire page with an enabled image field.
2. The configured system prompt is pre-filled and page placeholders are resolved.
3. The editor adjusts the prompt, aspect ratio and number of variations.
4. Results appear progressively below the field.
5. The editor selects the images to keep.
6. Saving the page downloads those images and adds them to the field permanently.

Unselected previews are not added to the page.

## Website Integration

Grok Imagine is an admin editing tool, not a frontend widget. Generated files become normal ProcessWire images and are rendered with the same template code as uploaded images:

```php
<?php foreach($page->gallery as $image): ?>
    <figure>
        <img src="<?= $image->url ?>" alt="<?= $sanitizer->entities($image->description) ?>">
    </figure>
<?php endforeach; ?>
```

This keeps frontend architecture independent from xAI. Public templates do not need provider credentials or direct API calls.

## Installation

1. Copy the `GrokImagine` folder into `/site/modules/`.
2. In ProcessWire Admin, refresh modules.
3. Install `Grok Imagine`.
4. Open the module configuration.

## Configuration

1. Create an API key at [console.x.ai](https://console.x.ai/).
2. Ensure the xAI account has a positive balance.
3. Enter the API key in the module settings.
4. Choose the default output resolution.
5. Optionally define a reusable system prompt.
6. Select the image fields that should display the Grok Imagine interface.

Example system prompt:

```text
Editorial image for %title%, modern composition, natural light, no text
```

## Agent Documentation

See [AGENTS.md](AGENTS.md) for AI-agent guidance, site-building patterns, ProcessWire hooks, request contracts, safety boundaries and validation steps.

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## Author

Maxim Semenov<br>
[smnv.org](https://smnv.org)<br>
[maxim@smnv.org](mailto:maxim@smnv.org)

## License

MIT
