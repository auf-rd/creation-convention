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
                console.log(val);
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
            console.log(o);
            convention_type = $('#choice_chosen').find(".chosen-single").text();
            console.log(convention_type);
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
            data: {table: "type_convention_clause", field: "*", where: 'id_type_convention=' + o[convention_type], order_by: "clause_order"},
            success: function( data ) {
                console.log(data)
                jQuery.each(JSON.parse(data), function(i, val) {
                    $.ajax({
                        type:'GET',
                        url: "getData.php",
                        data: {table: "clauses", field: "*", where: " id='" + val['id_clause'] + "';"},
                        async: false,
                        success: function( data ) {
                            jQuery.each(JSON.parse(data), function(i, clause) {
                                console.log(clause)
                                var span = clause['content'].match(/#[^#]*#/g);
                                var content = clause['content'];
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                var status = (val['required'] == 1) ? " Obligatoire" : "Facultative";
                                var required = (val['required'] == 1) ? " Facultative" : "Obligatoire";
                                var options = "<select class='form-control' class='statut-clause' id='form-control" + val['id_clause'] + "'><option selected='selected'>" + status + "</option><option>" + required + "</option></select>"
                                var closeBtn = "<button id='close" + val['id_clause'] + "' onclick='deleteClause(event)'>Supprimer</button>";
                                var addBtn = "<button onclick='addClause(event)'>Ajouter</button>";
                                console.log('content   ' + content)
                                console.log('span   ' + span)
                                if (span) {
                                jQuery.each(span, function(index, value) {
                                    console.log('content = ' + content)
                                    content = content.replace(value, "<span id='" + val['id_clause'] + "' class='" + value.replace(/#/g, "") + val['id_clause'] + " prout'>" + value + "</span>");
                                    console.log("value = " + value)
                                    console.log(content)
                                });
                            }
                                
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                content = "<div class='clause " + editable + "'onclick=textedit()  draggable='true' ondragstart='drag(event)' id='clause" + val['id_clause'] + "'>" + content + "</div>";
                                    content = "<div class='row' id=" + val['id_clause'] + "><div class='col-md-10 clauses '  id='" + val['id_clause'] + "'><h3>Clause " + val['id_clause'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + closeBtn + "</div></div>";
                                    $("#accordion").append(content);

                                    var txt = document.getElementById("clause" + val['id_clause']);
                                    jQuery.each($(txt).children(), function(i, val) {
                                        console.log(val)
                                        if (val.className) {
                                            currentId = val.className;
                                            if ($.inArray(currentId, ids) == -1) {
                                                currentIds.push(currentId.replace(/\d+/g, ''));
                                                ids.push(val.className.replace(/#/g, ""));
                                            }
                                        }
                                    });
                                    console.log("currentIds" + currentIds)
                                        update(currentIds);
                                        clauses_id.push(val['id']);
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
    ids = $.grep(ids, function(value) {
        return value.indexOf(id) <= 0;
    });
}

function addClause(ev) {
    ev.preventDefault();
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);
    $("#accordion").append(ev.path[2]);
    $("#close" + id).attr("onclick","deleteClause(event)");
    $("#close" + id).html('Supprimer');
    if ((a = $('#' + id).find('span').text().replace(/#/g, ""))) {
            ids.push("" + a + id + " prout");
    }
}

function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function textedit() {
    var edit = document.getElementsByClassName('editable');

    jQuery.each(edit, function(i, val) {
        val.ondblclick = function(e) {
            this.contentEditable = true;
            this.focus();
            this.style.backgroundColor = '#E0E0E0';
            this.style.border = '1px dotted black';
        }
        val.onmouseout = function() {
            this.style.backgroundColor = '#ffffff';
            this.style.border = '';
            this.contentEditable = false;
        }
    });
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

</script>

<body>

    <h1>Créer une convention</h1>
    <form id="convention-name" action="">
        Nom de votre convention: </br></br><input id="convention-type" type="text" name="name"></br></br>
    </form>
    <div type="text" id="box_convention"></div>
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

function update(elems) {
    jQuery.each(elems, function(i, val) {
        if ($.inArray(val, droped_div) != -1) {
            console.log('return');
            return ;
        }
        droped_div.push(val);
        console.log("prout!!!!" + currentIds)
        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: currentIds[i].replace(/#/g, ""), field: "nom", prefix: "dbauf__ref_"},
            success: function( data ) {
                console.log('box' + elems[i]);
                var id = elems[i].split(' ')[0] + "-box";
                $q = "<div class='choice_box' id=" + id + "><select id='choice' style='width: 200px;'' class='chosen-select'>";
                jQuery.each(JSON.parse(data), function(i, val) {
                    $d = val['nom'];
                    $q += ('<option value=' + $d + '>' + $d + '</option>');
                });
                $q += '</select></div><br/><br/>';
                $("#box").append('<span id=' + capitalizeFirstLetter(elems[i].split(' ')[0])  + '>' + capitalizeFirstLetter(elems[i].split(' ')[0]) + '    : </span>');
                $("#box").append($q);
                jQuery('.chosen-select').chosen();
                },
                error:function (xhr, ajaxOptions, thrownError){
                    alert(xhr.status+': '+thrownError);
                }
        });
    });
}

$('.checkAll').click(function(e) {
    console.log(e);
});

$('#cmd').click(function () {
    getValues();
    console.log(values);
    console.log(ids);
    console.log(clauses_id);
    console.log(currentIds);
    $.ajax({
        type:'GET',
        url: "addConvention.php",
        data: {table: "convention", field: " (code_auf, id_type_document) ", values: "('" + $('#convention-type').val() + "', " + parseInt(o[convention_type]) + ")"},
        async: false,
        success: function( data ) {
            jQuery.each(ids, function(i, val) {
                var v = "'" + $('#' + val.replace(/\d+/g, '').split(' ')[0] + '-box').find(".chosen-single").text() + "'";
                values = "('" + data + "', '" + val.match(/\d+/) + "', '" + val.replace(" prout", "").replace(/\d+/g, '') + "', " + v + ")";
                $.ajax({
                    type:'GET',
                    url: "addConvention.php",
                    data: {table: "convention__clause_param", field: " (id_convention, id_clause, parameter, value) ", values: values},
                    async: false,
                    success: function( data ) {
                        console.log(data)
                        
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        alert(xhr.status+': '+thrownError);
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=False">');
                    }
                });
            });
            jQuery.each($('#accordion').find('.editable'), function(i, val) {
                console.log("editable : " + val.id.replace("clause", ""));
                console.log(val.innerHTML);
                $.ajax({
                    type:'GET',
                    url: "addConvention.php",
                    data: {table: "convention__clause_mod", field: " (id_convention, id_old_clause, content) ", values: "('" + data + "', " + val.id.replace("clause", "") + ", '" + val.innerHTML + "')"},
                    async: false,
                    success: function( data ) {                        
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=False">');
                        alert(xhr.status+': '+thrownError);
                    }
                });
            })

        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(xhr.status+': '+thrownError);
        }
    });
    $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=True">');
});

</script>
</body>
</html>