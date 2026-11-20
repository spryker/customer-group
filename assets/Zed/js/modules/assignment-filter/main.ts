/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

import select2combobox from 'ZedGuiModules/libs/select2-combobox';

// The select2 jQuery plugin ships no typings in this repository, so only the call used here is declared.
declare global {
    interface JQuery {
        select2(method: 'destroy'): JQuery;
    }
}

const ASSIGNMENT_TYPE_SELECTOR = '#customerGroupTableFilter_assignmentType';
const ASSIGNED_ENTITY_SELECTOR = '#customerGroupTableFilter_idAssignedEntity';
const AUTOCOMPLETE_URL_DATA_KEY = 'autocomplete-url';
const AUTOCOMPLETE_URL_ATTRIBUTE = `data-${AUTOCOMPLETE_URL_DATA_KEY}`;
// select2combobox binds these on every init and select2('destroy') does not remove them.
const SELECT2_COMBOBOX_EVENTS = 'select2:unselecting select2:open';

function resetAssignedEntity(assignedEntity: HTMLSelectElement, suggestUrl: string): void {
    const $assignedEntity = $(assignedEntity);

    if ($assignedEntity.hasClass('select2-hidden-accessible')) {
        $assignedEntity.select2('destroy');
    }

    $assignedEntity.off(SELECT2_COMBOBOX_EVENTS);

    assignedEntity.querySelectorAll('option:not([value=""])').forEach((option) => option.remove());
    assignedEntity.value = '';

    if (suggestUrl) {
        assignedEntity.setAttribute(AUTOCOMPLETE_URL_ATTRIBUTE, suggestUrl);
    } else {
        assignedEntity.removeAttribute(AUTOCOMPLETE_URL_ATTRIBUTE);
    }

    // jQuery caches .data() reads; drop the cache so select2combobox picks up the new attribute.
    $assignedEntity.removeData(AUTOCOMPLETE_URL_DATA_KEY);

    select2combobox(ASSIGNED_ENTITY_SELECTOR);
}

document.addEventListener('DOMContentLoaded', () => {
    const assignmentType = document.querySelector<HTMLSelectElement>(ASSIGNMENT_TYPE_SELECTOR);
    const assignedEntity = document.querySelector<HTMLSelectElement>(ASSIGNED_ENTITY_SELECTOR);

    if (!assignmentType || !assignedEntity) {
        return;
    }

    const syncAssignedEntityDisabledState = (): void => {
        assignedEntity.disabled = !assignmentType.value;
    };

    syncAssignedEntityDisabledState();

    // select2 dispatches change through jQuery.trigger(), which native listeners do not receive.
    $(assignmentType).on('change', () => {
        const selectedOption = assignmentType.options[assignmentType.selectedIndex];

        syncAssignedEntityDisabledState();
        resetAssignedEntity(assignedEntity, selectedOption?.dataset.suggestUrl || '');
    });
});
