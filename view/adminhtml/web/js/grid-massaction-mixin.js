define([
    'Magento_Ui/js/modal/confirm'
], function (confirm) {
    'use strict';

    return function (target) {
        var massactionPrototype = window.varienGridMassaction && window.varienGridMassaction.prototype,
            originalApply;

        if (!massactionPrototype || massactionPrototype.mbAdminReindexPatched) {
            return target;
        }

        originalApply = massactionPrototype.apply;
        massactionPrototype.apply = function () {
            var item = this.getSelectedItem(),
                fieldName,
                hasSelection = window.varienStringArray.count(this.checkedString) > 0;

            if (!item || !item.allow_no_selection || hasSelection) {
                return originalApply.apply(this, arguments);
            }

            this.currentItem = item;
            fieldName = item.field ? item.field : this.formFieldName;

            if (item.confirm) {
                confirm({
                    content: item.confirm,
                    actions: {
                        confirm: this.onConfirm.bind(this, fieldName, item)
                    }
                });
            } else {
                this.onConfirm(fieldName, item);
            }

            return undefined;
        };
        massactionPrototype.mbAdminReindexPatched = true;

        return target;
    };
});
