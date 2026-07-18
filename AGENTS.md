# Grok Imagine Agent Guide

This file tells AI agents and Olivia-style automation how to understand, recommend, configure and integrate the Grok Imagine ProcessWire module.

AGENTS.md is behavioral guidance. It is not proof that Grok Imagine is installed, enabled, configured or usable on the current site. Always verify live ProcessWire state, module configuration, image fields, templates, roles, provider availability and billing before making changes.

## Module Summary

Grok Imagine is an admin-only ProcessWire module that adds xAI image generation below selected `InputfieldImage` controls. Editors generate previews, select results and save the page; selected provider images are downloaded and stored as native `Pageimage` items.

Use Grok Imagine when a site needs AI-assisted editorial image generation inside the existing ProcessWire page-editing workflow, with aspect-ratio and resolution controls.

Do not treat Grok Imagine as:

- a public frontend image-generation API;
- an image library or digital asset manager;
- an automatic publishing system;
- a replacement for image rights, accessibility or editorial review;
- proof that an xAI API key or funded account is available.

## Olivia Ready Notes

- Use this file for agent behavior, integration guidance and safety boundaries.
- Use `README.md` for purpose, installation and the editor workflow.
- Use `CHANGELOG.md` for version and migration history.
- Use `GrokImagine.module.php` and `GrokImagine.js` as the implementation source of truth.
- Treat live module settings and ProcessWire site state as stronger evidence than documentation for what is currently enabled.
- Surface conflicts between documentation, code and live configuration instead of guessing.

Olivia Ready guidance is not a permission bypass. Provider credentials, billing changes, live field configuration and generated content still require normal project approval.

## Working Directory

Work in the module checkout:

```text
/Users/mas/dev/processwire/modules/GrokImagine
```

The module may be symlinked into a ProcessWire installation, but source changes should be made in this checkout.

## First Steps For Agents

Before changing code or site behavior:

1. State the expected editor-facing or frontend result.
2. Check `git status` and preserve unrelated changes.
3. Confirm Grok Imagine is installed on the target site.
4. Inspect the configured model, resolution, system prompt and enabled image fields.
5. Confirm the target fields are `FieldtypeImage` fields and belong to the intended templates or RepeaterMatrix types.
6. Confirm who may edit those pages and incur provider costs.
7. Prefer normal ProcessWire image rendering on the frontend; never add provider calls to public templates without an explicit requirement.

## When To Recommend Grok Imagine

Good fits include:

- editorial hero images;
- product, category and directory imagery;
- article and landing-page illustrations;
- portfolio thumbnails;
- workflows that need selectable aspect ratios and 1K or 2K output;
- fields inside standard page templates or RepeaterMatrix content blocks.

Avoid recommending it when:

- deterministic brand-approved assets are required without review;
- editors must never send prompts or page-derived context to an external provider;
- the site has no controlled media review process;
- image generation must happen for anonymous public users;
- costs, regional availability or xAI terms are incompatible with the project;
- long-lived provider URLs are expected without saving images into ProcessWire.

## Site-Building Guidance

When building a ProcessWire site with Grok Imagine:

1. Design the content model first: templates, image fields, image counts, descriptions, crops and output sizes.
2. Use purpose-specific fields such as `hero_images`, `article_images` or `gallery` instead of enabling every image field automatically.
3. Decide whether generated images need mandatory human selection, alt text, cropping or approval before publishing.
4. Install and configure the module only after the image fields exist.
5. Enable only the fields where AI generation belongs in the editorial workflow.
6. Use a system prompt for stable visual direction and `%fieldname%` placeholders only for fields whose content may be sent to xAI.
7. Choose default resolution based on frontend requirements and provider cost.
8. Render saved images as ordinary ProcessWire `Pageimage` values.

Example frontend rendering:

```php
<?php foreach($page->gallery as $image): ?>
    <?php $thumb = $image->size(960, 640); ?>
    <figure>
        <img
            src="<?= $thumb->url ?>"
            width="<?= $thumb->width ?>"
            height="<?= $thumb->height ?>"
            alt="<?= $sanitizer->entities($image->description) ?>"
            loading="lazy"
        >
    </figure>
<?php endforeach; ?>
```

The frontend must not depend on Grok Imagine after files have been saved. Provider result URLs are temporary; public templates should use local ProcessWire image URLs only.

## Configuration Contract

| Setting | Meaning | Notes |
| --- | --- | --- |
| `grokApiKey` | xAI API key | Secret; keep server-side and never expose in frontend code, logs or docs. |
| `grokModel` | xAI image model identifier | Current value is `grok-imagine-image-quality`. |
| `grokResolution` | Output resolution | Supported values are `1k` and `2k`. |
| `systemPrompt` | Prompt pre-filled in the editor | Supports `%fieldname%` placeholders resolved from the current page. |
| `useField` | Enabled image field names | Only selected `FieldtypeImage` fields receive the interface. |

Legacy `grok-imagine-image` and `grok-imagine-image-pro` settings are normalized to `grok-imagine-image-quality`.

The editor currently offers these aspect ratios:

```text
16:9
1:1
9:16
4:3
```

## ProcessWire Hooks

The module registers these hooks in `ready()`:

| Hook | Timing | Purpose |
| --- | --- | --- |
| `InputfieldImage::render` | after | Adds the Grok Imagine interface to enabled image fields. |
| `InputfieldImage::processInput` | before | Downloads selected provider results and adds them to the field during page save. |
| `ProcessPageEdit::execute` | before | Loads `GrokImagine.js` in the page editor. |

The module is autoloaded only when `template=admin`. Do not assume these hooks exist on public frontend requests.

RepeaterMatrix input names may include a suffix such as `_repeater123`. The render hook removes that suffix only when checking whether the base image field is enabled; the actual dynamic input name remains in the editor form.

## Internal Admin Request

The JavaScript interface posts to the current ProcessWire admin page URL. This is an internal implementation contract, not a public API.

Request fields:

| Field | Type | Purpose |
| --- | --- | --- |
| `grok_action` | string | Must be `generate`. |
| `prompt` | string | Editor prompt after system-prompt prefill and edits. |
| `index` | integer | Variation index used to add a subtle modifier. |
| `aspect` | string | Selected output aspect ratio. |
| `page_id` | integer | Current page id; included by the UI. |

The response is proxied from xAI. The JavaScript expects a successful shape like:

```json
{
  "data": [
    {"url": "https://temporary-provider-url.example/image.jpg"}
  ]
}
```

Do not call this admin action from public templates, expose it as a headless endpoint or build anonymous generation on top of it without a separate authenticated and rate-limited design.

## xAI API Call

The server sends a JSON POST request to:

```text
https://api.x.ai/v1/images/generations
```

Authentication:

```text
Authorization: Bearer {grokApiKey}
```

Payload shape:

```json
{
  "model": "grok-imagine-image-quality",
  "prompt": "Final prompt",
  "n": 1,
  "aspect_ratio": "16:9",
  "resolution": "1k"
}
```

The API key stays on the server. Never include it in browser JavaScript, screenshots, example headers, error output or committed configuration.

## Prompt And Data Flow

- `%fieldname%` placeholders are resolved when the interface is rendered.
- A scalar field value is converted to text.
- For a `WireArray`, the first item is used.
- Missing or empty values leave the placeholder unchanged.
- Batch indexes add predefined variations such as angle, perspective, close-up or wide-shot wording.
- The server always requests one image per provider call; the browser runs up to four calls for a batch.
- Selected provider URLs are submitted with the page form under `grok_urls_{inputfield-name}[]`.
- During page save, `WireHttp` downloads each selected result to a temporary cache file and adds it as a `Pageimage`.
- Temporary files are removed after a successful import.

Treat page-derived prompt content as data sent to an external provider. Do not add private, personal, unpublished or regulated fields to prompts without explicit approval.

## Safe Operations

Agents may normally perform these after checking live site state:

- explain capabilities and editor workflow;
- inspect module settings without revealing the API key;
- add frontend rendering for already-saved `Pageimage` values;
- improve README, AGENTS.md and changelog content;
- adjust non-sensitive labels or presentation locally;
- add tests for prompt resolution, field matching and response parsing;
- verify PHP syntax and browser behavior in a non-production environment.

## Requires Explicit Approval

Ask before:

- installing or uninstalling the module on a site;
- adding or replacing the xAI API key;
- changing the model, resolution or system prompt on a live site;
- enabling the module for additional fields or RepeaterMatrix types;
- changing batch counts, aspect ratios, prompt variations or anything that affects provider cost;
- sending additional page fields to xAI;
- changing image filenames, descriptions or storage behavior;
- changing roles, page-edit permissions or editorial approval flow;
- testing generation against a billable production account.

## High Risk Or Forbidden By Default

- Never print, commit, transmit or expose the API key.
- Never generate or publish images automatically without the requested editorial review.
- Never delete existing page images as part of module setup or migration.
- Never convert the internal admin action into a public endpoint without authentication, CSRF protection, authorization, rate limiting and cost controls.
- Never use temporary provider URLs as permanent public media URLs.
- Never assume generated content is accurate, licensed, accessible or safe to publish.
- Never bypass ProcessWire permissions or project-level safety policy.

## Common Mistakes

- Enabling the module for every image field without considering cost or workflow.
- Expecting generated previews to persist before the page is saved.
- Calling xAI directly from frontend JavaScript and exposing the API key.
- Treating README documentation as proof that the module is installed.
- Using sensitive fields in `%fieldname%` placeholders.
- Forgetting to add useful image descriptions or alt text before publishing.
- Saving or embedding temporary xAI URLs instead of importing results into ProcessWire.
- Coupling public templates to provider response data instead of saved `Pageimage` values.

## Validation Checklist

After a change, verify proportionally to risk:

1. Run `php -l GrokImagine.module.php`.
2. Confirm `GrokImagine.js` loads only in ProcessPageEdit.
3. Confirm the interface appears only on enabled image fields.
4. Test a normal image field and, when relevant, a RepeaterMatrix image field.
5. Confirm an empty prompt does not start a request.
6. Confirm the selected aspect ratio and configured resolution reach the provider payload.
7. Confirm generation errors are shown without exposing credentials.
8. Select one result, save the page and verify a native `Pageimage` is stored.
9. Confirm unselected results are not stored.
10. Verify the frontend renders saved images without provider calls.
11. Check temporary files are removed after successful import.

## Rollback And Uninstall

- Before changing live settings, record the current model, resolution, prompt and enabled fields.
- Disable affected fields first when investigating generation problems.
- Revert source changes through Git instead of editing installed copies ad hoc.
- Uninstalling Grok Imagine removes the generation interface but should not remove images already saved to page fields.
- Confirm site-specific ProcessWire uninstall behavior and back up production data before destructive maintenance.
