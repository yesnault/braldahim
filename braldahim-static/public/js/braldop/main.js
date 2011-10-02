/**
 * Contenu de map.html.
 */
var map = null

function fetchMap(callback) {
    var httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = function() {
        if (httpRequest.readyState === 4) {
            if (httpRequest.status === 200) {
                var msg = eval('(' + httpRequest.responseText + ')');
                console.log("reçu : ");
                console.log(msg);
                if (callback) callback(msg);
            }
        }
    };
    httpRequest.open('GET', '/interface/cartejson?time=' + (new Date().getTime()) + "&dateAuth=" + $('#dateAuth').val());
    httpRequest.send();
}

function initBraldop() {
    map = new Map("map_canvas", "posmark", "map_dialog");
    fetchMap(function(msg) {
        map.setData(msg);
        //> on batit le menu de choix de la profondeur
        var html = ''
        if (msg.Couches) {
            html += 'Profondeur : <select id=select_profondeur>';
            for (var i = 0; i < msg.Couches.length; i++) {
                var z = msg.Couches[i].Z;
                html += '<option value=' + z + '>' + z + '</option>';
            }
            html += '</select>';
        }
        $('#choix_profondeur').html(html);
        
        map.setCallback('profondeur', function(z) {
            $('#select_profondeur').val(z);
        });

        map.redraw();
        setTimeout(function() {
            map.redraw();
        }, 1000); // contournement de bug pas compris
    });

    $('#layer_satellite').attr('checked', map.displayPhotoSatellite).change(function() {
        map.displayPhotoSatellite = this.checked;
        map.redraw();
    });
    $('#layer_régions').attr('checked', map.displayRégions).change(function() {
        map.displayRégions = this.checked;
        map.redraw();
    });
    $('#layer_fog').attr('checked', map.displayFog).change(function() {
        map.displayFog = this.checked;
        map.redraw();
    });

    $('#btnCentrer').bind('click', function() {
        map.zoom = 64;
        map.redraw();
        map.goto(parseInt($("#positionX").val()), parseInt($("#positionY").val()));
    });

    $('#select_profondeur').live('change', function() {
        map.changeProfondeur(parseInt($(this).val()));
        map.redraw();
    });

    setTimeout(function() {
        for (i in map.mapData.Vues) {
            var v = map.mapData.Vues[i];
            v.active = true; //this.checked;
            map.goto(parseInt($("#positionX").val()), parseInt($("#positionY").val()));
        }
        map.redraw();
    }, 1000);

}
