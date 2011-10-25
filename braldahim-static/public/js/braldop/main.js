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


function initBraldopCallback(map) {
    map.setCallback("Marcher", function(a) {
        console.log('Action Marcher:');
        console.log(a);
        actionMarcher(a);
    });
}

function actionMarcher(action) {
    alert('Developpement en cours. Action Marcher PA:' + action.PA);
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

    $('#menu_actions').delegate('img.étoile', 'click',
        function() {
            var id = parseInt($(this).attr('id_action'));
            actions[id].Favorite = !actions[id].Favorite;
        }).delegate('.titre_liste', 'click', function() {
            var tag = $(this).text();
            var isVisible = $('.liste[tag="' + tag + '"]').is(':visible');
            $('.liste').hide('fast');
            if (!isVisible) $('.liste[tag="' + tag + '"]').show('fast');
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
}


function construitMenuActions() {

    $.getJSON('/interface/competencesjson?time=' + (new Date().getTime()) + "&dateAuth=" + $('#dateAuth').val(), function(data) {

        var listesActions = {};
        var tags = [];
        listesActions["Favorites"] = [];
        tags.push("Favorites");

        $.each(data, function(key, val) {
            var action = val;

            action.id = key;
            if (action.favorite) listesActions["Favorites"].push(action);
            if (!listesActions[action.type]) {
                listesActions[action.type] = [];
                tags.push(action.type);
            }
            listesActions[action.type].push(action);
        });

        var html = '<br /><center><b>Mes Actions</b></center>';
        for (var it in tags) {
            var tag = tags[it];
            var liste = listesActions[tag];
            html += '<div class=titre_liste>' + tag + '</div>';
            html += '<div class=liste tag="' + tag + '">';
            for (var ia = 0; ia < liste.length; ia++) {
                var action = liste[ia];
                html += '<a><img class="étoile" id_action=' + action.id + ' src="http://static.braldahim.com/images/layout/etoile_' + (action.favorite ? 'pleine' : 'vide') + '.png" height=14/> ' + action.pa_texte + ' PA - ' + action.nom + '</a>';
            }
            html += '</div>';
        }
        console.log(html);
        $('#menu_actions').html(html);
        $('.liste').hide();
        $('.liste[tag="Favorites"]').show();
    });


}