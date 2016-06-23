jQuery( function() {
    jQuery('.wppb-table .wppb-sorting').each( function() {
        $sortLink = jQuery(this).children('.sortLink');

        if( $sortLink.length > 0 ) {
            jQuery(this).append('<span class="wppb-sorting-default"></span>');

            if( $sortLink.hasClass('sort-asc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-ascending');
            }

            if( $sortLink.hasClass('sort-desc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-descending');
            }
        }
    });

    /* js handle for the select facet filter */
    jQuery(document).on('change', '.wppb-facet-select', function() {
        if( jQuery(this).val() == '' ){
            return;
        }
        wppbHandleFacet(jQuery(this));
    });

    /* js handle for the checkbox facet filter */
    jQuery(document).on('click', '.wppb-facet-checkbox', function() {
        wppbHandleFacet(jQuery(this));
    });

    /* js handle for the search facet filter */
    jQuery(document).on( 'keydown', '.wppb-facet-search', function (e){
        if( jQuery(this).val() == '' ){
            return;
        }
        if(e.keyCode == 13){
            wppbHandleFacet(jQuery(this));
        }
    });

    /* js handle for the range facet filter has it's own function */


    /* remove individual facet value */
    jQuery(document).on('click', '.wppb-remove-facet', function(e) {
        e.preventDefault();

        jQuery('.wppb-userlisting-container').addClass('spinner');

        paramName = 'ul_filter_' + jQuery( this).attr('data-meta-name');
        paramValue = jQuery( this).attr('data-meta-value');

        /* remove page arg first */
        currentPage = jQuery( this).attr('data-current-page');
        if( currentPage > 1 )
            url = wppbRemovePageFromUrl( window.location.href, currentPage );
        else
            url = window.location.href;

        url = wppbRemoveURLParameter( url, paramName, paramValue );

        jQuery.get( url, function( response ) {
            found = jQuery('.wppb-userlisting-container', jQuery( response ) );
            jQuery( '.wppb-userlisting-container').html( found.html() );
            window.history.pushState( {}, '', url );
            jQuery('.wppb-userlisting-container').removeClass('spinner');
        });
    });

    /* remove all filters */
    jQuery(document).on('click', '.wppb-remove-all-facets', function(e) {
        e.preventDefault();

        jQuery('.wppb-userlisting-container').addClass('spinner');

        allParams = jQuery( this).attr('data-all-filters');

        /* remove page arg first */
        currentPage = jQuery( this).attr('data-current-page');
        if( currentPage > 1 )
            url = wppbRemovePageFromUrl( window.location.href, currentPage );
        else
            url = window.location.href;

        var allParamsParts= allParams.split(',');
        url = wppbRemoveAllURLFacets( url, allParamsParts );

        jQuery.get( url, function( response ) {
            found = jQuery('.wppb-userlisting-container', jQuery( response ) );
            jQuery( '.wppb-userlisting-container').html( found.html() );
            window.history.pushState( {}, '', url );
            jQuery('.wppb-userlisting-container').removeClass('spinner');
        });
    });


    /* show all checkboxes in facet */
    jQuery(document).on('click', '.show-all-facets', function(e) {
        e.preventDefault();
        jQuery( '.hide-this', jQuery( this).closest('li') ).show();
        jQuery( '.hide-all-facets', jQuery( this).closest('li') ).show();
        jQuery( this ).hide();
    });

    /* hide checkboxes in a facet */
    jQuery(document).on('click', '.hide-all-facets', function(e) {
        e.preventDefault();
        jQuery( '.hide-this', jQuery( this).closest('li') ).hide();
        jQuery( this ).hide();
        jQuery( '.show-all-facets', jQuery( this).closest('li') ).show();
    });




});

function wppbHandleFacet( facetObj ){
    jQuery('.wppb-userlisting-container').addClass('spinner');
    var setParam = true;

    if( facetObj.hasClass('wppb-facet-checkbox') )
        setParam= facetObj.is(':checked');


    paramName = 'ul_filter_' + facetObj.attr('data-meta-name');
    paramBehaviour = facetObj.attr('data-filter-behaviour');
    paramValue = facetObj.attr('value');
    currentPage = facetObj.attr('data-current-page');
    if( currentPage > 1 )
        url = wppbRemovePageFromUrl( window.location.href, currentPage );
    else
        url = window.location.href;

    searchForAttr = facetObj.closest('.wppb-faceted-list').attr('data-search-for');
    if( searchForAttr != '' )
        url = wppbSetUrlParam( url, 'searchFor', searchForAttr, 'narrow' );

    if( setParam )
        url = wppbSetUrlParam( url, paramName, paramValue, paramBehaviour );
    else
        url = wppbRemoveURLParameter( url, paramName, paramValue );


    jQuery.get( url, function( response ) {
        found = jQuery('.wppb-userlisting-container', jQuery( response ) );
        jQuery( '.wppb-userlisting-container').html( found.html() );
        window.history.pushState( {}, '', url);
        jQuery('.wppb-userlisting-container').removeClass('spinner');
    });
}

/**
 * Function that retrieves a GET parameter from the current url or from a given url
 * @param name the name of the parameter
 * @param link optional url
 * @returns the value of the GET parameter
 */
function wppbGetUrlParam( name, link ){
    link = (typeof link === 'undefined') ? window.location.href : link;
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
        return null;
    }
    else{
        return results[1] || 0;
    }
}


/**
 * Function that changes the value of a GET parameter
 * @param link the url in which we make the change
 * @param name the name of the parameter we want to change
 * @param value the new value
 * @param behaviour facet behaviour( expand, narrow ) for narrow we change it for expand we add it to the existing one
 * @returns {*}
 */
function wppbSetUrlParam( link, name, value, behaviour ){
    /* check if wh have any get parameters */
    if( link.search( "\\?" ) === -1 ){
        link = link + '?'+ name + '=' + value;
    }
    else{
        /* check to see if the parameter is already there */
        thisParamValue = wppbGetUrlParam( name, link );
        if( thisParamValue !== null ){
            if( behaviour == 'narrow' )
                link = link.replace( name+'='+thisParamValue, name+'='+value );
            else if( behaviour == 'expand' )
                link = link.replace( name+'='+thisParamValue, name+'='+thisParamValue+'||'+value );
        }
        else{
            link = link + '&'+ name + '=' + value;
        }
    }
    return link;
}

/* Function that removes the page arg from the url */
function wppbRemovePageFromUrl( link, page ){
    link = link.replace( '/'+page+'/', '/' );
    link = wppbRemoveURLParameter( link, 'page' );
    return link;
}

/**
 * Function that removes a GET parameter from the url
 * @param url the url from which we remove
 * @param parameter the name of the parameter
 * @param parameterValue the value of the parameter
 * @returns {*}
 */
function wppbRemoveURLParameter( url, parameter, parameterValue ){

    if( typeof parameterValue !== 'undefined' ){
        allParamValue = wppbGetUrlParam( parameter, url );
        if( allParamValue.search( "\\|\\|" + parameterValue ) !== -1 ) {
            changedAllParamValue = allParamValue.replace('||' + parameterValue, '');
            url = url.replace( allParamValue, changedAllParamValue );
            return url;
        }
        if( allParamValue.search( parameterValue + "\\|\\|" ) !== -1 ) {
            changedAllParamValue = allParamValue.replace( parameterValue + '||', '');
            url = url.replace( allParamValue, changedAllParamValue );
            return url;
        }
    }

    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');
    if (urlparts.length>=2) {
        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}

/**
 * Function that removes all the facet parameters
 * @param url the url from which we remove
 * @param allParams an array with all the facet parameters
 * @returns {*} the link without facet parameters
 */
function wppbRemoveAllURLFacets( url, allParams ){
    for (var index = 0; index < allParams.length; ++index ) {
        url = wppbRemoveURLParameter( url, 'ul_filter_'+ allParams[index] );
    }
    return url;
}

/**
 * Function for handling the range filter
 * @param facetMeta the mata name of the filter
 * @param firstValue first value of the interval
 * @param lastValue last value of the interval
 * @param firstCurrentValue first selected value
 * @param lastCurrentValue last selected value
 */
function wppbRangeFacet( facetMeta, firstValue, lastValue, firstCurrentValue, lastCurrentValue ){
    jQuery( ".wppb-ul-slider-range."+ facetMeta ).slider({
        range: true,
        min: firstValue,
        max: lastValue,
        values: [ firstCurrentValue, lastCurrentValue ],
        slide: function( event, ui ) {
            jQuery( ".wppb-ul-range-values."+facetMeta ).text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
            jQuery( this ).attr("value", ui.values[ 0 ] + "-" + ui.values[ 1 ]);
        },
        stop: function( event, ui ){
            wppbHandleFacet( jQuery(this) );
        }
    });
}