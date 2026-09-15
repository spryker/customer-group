/**
 * Copyright (c) 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

var TableHandler = require('./table-handler');
var tableAccess = require('ZedGuiModules/libs/table/table-access');

/**
 * @param {string} sourceTableSelector
 * @param {string} destinationTableSelector
 * @param {string} checkboxSelector
 * @param {string} labelCaption
 * @param {string} labelId
 * @param {string} formFieldId
 * @param {function} onRemoveCallback
 *
 * @return {TableHandler}
 */
function create(
    sourceTableSelector,
    destinationTableSelector,
    checkboxSelector,
    labelCaption,
    labelId,
    formFieldId,
    onRemoveCallback,
) {
    var $sourceTable = $(sourceTableSelector);

    var tableHandler = TableHandler.create(
        $sourceTable,
        $(destinationTableSelector),
        labelCaption,
        labelId,
        formFieldId,
        onRemoveCallback,
    );

    if (!$sourceTable.length) {
        return tableHandler;
    }

    $sourceTable.on('change', checkboxSelector, function () {
        var $checkbox = $(this);
        var info = $.parseJSON($checkbox.attr('data-info'));

        if (tableHandler.isCheckboxActive($checkbox)) {
            tableHandler.addSelectedItem(info.id, info.email, info.firstName, info.lastName);

            return;
        }

        tableHandler.removeSelectedItem(info.id);
    });

    tableAccess.requestTable($sourceTable[0], function (handle) {
        handle.on('draw', function () {
            var selector = tableHandler.getSelector();

            handle
                .raw()
                .rows()
                .data()
                .each(function (item) {
                    var idItem = parseInt(item[1], 10);

                    if (selector.isItemSelected(idItem)) {
                        tableHandler.checkCheckbox($('input[value="' + idItem + '"]', $sourceTable));
                    }
                });
        });
    });

    return tableHandler;
}

module.exports = {
    create: create,
    CHECKBOX_CHECKED_STATE_CHECKED: TableHandler.CHECKBOX_CHECKED_STATE_CHECKED,
    CHECKBOX_CHECKED_STATE_UN_CHECKED: TableHandler.CHECKBOX_CHECKED_STATE_UN_CHECKED,
};
