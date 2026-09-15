/**
 * Copyright (c) 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

var ItemTable = require('./parts/table');

var sourceTabSelector = '#assigned-items-source-tab';
var destinationTabSelector = '#assigned-items-destination-tab';
var fieldToBeDeassignedItemIds = '#js-item-to-de-assign-ids-csv-field';
var checkboxSelector = '.js-item-checkbox';

var sourceTableSelector = sourceTabSelector + ' table.table';
var destinationTabLabelSelector = destinationTabSelector + '-label';
var destinationTableSelector = destinationTabSelector + '-table';
var buttonSelectAllSelector = sourceTabSelector + ' .js-de-select-all-button a';
var tableHandler;

/**
 * @return {void}
 */
function initialize() {
    tableHandler = ItemTable.create(
        sourceTableSelector,
        destinationTableSelector,
        checkboxSelector,
        $(destinationTabLabelSelector).text(),
        destinationTabLabelSelector,
        fieldToBeDeassignedItemIds,
        onRemove,
    );

    tableHandler.getInitialCheckboxCheckedState = function () {
        return ItemTable.CHECKBOX_CHECKED_STATE_CHECKED;
    };

    $(buttonSelectAllSelector).on('click', tableHandler.deSelectAll);
}

/**
 * @returns {boolean}
 */
function onRemove() {
    tableHandler.removeSelectedItem($(this).data('id'));

    return false;
}

module.exports = {
    initialize: initialize,
};
