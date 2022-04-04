var _glf_setInterval = setInterval( function () {
        let openingData = jQuery( "#glf-opening-data" );
        if( openingData.length > 0 ){
            clearInterval( _glf_setInterval );
            window.opening_types = JSON.parse( openingData.find( ".types" ).text() );
            window.openingHours = JSON.parse( openingData.find( ".hours" ).text() );
            window.openingCountryCode = openingData.find( ".country" ).text();
            window.openingDays = JSON.parse( openingData.find( ".days" ).text() );
            openingData.remove();
            window.runOpeningHours();
        }
    },
    300 );
function runOpeningHours(){
    let opening_hours = processOpeningHours( window.openingHours );
    jQuery( '.glf-widget-opening-hours .content' ).append( opening_hours );
}

function processOpeningHours( openingHoursData ) {
    let html = '',
        h_tag = 'h2';
    if( openingHoursData && ( typeof openingHoursData === 'object' ) ) {
        jQuery.each( openingHoursData, function ( key, openingHoursArr ) {
            html += '<' + h_tag + ' class="glf-open-type ' + key + '">';
            html += ( key in opening_types ) ? opening_types[key] : key;
            html += '</' + h_tag + '>';

            if( openingHoursArr && Array.isArray( openingHoursArr ) && openingHoursArr.length ) {
                openingHoursArr.forEach( ( elem ) => {
                    html += '<div class="glf-opening-entry">' +
                        '<div class="glf-week-days">' + getDaysString( numberToDaysOfWeek( elem.day_of_week ) ) + '</div>' +
                        '<div>' + getTimeString( elem.begin_minute, openingCountryCode ) + ' - ' + getTimeString( elem.end_minute, openingCountryCode ) + '</div>' +
                        '</div>';
                } );
            }

            h_tag = 'h3';
        } );
    }

    return html;
}

function getDaysString( daysArr ) {
    let result, iStart, iEnd;

    daysArr.forEach( ( key ) => {
        if( typeof iStart === 'undefined' ) {
            iStart = key;
            iEnd = key;
        } else {
            if( key === iEnd + 1 ) {
                iEnd = key;
            } else {
                result = result ? result + ', ' : '';
                if( iStart === iEnd ) {
                    result += openingDays[iStart];
                } else {
                    result += openingDays[iStart] + ' - ' + openingDays[iEnd];
                }
                iStart = key;
                iEnd = key;
            }
        }
    } );

    result = result ? result + ', ' : '';
    if( iStart === iEnd ) {
        result += openingDays[iStart];
    } else {
        result += openingDays[iStart] + ' - ' + openingDays[iEnd];
    }

    return result;
}

function getTimeString( minutes, countryCode ) {
    let h = Math.floor( minutes / 60 ),
        m = minutes % 60,
        isPm = h >= 12 && h < 24,
        is12h = [ 'US', 'DO', 'UK', 'PH', 'CA', 'AU', 'NZ', 'IN', 'EG', 'SA', 'CO', 'PK', 'MY', 'PG', 'SG', 'ZA', 'VN', 'PR' ].includes( countryCode );

    h = is12h ? ( h % 12 === 0 ? 12 : h % 12 ) : h % 24;

    return padLeft( h ) + ':' + padLeft( m ) + ( is12h ? ( isPm ? ' PM' : ' AM' ) : '' );
}

function numberToDaysOfWeek( nr ) {
    const result = [];

    [ 0, 1, 2, 3, 4, 5, 6 ].forEach( key => {
        if( nr & Math.pow( 2, key ) ) {
            result.push( key );
        }
    } );
    return result;
}

function padLeft( time ) {
    return ( '00' ).substring( time.toString().length ) + time;
}