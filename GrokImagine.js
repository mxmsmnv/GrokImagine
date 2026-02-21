(function($) {
    $(document).ready(function() {
        $(document).on('click', '.grok-btn-gen', function(e) {
            e.preventDefault();
            const $container = $(this).closest('.GrokImagine-container');
            const $btn = $(this);
            const $preview = $container.find('.grok-results-area');
            const prompt = $container.find('.grok-prompt').val();
            const num = parseInt($container.find('.grok-num').val());
            const aspect = $container.find('.grok-aspect').val();
            const fieldName = $container.data('name');

            if(!prompt) return;

            $btn.addClass('ui-state-disabled').find('.ui-button-text').text('Imagining...');
            $preview.html(''); 

            for(let i=0; i < num; i++) {
                $preview.append(`
                    <div class='grok-slot' id='grok-slot-${i}' style='margin-bottom:12px;'>
                        <div class='grok-skeleton uk-border-rounded' style='width:100%; aspect-ratio:${aspect.replace(':','/')}'>
                            Generating...
                        </div>
                    </div>
                `);
            }

            let completed = 0;
            for(let i=0; i < num; i++) {
                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: { grok_action: 'generate', prompt: prompt, index: i, aspect: aspect },
                    success: function(response) {
                        if(response.data && response.data[0]) {
                            const item = response.data[0];
                            const html = `
                                <div class='grok-card-item' style='cursor:pointer;'>
                                    <div class='uk-panel uk-border-rounded uk-overflow-hidden' 
                                         style='border: 3px solid #eee; transition: 0.2s; position: relative;'>
                                        <img src='${item.url}' onload='$(this).addClass("loaded")' style='display: block; width: 100%; height: auto;'>
                                        <input type='hidden' class='grok-hidden-input' data-url='${item.url}'>
                                        <div class='grok-badge' style='display:none; position:absolute; top:8px; right:8px; background:#2db7f5; color:#fff; border-radius:50%; width:24px; height:24px; text-align:center; line-height:24px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);'>✓</div>
                                    </div>
                                    <p style='font-size:10px; text-align:center; margin-top:5px; color:#999;'>CLICK TO SELECT</p>
                                </div>`;
                            $(`#grok-slot-${i}`).html(html);
                        } else {
                            $(`#grok-slot-${i}`).html("<div style='color:red; font-size:10px;'>Error</div>");
                        }
                    },
                    error: function() {
                        $(`#grok-slot-${i}`).html("<div style='color:red; font-size:10px;'>Failed</div>");
                    },
                    complete: function() {
                        completed++;
                        if(completed === num) {
                            $btn.removeClass('ui-state-disabled').find('.ui-button-text').text('Generate');
                        }
                    }
                });
            }
        });

        $(document).on('click', '.grok-card-item', function() {
            const $card = $(this).find('.uk-panel');
            const $input = $(this).find('.grok-hidden-input');
            const $badge = $(this).find('.grok-badge');
            const fieldName = $(this).closest('.GrokImagine-container').data('name');
            const prompt = $(this).closest('.GrokImagine-container').find('.grok-prompt').val();

            if($input.attr('name')) {
                $input.removeAttr('name');
                $card.css('border-color', '#eee');
                $badge.hide();
            } else {
                $input.attr('name', 'grok_urls_' + fieldName + '[]');
                $input.val($input.data('url') + '*' + prompt);
                $card.css('border-color', '#2db7f5');
                $badge.show();
            }
        });
    });
})(jQuery);