<?php
namespace TrustComponent\TrustCaptchaMagento2\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $url = $this->getUrl('trustcaptcha/settings/testConnection');
        $btnId = 'tc-conn-test-' . $element->getHtmlId();
        $outId = $btnId . '-out';

        return <<<HTML
<button type="button" class="button" id="{$btnId}">Test connection</button>
<span id="{$outId}" style="margin-left:8px;"></span>
<script>
require(['jquery', 'Magento_Ui/js/modal/alert'], function($){
    document.getElementById('{$btnId}').addEventListener('click', function(){
        var out = document.getElementById('{$outId}');
        out.textContent = '⏳ Testing…';
        out.style.color = '';
        $.ajax({
            url: '{$url}',
            type: 'POST',
            dataType: 'json',
            data: { form_key: window.FORM_KEY }
        }).done(function(j){
            if (j && j.ok) { out.textContent = '✓ ' + (j.message || 'OK'); out.style.color = '#1e8449'; }
            else { out.textContent = '✗ ' + (j && j.message ? j.message : 'Failed'); out.style.color = '#b32d2e'; }
        }).fail(function(xhr){
            out.textContent = '✗ ' + (xhr.statusText || 'Request failed');
            out.style.color = '#b32d2e';
        });
    });
});
</script>
HTML;
    }
}
