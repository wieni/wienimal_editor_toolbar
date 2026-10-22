/**
 * @file
 * Take the query string into account when highlighting toolbar links.
 *
 * Core's toolbar.menu.js looks for the links to the current page (walking up
 * the path until it finds some) and marks all of them, whatever their query
 * string: the horizontal toolbar adds is-active (Drupal 10.2+), the vertical
 * one adds menu-item--active and expands their parents. The content overview
 * links of this module all point to /admin/content with another ?type=, so on
 * any content overview every one of them was highlighted, and so was "Add
 * content", which links to /admin/content as well.
 *
 * This runs right after core, in both orientations, and decides again for the
 * links core matched, i.e. the links with that same system path. Other
 * highlighted links (Gin marks the active trail server-side) are left alone.
 * - A link whose data-drupal-link-query has a parameter that the current URL
 *   lacks, or has with another value, is not active. Extra parameters in the
 *   URL (filters, pager, destination) don't matter.
 * - Of the remaining links, the ones with the most matching parameters are
 *   active, together with their parents in that group.
 * - When several links tie, a parent whose children all point elsewhere (a
 *   grouping like "Add content") gives way to one that is the current page
 *   itself ("Content overview", whose children are filtered versions of it).
 *
 * Core's active-link.js marks links as well, on its own terms. The library
 * depends on it so that its behavior has run by the time the toolbar's does.
 */
(function ($, Drupal, drupalSettings) {
    const markHorizontal = $.fn.drupalToolbarMenuHorizontal;
    const markVertical = $.fn.drupalToolbarMenu;

    function systemPath(link) {
        return link.getAttribute('data-drupal-link-system-path');
    }

    // The links core matched: the deepest path, from the current one up, that
    // has links in this menu.
    function matchedLinks(menu) {
        const links = Array.from(menu.querySelectorAll('a[data-drupal-link-system-path]'));
        const paths = new Set(links.map(systemPath));
        let path = (drupalSettings.path && drupalSettings.path.currentPath) || '';

        while (path && !paths.has(path)) {
            const slash = path.lastIndexOf('/');
            path = slash === -1 ? '' : path.slice(0, slash);
        }

        return path ? links.filter((link) => systemPath(link) === path) : [];
    }

    // Like core's active-link.js: no attribute means no query string.
    function linkQuery(link) {
        try {
            return JSON.parse(link.getAttribute('data-drupal-link-query') || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    function sameValue(a, b) {
        if (typeof a === 'object' || typeof b === 'object') {
            return JSON.stringify(a) === JSON.stringify(b);
        }

        return String(a) === String(b);
    }

    // -1 when the link asks for something the current URL doesn't have,
    // otherwise the number of query parameters it matches.
    function score(link, currentQuery) {
        const query = linkQuery(link);
        const keys = Object.keys(query);

        if (keys.some((key) => !(key in currentQuery) || !sameValue(query[key], currentQuery[key]))) {
            return -1;
        }

        return keys.length;
    }

    // A leaf, or a parent with a link to the same page among its children.
    function isPage(link) {
        const item = link.closest('li');
        const submenu = item && item.querySelector(':scope > ul');

        if (!submenu) {
            return true;
        }

        return Array.from(submenu.querySelectorAll('a[data-drupal-link-system-path]'))
            .some((child) => systemPath(child) === systemPath(link));
    }

    function contains(link, other) {
        const item = link.closest('li');

        return link !== other && !!item && item.contains(other);
    }

    // Which of the given links (all to the same path) are the current page,
    // plus their parents among them.
    function activeAmong(links) {
        const currentQuery = (drupalSettings.path && drupalSettings.path.currentQuery) || {};
        const scores = new Map(links.map((link) => [link, score(link, currentQuery)]));
        const best = Math.max(...scores.values());
        let winners = links.filter((link) => best >= 0 && scores.get(link) === best);

        if (winners.length > 1) {
            const pages = winners.filter(isPage);

            if (pages.length) {
                winners = pages;
            }
        }

        return links.filter((link) => winners.includes(link) || winners.some((winner) => contains(link, winner)));
    }

    function refineHorizontal(menu) {
        const links = matchedLinks(menu);

        if (links.length < 2 && !links.some((link) => link.hasAttribute('data-drupal-link-query'))) {
            return;
        }

        const active = activeAmong(links);
        links.forEach((link) => link.classList.toggle('is-active', active.includes(link)));
    }

    // Undo what core's openActiveItem() did for a link that isn't active after all.
    function refineVertical(menu) {
        const matched = new Set(matchedLinks(menu));
        const links = Array.from(menu.querySelectorAll('a.menu-item--active'))
            .filter((link) => matched.has(link));

        if (links.length < 2) {
            return;
        }

        const active = activeAmong(links);
        links
            .filter((link) => !active.includes(link))
            .forEach((link) => {
                const item = link.closest('li');
                link.classList.remove('menu-item--active');

                if (!item || active.some((other) => item.contains(other))) {
                    return;
                }

                // Same as core's toggleList($item, false).
                const handle = item.querySelector(':scope > .toolbar-box > .toolbar-handle');
                item.classList.remove('menu-item--active-trail', 'open');

                if (handle) {
                    handle.classList.remove('open');
                    handle.querySelectorAll('.action').forEach((action) => {
                        action.textContent = Drupal.t('Extend');
                    });
                }
            });
    }

    if (markHorizontal) {
        $.fn.drupalToolbarMenuHorizontal = function (...args) {
            const result = markHorizontal.apply(this, args);
            this.each((index, menu) => refineHorizontal(menu));

            return result;
        };
    }

    if (markVertical) {
        $.fn.drupalToolbarMenu = function (...args) {
            const result = markVertical.apply(this, args);
            this.each((index, menu) => refineVertical(menu));

            return result;
        };
    }
})(jQuery, Drupal, drupalSettings);
