<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
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
    display_clauses();
});

function display_clauses() {
    $("#div1").empty();
    $("#accordion").empty();
    $("#accordion").append("<div type='text' id='box'>");
    $.ajax({
        type:'GET',
        url: "getData.php",
        data: {table: "clauses", field: "*", prefix: "", join: "", on: "", order_by: "id"},
        success: function( data ) {
            jQuery.each(JSON.parse(data), function(i, val) {
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
                            var closeBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id'] + "' onclick='deleteClause(event)'>x</button>";
                            var addBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id'] + "' onclick='addClause(event)'>+</button>";
                            content = "<div class='row' id=clause" + val['id'] + "><div class='col-md-10 clauses '  id='" + val['id'] + "'><h3>Clause " + val['id'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + addBtn + "</div></div>";
                            $("#div1").append(content);
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

function deleteClause(ev) {
    ev.preventDefault();    
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);

    $("#div1").append(ev.path[2]);
    $("#close" + id).attr("onclick","addClause(event)");
    $("#close" + id).html('+');
}

function addClause(ev) {
    ev.preventDefault();
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);
    
    $("#accordion").append(ev.path[2]);
    $("#close" + id).attr("onclick","deleteClause(event)");
    $("#close" + id).html('x');
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
        clauses_add = $(clauses_add, function(value) {
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
<h1>Créer un type de convention</h1>
<form action="" id="type-name">
    Nom du type de votre convention: </br></br><input id="convention-type" type="text" name="name"></br></br>
</form>
<div id="clause_container">
    <div id="div1""> 
        <h1>Clauses</h1>
    </div>
    <div type="text" id="accordion" name="clause"></div>
</div>
<div id="editor">
    <button id="cmd">Ajouter</button>
</div>

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
    var req = ($("#form-control1").val() == "Obligatoire") ? 1 : 0;

    getValues();
    $.ajax({
        type:'GET',
        url: "addType.php",
        data: {table: 'convention__type_convention ', values: "('" + $('#convention-type').val().toString() + "')"},
        success: function( data ) {
            jQuery.each(values, function(i, val) {
                $.ajax({
                    type:'GET',
                    url: "addData.php",
                    data: {table: "type_convention_clause", where: "id_type_convention=" + parseInt(data), values: "(" + val + ", " + parseInt(data) + ", " + req + ", " + (parseInt(i) + parseInt(1)) + ")"},
                    success: function( data ) {
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=True">');
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=False">');
                    }
                });
            });
        }
    });
});

</script>
</body>
</html>