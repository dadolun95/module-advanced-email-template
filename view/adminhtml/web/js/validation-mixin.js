define(['jquery'], function ($) {
    'use strict';

    var validationMixin = {
        options: {
            ignore: ':disabled, .ignore-validate, .no-display.template, ' +
                ':disabled input, .ignore-validate input, .no-display.template input, ' +
                ':disabled select, .ignore-validate select, .no-display.template select, ' +
                ':disabled textarea, .ignore-validate textarea, .no-display.template textarea, #grapesjs form, #grapesjs input',
        }
    };

    return function (mageValidation) {
        $.widget('mage.validation', mageValidation, validationMixin);
        return $.mage.validation;
    };
});
