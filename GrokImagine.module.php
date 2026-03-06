<?php
namespace ProcessWire;

/**
 * GrokImagine: AI Image Generation for ProcessWire via x.ai
 * Developer: Maxim Alex
 */
class GrokImagine extends InputfieldImage implements ConfigurableModule {

    public static function getModuleInfo() {
        return array(
            'title' => 'Grok Imagine',
            'version' => 185,
            'icon' => 'camera',
            'author' => 'Maxim Alex',
            'summary' => 'Generate AI images directly in your image fields using x.ai (Grok).',
            'autoload' => 'template=admin',
            'requires' => 'ProcessWire>=3.0.0'
        );
    }

    public function ready() {
        if($this->wire('input')->post('grok_action') === 'generate') {
            $this->handleGrokRequest();
        }

        $this->addHookAfter('InputfieldImage::render', $this, 'renderGrokInterface');
        $this->addHookBefore('InputfieldImage::processInput', $this, 'processGrokInput');
        $this->addHookBefore('ProcessPageEdit::execute', $this, 'addAssets');
    }

    protected function addAssets(HookEvent $event) {
        $config = $this->wire('config');
        $url = $config->urls->siteModules . $this->className() . "/";
        $config->scripts->add($url . "GrokImagine.js?v=" . time());
    }

    protected function handleGrokRequest() {
        $apiKey = $this->grokApiKey;
        $prompt = $this->wire('input')->post->text('prompt');
        $aspect = $this->wire('input')->post->text('aspect') ?: '16:9';
        $index  = $this->wire('input')->post->int('index');
        $pageId = $this->wire('input')->post->int('page_id');
        
        $model = $this->grokModel ?: 'grok-imagine-image-pro';
        $resolution = $this->grokResolution ?: '1k';

        if(!$apiKey) {
            header('Content-Type: application/json');
            die(json_encode(['error' => 'API Key missing']));
        }

        // Resolve system prompt with page field placeholders
        $systemPrompt = trim($this->systemPrompt ?? '');
        if($systemPrompt && $pageId) {
            $page = $this->wire('pages')->get($pageId);
            if($page && $page->id) {
                $systemPrompt = preg_replace_callback('/%([a-zA-Z0-9_]+)%/', function($matches) use ($page) {
                    $fieldName = $matches[1];
                    $value = $page->get($fieldName);
                    if($value instanceof WireArray) return (string) $value->first();
                    return $value ? (string) $value : $matches[0];
                }, $systemPrompt);
            }
        }

        // System prompt is already included in the user's prompt (pre-filled in the input field)
        // No need to prepend here

        $variations = ["", ", different angle", ", alternative perspective", ", close-up shot", ", wide shot"];
        $prompt .= $variations[$index % count($variations)];

        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'aspect_ratio' => $aspect,
            'resolution' => $resolution
        ];

        $ch = curl_init('https://api.x.ai/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60
        ]);

        $response = curl_exec($ch);
        header('Content-Type: application/json');
        echo $response;
        exit;
    }

    protected function renderGrokInterface(HookEvent $event) {
        $inputfield = $event->object;
        $useFields = is_array($this->useField) ? $this->useField : [];
        if(!in_array($inputfield->name, $useFields)) return;

        $page = $inputfield->hasPage;
        $pageId = $page ? $page->id : 0;

        // Resolve system prompt placeholders to pre-fill the input field
        $systemPrompt = trim($this->systemPrompt ?? '');
        $prefillValue = '';
        if($systemPrompt) {
            $resolved = $systemPrompt;
            if($page && $page->id) {
                $resolved = preg_replace_callback('/%([a-zA-Z0-9_]+)%/', function($matches) use ($page) {
                    $fieldName = $matches[1];
                    $value = $page->get($fieldName);
                    if($value instanceof WireArray) return (string) $value->first();
                    return $value ? (string) $value : $matches[0];
                }, $systemPrompt);
            }
            $prefillValue = htmlspecialchars($resolved, ENT_QUOTES);
        }

        $markup = "
        <div class='GrokImagine-container' data-name='{$inputfield->name}' data-page-id='{$pageId}' style='border-top: 1px solid #ddd; margin-top: 15px; padding-top: 15px;'>
            <div class='uk-grid-collapse uk-grid' uk-grid>
                <div class='uk-width-expand@s uk-width-1-1'>
                    <input type='text' class='grok-prompt uk-input' value='{$prefillValue}' placeholder='Describe image...' style='border-radius: 4px 0 0 4px;'>
                </div>
                <div class='uk-width-auto@s'>
                    <select class='grok-aspect uk-select' style='border-radius: 0; min-width: 75px; border-left:0;'>
                        <option value='16:9'>16:9</option>
                        <option value='1:1'>1:1</option>
                        <option value='9:16'>9:16</option>
                        <option value='4:3'>4:3</option>
                    </select>
                </div>
                <div class='uk-width-auto@s'>
                    <select class='grok-num uk-select' style='border-radius: 0; min-width: 50px; border-left:0;'>
                        <option value='1'>1</option>
                        <option value='2'>2</option>
                        <option value='3'>3</option>
                        <option value='4'>4</option>
                    </select>
                </div>
                <div class='uk-width-auto@s uk-width-1-1'>
                    <button type='button' class='grok-btn-gen ui-button ui-widget ui-state-default' style='width: 100%; margin: 0; height: 100%; min-height: 36px; border-radius: 0 4px 4px 0;'>
                        <span class='ui-button-text'>Generate</span>
                    </button>
                </div>
            </div>
            <div class='grok-results-area uk-grid-small uk-grid uk-child-width-1-2@s uk-child-width-1-1' uk-grid style='margin-top: 10px;'></div>
            <style>
                .grok-skeleton { 
                    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
                    background-size: 200% 100%;
                    animation: grok-loading 1.5s infinite linear;
                    min-height: 100px; border-radius: 4px;
                    display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 11px;
                }
                @keyframes grok-loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
                .grok-card-item img { transition: opacity 0.5s ease-in; opacity: 0; }
                .grok-card-item img.loaded { opacity: 1; }
            </style>
        </div>";

        $event->return .= $markup;
    }

    public function processGrokInput(HookEvent $event) {
        $inputfield = $event->object;
        $field_name = $inputfield->name;
        $grok_data = $this->wire('input')->post("grok_urls_{$field_name}");
        if(!$grok_data) return;
        $page = $inputfield->hasPage; 
        if(!$page) return;
        $page->of(false);
        $field_value = $page->getUnformatted($field_name);
        foreach ($grok_data as $index => $url_string) {
            list($url, $desc) = explode('*', $url_string);
            try {
                $newFileName = $page->id . "-" . time() . "-" . $index . ".jpg";
                $http = new WireHttp();
                $tempPath = $this->wire('config')->paths->cache . 'GrokImagine/' . $newFileName;
                if(!is_dir(dirname($tempPath))) wireMkdir(dirname($tempPath));
                if($http->download($url, $tempPath)) {
                    $pagefile = new Pageimage($field_value, $tempPath);
                    $pagefile->description = $desc;
                    $field_value->add($pagefile);
                    unlink($tempPath);
                }
            } catch (\Exception $e) { $this->error($e->getMessage()); }
        }
    }

    public function getModuleConfigInputfields(array $data) {
        $inputfields = new InputfieldWrapper();

        $f = $this->wire('modules')->get('InputfieldTextarea');
        $f->name = 'systemPrompt';
        $f->label = 'System Prompt';
        $f->description = 'Optional context prepended to every prompt before sending to the API. Use `%fieldname%` placeholders to insert values from the current page (e.g. `%title%`, `%summary%`).';
        $f->notes = 'Example: "Professional product photo of %title%, studio lighting, white background"';
        $f->rows = 3;
        $f->value = $data['systemPrompt'] ?? '';
        $inputfields->add($f);

        $f = $this->wire('modules')->get('InputfieldText');
        $f->name = 'grokApiKey'; 
        $f->label = 'x.ai API Key';
        $f->description = "How to get the key: 1. Visit [console.x.ai](https://console.x.ai/). 2. Create an API Key. 3. Ensure your account has a positive balance.";
        $f->notes = "Models documentation: https://docs.x.ai/developers/models";
        $f->value = $data['grokApiKey'] ?? '';
        $inputfields->add($f);

        $f = $this->wire('modules')->get('InputfieldSelect');
        $f->name = 'grokModel';
        $f->label = 'Model';
        $f->addOptions([
            'grok-imagine-image-pro' => 'grok-imagine-image-pro (Premium)',
            'grok-imagine-image' => 'grok-imagine-image (Standard)'
        ]);
        $f->value = $data['grokModel'] ?? 'grok-imagine-image-pro';
        $f->columnWidth = 50;
        $inputfields->add($f);

        $f = $this->wire('modules')->get('InputfieldSelect');
        $f->name = 'grokResolution';
        $f->label = 'Resolution';
        $f->addOptions(['1k' => '1k', '2k' => '2k']);
        $f->value = $data['grokResolution'] ?? '1k';
        $f->columnWidth = 50;
        $inputfields->add($f);

        $f = $this->wire('modules')->get('InputfieldAsmSelect');
        $f->name = 'useField'; 
        $f->label = 'Enabled Image Fields';
        foreach($this->wire('fields') as $field) if($field->type instanceof FieldtypeImage) $f->addOption($field->name);
        $f->value = $data['useField'] ?? [];
        $inputfields->add($f);

        return $inputfields;
    }
}