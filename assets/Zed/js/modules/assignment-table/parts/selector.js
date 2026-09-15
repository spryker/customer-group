/**
 * Copyright (c) 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

function ItemSelector() {
    var itemSelector = {};
    var selectedItems = {};

    /**
     * @param {number} idItem
     * @param {Array} row - Row the item is shown with in the table of the selection.
     */
    itemSelector.addItemToSelection = function (idItem, row) {
        selectedItems[idItem] = row;
    };

    itemSelector.removeItemFromSelection = function (idItem) {
        delete selectedItems[idItem];
    };

    itemSelector.isItemSelected = function (idItem) {
        return selectedItems.hasOwnProperty(idItem);
    };

    itemSelector.clearAllSelections = function () {
        selectedItems = {};
    };

    itemSelector.getSelected = function () {
        return selectedItems;
    };

    /**
     * @return {Array} Rows of every selected item, the table of the selection is built from them.
     */
    itemSelector.getRows = function () {
        return Object.keys(selectedItems).map(function (idItem) {
            return selectedItems[idItem];
        });
    };

    return itemSelector;
}

module.exports = {
    /**
     * @return {ItemSelector}
     */
    create: function () {
        return new ItemSelector();
    },
};
