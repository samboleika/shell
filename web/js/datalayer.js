/*
This file should be detached from CQ source and injected via DTM.
Doing so will make the metrics implementation independent from the Rio codebase.
*/

window.shell = window.shell || {};


(function(shell, $) {
    "use strict";

    var o = {};

    o.NULL = 'null';

    o.encodeArray = function (array, originalSeparator, newSeparator) {
        var separator = '/',
            outputSeparator = '|';
        if (originalSeparator !== undefined) {
            separator = originalSeparator;
        }
        if (newSeparator !== undefined) {
            outputSeparator = newSeparator;
        }
        return array.split(separator).join(outputSeparator);
    };

    o.getHierarchy = function(metaData) {
        var hierarchy = o.NULL;
        if (metaData.metaHome !== undefined) {
            hierarchy = metaData.metaPagePath.split(o.getHierarchyRoot(metaData) + '/')[1];
            hierarchy = o.encodeArray(hierarchy);
        }
        return hierarchy;
    };

    o.getHierarchyRoot = function (metaData) {
        var hierarchy = metaData.metaHome.split('/');
        return hierarchy.join('/');
    };

    o.getPageName = function (metaData) {
        var pageName;
        if (metaData.metaHome !== undefined) {
            var pagePath = metaData.metaPagePath.split(metaData.metaHome + '/').pop();
            pageName = o.encodeArray(pagePath);
        } else {
            pageName = metaData.metaPageTitle;
        }

        return pageName;
    };

    o.createData = function(data) {
        window.digitalData = {
            'version': '1.0.1',
            'page': {
                'pageInfo': {
                    'pageName': o.getPageName(data),
                    'vanityUrl': '',
                },
                'category': {
                    'pageType': data.metaTemplate,
                    'tags': ''
                },
                'attributes': {
                    'domain' : location.origin,
                    'country': data.metaCountry,
                    'site_language': data.metaLanguage,
                    'pageHierarchy' : o.getHierarchy(data)
                }
            },
            'event': []
        };
    };

    o.markLinks = function(data) {
        var TRACKING_ATTRIBUTE = 'data-tracking',
            TRACKING_EXTERNAL = 'external',
            TRACKING_SHARE = 'share',
            TRACKING_DOWNLOAD = 'download',
            TRACKING_REGISTER = 'register',
            $differentDomainLinks = $('a[href^=http]')
                .filter("a:not([href^='" + location.origin + "'])"); // Get all links on a different domain

        if ($differentDomainLinks.length > 0) {
            // External links
            $differentDomainLinks
                .addClass('tracking-progression');

            // All different domain links are marked as external
            $differentDomainLinks
                .attr(TRACKING_ATTRIBUTE, TRACKING_EXTERNAL); 
        }

        // Download links
        $('a[download],a.metrics_download').attr(TRACKING_ATTRIBUTE, TRACKING_DOWNLOAD);

        // Social links.
        $('.items-soc')
            .addClass('tracking-progression')
            .attr(TRACKING_ATTRIBUTE, TRACKING_SHARE);

        // login register link
        $('.login').attr(TRACKING_ATTRIBUTE, TRACKING_REGISTER);
        
    };

    o.setCategories = function (metaData) {
        if (metaData.metaHome !== undefined) {
            var pagePath = metaData.metaPagePath.split(metaData.metaHome + '/').pop(),
                pathLevels = pagePath.split('/');

            var primaryCategory = pathLevels.shift();
            if (primaryCategory) {
                digitalData.page.category.primaryCategory = primaryCategory;

                for(var categoryIndex = 0; categoryIndex < pathLevels.length; categoryIndex++) {
                    digitalData.page.category['subCategory' + (categoryIndex + 1)] = pathLevels[categoryIndex];
                }
            }
        }
    };

    o.init = function() {
        var metaData = $('meta[name=datalayer]').data();

        o.createData(metaData);
        o.setCategories(metaData);
        o.markLinks(metaData);

        console.log('Initialized data layer.');
    };

    o.init();

})(window.shell, jQuery);
