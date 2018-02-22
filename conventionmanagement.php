<?php
    
    if ($_GET['addconvok'] == "True") {
        echo "<div class='alert alert-success'>
  <strong>Succès!</strong> La convention a bien été enregistrée.
</div>";       
    } else if ($_GET['addconvok'] == "Nothing") {
echo "<div class='alert alert-info'>
  <strong>Info!</strong> Aucune modification.
</div>";
    }
    else if ($_GET['addconvok'] == "TypeModified") {
echo "<div class='alert alert-success'>
  <strong>Succès!</strong> Le type de convention a bien été enregistrée.
</div>"; 
    }
     else if ($_GET['addconvok'] == "TypeCreate") {
        echo "<div class='alert alert-success'><strong>Succès!</strong> Le type de convention a bien été enregistré.
</div>"; 
    } else if ($_GET['addconvok'] == "False") {
        echo "<div class='alert alert-danger'>
            <strong>Danger!</strong> Une erreur s'est produite.
        </div>";
    }
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="js/chosen.css">
    <link rel="stylesheet" href="generator.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    <script src="js/chosen.jquery.js"></script>
</head>

<script>

var values = [];
var chosen = false;
var currentIds = [];
var ids = [];
var $q = "";
var convention_type = "";
var o = new Object();
var clauses = [[]];
var droped_div = [];
var clauses_id = [];
var convention_id = 0;
var clauses_add = [];


$(document).ready(function() {
    $.ajax({
        type:'GET',
        url: "getData.php",
        data: {table: "type_convention", field: "*"},
        success: function( data ) {
            $q = "<div class='choice_box' data-placeholder='Select Your Options' id='convention_choice'><select id='choice' style='width: 200px;' class='chosen-select'><option> </option>";
            jQuery.each(JSON.parse(data), function(i, val) {
                o[val['label']] = val['id'];
                $d = val['label'];
                $q += ('<option value=' + $d + '>' + $d + '</option>');
            });
            $q += '</select></div><br/><br/>';
            $("#box_convention").append($q);
            $('.chosen-select').chosen({
                placeholder_text_single: "Sélectionnez le type de votre convention",
                no_results_text: "Oops, nothing found!"
            });
            convention_type = $('#choice_chosen').find(".chosen-single").text();
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(xhr.status+': '+thrownError);
        }
    });

    $('#box_convention').on('change', function(evt, params) {
        convention_type = params.selected;
        display_clauses();
    });

    function display_clauses() {
        $("#div1").empty();
        $("#accordion").empty();
        $("#accordion").append("<div type='text' id='box'>");
        var order =  parseInt(o[convention_type]);
        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: "convention__type_convention_clause", field: " convention__clauses.id, convention__type_convention_clause.id_clause, convention__type_convention_clause.id_type_convention, convention__type_convention_clause.clause_order, convention__type_convention_clause.id as id_type ", prefix: "convention__clauses", join: " LEFT JOIN ", on: " convention__type_convention_clause ON convention__clauses.id=convention__type_convention_clause.id_clause ", order_by: "id_type_convention='" + order + "' DESC, clause_order"},
            success: function( data ) {
                jQuery.each(JSON.parse(data), function(i, val) {
                    var id = (val['id_clause'] == 'NULL') ? val['id'] : val['id'];
                    $.ajax({
                        type:'GET',
                        url: "getData.php",
                        data: {table: "clauses", field: "*", where: " id='" + val['id'] + "';"},
                        async: false,
                        success: function( data ) {
                            jQuery.each(JSON.parse(data), function(i, clause) {
                                var content = clause['content'];
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                var status = (val['required'] == 1) ? " Obligatoire" : "Facultative";
                                var required = (val['required'] == 1) ? " Facultative" : "Obligatoire";
                                var options = "<select class='form-control' class='statut-clause' id='form-control" + val['id'] + "'><option selected='selected'>" + status + "</option><option>" + required + "</option></select>"
                                var closeBtn = "<button id='close" + val['id'] + "' onclick='deleteClause(event)'>Supprimer</button>";
                                var addBtn = "<button onclick='addClause(event)'>Ajouter</button>";

                                if (($.inArray(val['id'], clauses_id) == -1)) {
                                    if (val['id_type_convention'] == o[convention_type]) {
                                        content = "<div class='row' id=clause" + val['id'] + "><div class='col-md-10 clauses '  id='" + val['id'] + "'><h3>Clause " + val['id'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + closeBtn + "</div></div>";
                                        $("#accordion").append(content);
                                        clauses_id.push(val['id']);
                                    } else {
                                        content = "<div class='row' id=clause" + val['id'] + "><div class='col-md-10 clauses '  id='" + val['id'] + "'><h3>Clause " + val['id'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + addBtn + "</div></div>";
                                        $("#div1").append(content);
                                    }
                                }
                            });
                        },
                        error:function (xhr, ajaxOptions, thrownError){
                            alert(xhr.status+': '+thrownError);
                        }
                    }); 
                });
            },
            error:function (xhr, ajaxOptions, thrownError){
                alert(xhr.status+': '+thrownError);
            }
        }); 
    }
});

function deleteClause(ev) {
    ev.preventDefault();    
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);

    $("#div1").append(ev.path[2]);
    $("#close" + id).attr("onclick","addClause(event)");
    $("#close" + id).html('Ajouter');
}

function addClause(ev) {
    ev.preventDefault();
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);

    $("#accordion").append(ev.path[2]);
    $("#close" + id).attr("onclick","deleteClause(event)");
    $("#close" + id).html('Supprimer');
}

function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    var txt = document.getElementById(data);
    var div = document.getElementById(data).parentNode;

    if (ev.target.parentNode.parentNode.id == "accordion"  || ev.target.parentNode.id == "accordion" || ev.target.id == "accordion") {
        clauses_add.push(txt.id);
         $('#accordion').append(document.getElementById(data));
        $("#accordion").find('.col-md-2').css({"display": "block"});
    } else {
        $('#div1').append(document.getElementById(data));
        clauses_add = $.grep(clauses_add, function(value) {
            return value != txt.id;
        });
    }
}

function sortDiv() {
    $("#accordion .clause").sort(function (a, b) {
        return parseInt(a.id) > parseInt(b.id);
        }).each(function () {
            var elem = $(this);
            elem.detach();
            $(elem).appendTo("#accordion");
    });
    $("#div1 .clause").sort(function (a, b) {
        return parseInt(a.id) > parseInt(b.id);
        }).each(function () {
            var elem = $(this);
            elem.detach();
            $(elem).appendTo("#div1");
    });
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function update(elems) {
}

</script>

<body>

<h1>Gestion des conventions</h1>

<!-- <ul id="menu_horizontal"> -->
<div class="list-group">
  <a href="addconventiontype.php" id="type-convention" class="list-group-item">Ajouter un type de convention</a>
  <a href="conventionclauses.php" id="type-list" class="list-group-item">Liste des types de convention</a>
  <a href="creationconvention.php" id="create-convention" class="list-group-item">Créer une convention</a>
  <a href="conventionslist.php" id="convention-list" class="list-group-item">Liste des conventions</a>

</div>
<!-- 
    <li><a href="addconventiontype.php">Ajouter un type de convention</a></li>
    <li><a href="conventionclauses.php">Liste des types de convention</a></li>
    <li><a href="creationconventionnotype.php">Créer une convention</a></li>
    <li><a href="creationconvention.php">Créer une convention</a></li>
    <li><a href="conventionslist.php">Liste des conventions</a></li>
</ul> -->


<script>

$(function () {
    $("#accordion")
        .accordion({
            header: "> div > div.clauses"
        })
        .sortable({
            axis: "y",
            handle: "div.clauses",
            update: function (event, ui) {
                getValues();
            }
        });
});
    
function getValues() {
    values = [];
    $('#accordion > .row').each(function (index) {
        values.push($(this).attr("id").replace("clause", ""));
    });
}

function removeElementsByClass(className){
    var elements = document.getElementsByClassName(className);

    while(elements.length > 0){
        elements[0].parentNode.removeChild(elements[0]);
    }
}

$('.checkAll').click(function(e) {
});

$('#cmd').click(function () {
    getValues();
    $.ajax({
        type:'GET',
        url: "deleteData.php",
        data: {where: "id_type_convention=" + parseInt(o[convention_type])},
        success: function( data ) {
            jQuery.each(values, function(i, val) {
                var req = ($("#form-control1").val() == "Obligatoire") ? 1 : 0;
                $.ajax({
                    type:'GET',
                    url: "addData.php",
                    data: {table: "type_convention_clause", where: "id_type_convention=" + parseInt(o[convention_type]), values: "(" + val + ", " + parseInt(o[convention_type]) + ", " + req + ", " + (parseInt(i) + parseInt(1)) + ")"},
                    success: function( data ) {
                        
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        alert(xhr.status+': '+thrownError);
                    }
                });
            });
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(xhr.status+': '+thrownError);
        }
    });
});

</script>
</body>
</html>