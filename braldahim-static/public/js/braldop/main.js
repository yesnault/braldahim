/**
 * Contenu de map.html.
 */
var map = null;


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

function initBraldopCallback(map) {
    map.setCallback("Marcher", function(a) {
        actionMarcher(a);
    });
    map.setCallback("Lieu", function(a) {
        actionLieu(a);
    });
    map.setCallback("Transbahuter", function(a) {
        actionTransbahuter(a);
    });
}

function actionMarcher(action) {
    _get_('/competences/doaction?caction=do_competence_marcher&valeur_1=' + action.Offset);
}

function actionLieu(action) {
    _get_('/interface/load/?box=box_lieu');
}

function actionTransbahuter(action) {
    _get_('/competences/doaction?caction=ask_competence_transbahuter&valeur_1=1');
}

function initBraldopFecth() {
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

        html = "";
        if (msg.Vues) {
            for (i in msg.Vues) {
                var v = msg.Vues[i];
                v.active = true; // on active par défaut les vues
            }
        }
        $('#view_table tbody').html(html);
        map.setCallback('profondeur', function(z) {
            $('#select_profondeur').val(z);
        });

        map.compileLesVues(); // en raison de leur activation
        map.redraw();
        setTimeout(function() {
            map.redraw();
        }, 1000); // contournement de bug pas compris
    });
}

function initBraldop() {
    map = new Map("map_canvas", "posmark");
    map.displayGrid = true;
    initBraldopFecth();

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
    $('#layer_grid').attr('checked', map.displayGrid).change(function() {
        map.displayGrid = this.checked;
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

    $('#goto').click(function() {
        if (map.zoom < 32) map.zoom = 32;
        //map.goto(parseInt($(this).attr('x')), parseInt($(this).attr('y')), parseInt($(this).attr('z')));
        map.goto(parseInt($("#positionX").val()), parseInt($("#positionY").val()));
    });
    $('#icon_grid').click(function() {
        map.displayGrid = !map.displayGrid;
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

    initBraldopCallback(map);
    initBlabla();
}
