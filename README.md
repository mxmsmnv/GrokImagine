# GrokImagine

**Version:** 1.8.5  
**Author:** Maxim Alex  
**Website:** [smnv.org](https://smnv.org)  
**Email:** maxim@smnv.org  
**Release Date:** March 5, 2026  
**License:** MIT

GrokImagine is a ProcessWire module that allows you to generate high-quality AI images directly within your `Pageimage` fields using the **x.ai (Grok)** API. 

## Features

- **Progressive Loading**: Images appear one-by-one as they are generated.
- **Batch Generation**: Generate up to 4 variations at once with a single prompt.
- **Smart Variations**: Automatically adds subtle prompt differences to ensure variety in batch results.
- **Format Support**: Choose between 16:9, 1:1, 9:16, and 4:3 aspect ratios.
- **Model Selection**: Supports `grok-imagine-image-pro` and `grok-imagine-image`.
- **Resolution Control**: Toggle between 1k and 2k resolutions.
- **Interactive UI**: Preview results, select the ones you like, and save them directly to the page.
- **System Prompt**: Define a reusable base prompt in module settings, pre-filled into the input field on every page. Supports `%fieldname%` placeholders (e.g. `%title%`) that are automatically resolved from the current page's field values.

## Installation

1. Upload the `GrokImagine` folder to your `/site/modules/` directory.
2. In the ProcessWire admin, go to **Modules > Refresh**.
3. Find **Grok Imagine** and click **Install**.

## Configuration

1. Go to the module settings.
2. Enter your **x.ai API Key** (obtainable at [console.x.ai](https://console.x.ai/)).
3. Optionally set a **System Prompt** — a base context pre-filled into the prompt field on every page. Use `%fieldname%` placeholders to inject page field values (e.g. `Professional photo of %title%, white background`).
4. Select which image fields should have the Grok Imagine interface enabled.
5. Choose your preferred default model and resolution.

## How to Use

1. Open a page for editing that contains an enabled image field.
2. Locate the **Grok Imagine** bar below the image field.
3. The prompt field will be pre-filled with the system prompt (if configured). Edit or extend it as needed.
4. Select your desired aspect ratio and quantity, then click **Generate**.
5. Once images appear, click on the ones you want to keep (a blue checkmark will appear).
6. **Save the page** to download and store the selected images.

---

## License

MIT License - Free to use in personal and commercial projects.
