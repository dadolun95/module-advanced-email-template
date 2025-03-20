<?php

namespace MageOS\AdvancedEmailTemplate\Block\Adminhtml\Template\Edit;

use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Form extends \Magento\Email\Block\Adminhtml\Template\Edit\Form
{
    private $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Variable\Model\VariableFactory $variableFactory,
        \Magento\Variable\Model\Source\Variables $variables,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->serializer = $serializer;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $variableFactory,
            $variables,
            $data,
            $serializer,
            $secureRenderer
        );
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Template Information'), 'class' => 'fieldset-wide']
        );

        $templateId = $this->getEmailTemplate()->getId();
        $fieldset->addField(
            'currently_used_for',
            'label',
            [
                'label' => __('Currently Used For'),
                'container_id' => 'currently_used_for',
                'after_element_html' => $this->secureRenderer->renderTag(
                    'script',
                    [],
                    'require(["prototype"], function () {' .
                    (!$this->getEmailTemplate()->getSystemConfigPathsWhereCurrentlyUsed() ? '$(\'' .
                        'currently_used_for' .
                        '\').hide(); ' : '') .
                    '});',
                    false
                ),
            ]
        );

        $fieldset->addField(
            'template_code',
            'text',
            ['name' => 'template_code', 'label' => __('Template Name'), 'required' => true]
        );
        $fieldset->addField(
            'template_subject',
            'text',
            ['name' => 'template_subject', 'label' => __('Template Subject'), 'required' => true]
        );
        $fieldset->addField('orig_template_variables', 'hidden', ['name' => 'orig_template_variables']);
        $fieldset->addField(
            'variables',
            'hidden',
            ['name' => 'variables', 'value' => $this->serializer->serialize($this->getVariables())]
        );
        $fieldset->addField('template_variables', 'hidden', ['name' => 'template_variables']);

        $insertVariableButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class,
            '',
            [
                'data' => [
                    'type' => 'button',
                    'label' => __('Insert Variable...'),
                    'onclick' => 'templateControl.openVariableChooser();return false;',
                ]
            ]
        );

        $fieldset->addField('insert_variable', 'note', ['text' => $insertVariableButton->toHtml(), 'label' => '']);

        $fieldset->addField(
            'template_text',
            'textarea',
            [
                'name' => 'template_text',
                'label' => __('Template Content'),
                'title' => __('Template Content'),
                'required' => true,
                'after_element_html' => "
<input id=\"grapesjs-template-html\" type=\"hidden\" />
<input id=\"grapesjs-template-css\" type=\"hidden\" />
<input id=\"grapesjs-template-data\" type=\"hidden\" value='{\"pages\": [{\"component\": \"\"}]}' />
<div id='grapesjs'></div>
<script type=\"text/javascript\">
require(['jquery', 'MageOS_AdvancedEmailTemplate/js/grapes.min', 'MageOS_AdvancedEmailTemplate/js/grapesjs-preset-newsletter'],
    function ($, grapesjs, nlPlugin) {

        var inlineStorage = (editor) => {
            window.grapesjsTemplateData = document.getElementById('grapesjs-template-data');
            window.grapesjsTemplateHtml = document.getElementById('grapesjs-template-html');
            window.grapesjsTemplateCss = document.getElementById('grapesjs-template-css');
            editor.Storage.add('inline', {
                  load() {
                    return JSON.parse(window.grapesjsTemplateData.value || '{}');
                  },
                  store(data) {
                    const component = editor.Pages.getSelected().getMainComponent();
                    window.grapesjsTemplateData.value = JSON.stringify(data);
                    window.grapesjsTemplateHtml.value = `\${editor.getHtml({ component })}`;
                    window.grapesjsTemplateCss.value = `\${editor.getCss({ component })}`;
                  },
            });
        };

        grapesjs.plugins.add('gjs-preset-newsletter-2', nlPlugin.default);
        var editor = grapesjs.init({
            container: '#grapesjs',
            plugins: ['gjs-preset-newsletter-2', inlineStorage],
            pluginsOpts: {
                'gjs-preset-newsletter-2': {}
            },
            storageManager: {
                type: 'inline',
                autosave: true,
                autoload: true
            },
        });
        window.grapesjsEditor = editor;
        window.grapesjsTemplateData.value = $('#email_template_edit_form #template_grapesjs_json').text();
        $('#email_template_edit_form #template_text').hide();
        $('#email_template_edit_form #field_template_grapesjs_json').hide();
        window.grapesjsEditor.on('component:selected', (component) => {
          window.grapesjsSelectedElement = component;
        });
    }
);
</script>
"
            ]
        );

        if (!$this->getEmailTemplate()->isPlain()) {
            $fieldset->addField(
                'template_styles',
                'textarea',
                [
                    'name' => 'template_styles',
                    'label' => __('Template Styles'),
                    'container_id' => 'field_template_styles'
                ]
            );
        }

        $fieldset->addField(
            'template_grapesjs_json',
            'textarea',
            [
                'name' => 'template_grapesjs_json',
                'label' => __('Template GrapesJS JSON'),
                'container_id' => 'field_template_grapesjs_json',
            ]
        );

        if ($templateId) {
            $form->addValues($this->getEmailTemplate()->getData());
        }

        $values = $this->_backendSession->getData('email_template_form_data', true);
        if ($values) {
            $form->setValues($values);
        }

        $this->setForm($form);
        return $this;
    }
}
