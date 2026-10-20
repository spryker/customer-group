/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

const FORM_SELECTOR = 'form[data-keeps-active-tab="true"]';
const TABS_CONTAINER_SELECTOR = '.tabs-container';
const ACTIVE_TAB_SELECTOR = ':scope > .nav-tabs > [data-bs-toggle="tab"].active';

const keepActiveTabOnSubmit = (form) => {
    form.addEventListener('submit', () => {
        const activeTab = form.querySelector(TABS_CONTAINER_SELECTOR)?.querySelector(ACTIVE_TAB_SELECTOR);

        if (!activeTab) {
            return;
        }

        const actionUrl = new URL(form.action, window.location.href);
        actionUrl.hash = activeTab.dataset.bsTarget;
        form.action = actionUrl.href;
    });
};

const initialize = () => {
    document.querySelectorAll(FORM_SELECTOR).forEach(keepActiveTabOnSubmit);
};

initialize();
