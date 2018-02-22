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
var param = new Object();
var id_convention = 0;
var type = 0;
var ids_in = [];
var to_remove = [];

$(document).ready(function() {
    $.ajax({
        type:'GET',
        url: "getData.php",
        data: {prefix: "convention__conventions", field: "*"},
        async: false,
        success: function( data ) {
            $q = "<div class='choice_box' data-placeholder='Select Your Options' id='convention_choice'><select id='choice' style='width: 200px;' class='chosen-select'><option> </option>";
            jQuery.each(JSON.parse(data), function(i, val) {
                o[val['nom']] = val['id'];
                $t = val['id_type_document'];
                $d = val['nom'];
                $q += ('<option class=' + $t + ' value=' + $d + '>' + $d + '</option>');
            });
            $q += '</select></div><br/><br/>';
            $("#box_convention").append($q);
            $('.chosen-select').chosen({
                placeholder_text_single: "Sélectionnez votre convention",
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
        document.getElementById('convention-id').value = o[convention_type];
        display_clauses();
    });

    function display_clauses() {
        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {prefix: "convention__covnentions", field: "id_type_document", where: "id=" + parseInt(o[convention_type])},
            async: false,
            success: function( data ) {
                jQuery.each(JSON.parse(data), function(i, val) {
                    type = val['id_type_document'];         
                })
            },
            error:function (xhr, ajaxOptions, thrownError){
                alert(xhr.status+': '+thrownError);
            }
        });

        $("#div1").empty();
        $("#accordion").empty();
        $("#accordion").append("<div type='text' id='box'>");

        var order =  parseInt(o[convention_type]);

        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: "clause_param", field: "*", where: 'id_convention=' + o[convention_type]},
            async: false,
            success: function( data ) {
                jQuery.each(JSON.parse(data), function(i, val) {
                    param[val['parameter']] = val['value'];
                    $.ajax({
                        type:'GET',
                        url: "getData.php",
                        data: {table: "clauses", field: "*", where: " id='" + val['id_clause'] + "';"},
                        async: false,
                        success: function( data ) {
                            jQuery.each(JSON.parse(data), function(i, clause) {
                                var span = clause['content'].match(/#[^#]*#/g);
                                var content = clause['content'];
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                var status = (val['required'] == 1) ? " Obligatoire" : "Facultative";
                                var required = (val['required'] == 1) ? " Facultative" : "Obligatoire";
                                var options = "<select class='form-control' class='statut-clause' id='form-control" + val['id'] + "'><option selected='selected'>" + status + "</option><option>" + required + "</option></select>"
                                var closeBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id_clause'] + "' onclick='deleteClause(event)'>x</button>";
                                var addBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id_clause'] + "' onclick='addClause(event)'>+</button>";
                                
                                if (span) {
                                    jQuery.each(span, function(index, value) {
                                        content = content.replace(value, "<span id='" + val['id_clause'] + "' class='" + value.replace(/#/g, "") + val['id_clause'] + " prout'>" + value + "</span>");
                                    });
                                }
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                content = "<div class='clause " + editable + "'onclick=textedit()  draggable='true' ondragstart='drag(event)' id='clause" + val['id_clause'] + "'>" + content + "</div>";  
                                content = "<div class='row' id=" + val['id_clause'] + "><div class='col-md-10 clauses '  id='" + val['id_clause'] + "'><h3>Clause " + val['id_clause'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + closeBtn + "</div></div>";
                                $("#accordion").append(content);
                                ids_in.push(clause['id']);
                                var txt = document.getElementById("clause" + val['id_clause']);
                                jQuery.each($(txt).children(), function(i, val) {
                                    if (val.className) {
                                        currentId = val.className;
                                        if ($.inArray(currentId, ids) == -1) {
                                            currentIds.push(currentId.replace(/\d+/g, ''));
                                            ids.push(val.className.replace(/#/g, ""));
                                        }

                                    }
                                });
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

        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: "clause_mod", field: "*", where: 'id_convention=' + o[convention_type]},
            async: false,
            success: function( data ) {
                jQuery.each(JSON.parse(data), function(i, val) {
                    $.ajax({
                        type:'GET',
                        url: "getData.php",
                        data: {table: "clauses", field: "*", where: " id='" + val['id_old_clause'] + "';"},
                        async: false,
                        success: function( data ) {
                            jQuery.each(JSON.parse(data), function(i, clause) {
                                var content = val['content'];
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                var status = (val['required'] == 1) ? " Obligatoire" : "Facultative";
                                var required = (val['required'] == 1) ? " Facultative" : "Obligatoire";
                                var options = "<select class='form-control' class='statut-clause' id='form-control" + val['id'] + "'><option selected='selected'>" + status + "</option><option>" + required + "</option></select>"
                                var closeBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id_old_clause'] + "' onclick='deleteClause(event)'>x</button>";
                                var addBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id'] + "' onclick='addClause(event)'>+</button>";

                                var editable = (clause['editable'] == 1) ? 'editable' : '';

                                content = "<div class='clause " + editable + "'onclick=textedit()  draggable='true' ondragstart='drag(event)' id='clause" + val['id_old_clause'] + "'>" + content + "</div>";
                                    content = "<div class='row' id=" + val['id_old_clause'] + "><div class='col-md-10 clauses '  id='" + val['id_old_clause'] + "'><h3>Clause " + val['id_old_clause'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + closeBtn + "</div></div>";
                                    $("#accordion").append(content);
                                    ids_in.push(clause['id']);
                                    var txt = document.getElementById("clause" + val['id_clause']);
                                    jQuery.each($(txt).children(), function(i, val) {
                                        if (val.className) {
                                            currentId = val.className;
                                            if ($.inArray(currentId, ids) == -1) {
                                                // currentIds.push(currentId.replace(/\d+/g, ''));
                                                ids.push(val.className.replace(/#/g, ""));
                                            }
                                        }
                                    });
                                    clauses_id.push(val['id_old_clause']);
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

        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: "type_convention_clause", field: "*", where: 'id_type_convention=' + type, order_by: "clause_order"},
            async: false,
            success: function( data ) {
                jQuery.each(JSON.parse(data), function(i, val) {
                    $.ajax({
                        type:'GET',
                        url: "getData.php",
                        data: {table: "clauses", field: "*", where: " id='" + val['id_clause'] + "';"},
                        async: false,
                        success: function( data ) {
                            jQuery.each(JSON.parse(data), function(i, clause) {
                                var span = clause['content'].match(/#[^#]*#/g);
                                var content = clause['content'];
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                var status = (val['required'] == 1) ? " Obligatoire" : "Facultative";
                                var required = (val['required'] == 1) ? " Facultative" : "Obligatoire";
                                var options = "<select class='form-control' class='statut-clause' id='form-control" + val['id_clause'] + "'><option selected='selected'>" + status + "</option><option>" + required + "</option></select>"
                                 var closeBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id_clause'] + "' onclick='deleteClause(event)'>x</button>";
                                var addBtn = "<button class='w3-button w3-circle w3-teal' id='close" + val['id_clause'] + "' onclick='addClause(event)'>+</button>";
                                if (span) {
                                    jQuery.each(span, function(index, value) {
                                        content = content.replace(value, "<span id='" + val['id_clause'] + "' class='" + value.replace(/#/g, "") + val['id_clause'] + " prout'>" + value + "</span>");
                                    });
                                }
                                var editable = (clause['editable'] == 1) ? 'editable' : '';
                                content = "<div class='clause " + editable + "'onclick=textedit()  draggable='true' ondragstart='drag(event)' id='clause" + val['id_clause'] + "'>" + content + "</div>";
                                // if (($.inArray(val['id'], clauses_id) == -1)) {
                                    // if (val['id_type_convention'] == o[convention_type]) {
                                    if ($.inArray(clause['id'], ids_in) == -1) {
                                        content = "<div class='row' id=" + val['id_clause'] + "><div class='col-md-10 clauses '  id='" + val['id_clause'] + "'><h3>Clause " + val['id_clause'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + addBtn + "</div></div>";
                                        $("#div1").append(content);

                                    } else {
                                        content = "<div class='row' id=" + val['id_clause'] + "><div class='col-md-10 clauses '  id='" + val['id_clause'] + "'><h3>Clause " + val['id_clause'] + "</h3>" + content + "</div><div class='col-md-2 options'>" + options + closeBtn + "</div></div>";
                                    }
                                    var txt = document.getElementById("clause" + val['id_clause']);
                                    jQuery.each($(txt).children(), function(i, val) {
                                        if (val.className) {
                                            currentId = val.className;
                                            if ($.inArray(currentId, ids) == -1) {
                                                // currentIds.push(currentId.replace(/\d+/g, ''));
                                                // ids.push(val.className.replace(/#/g, ""));
                                            }
                                        }
                                    });
                                        // update(currentIds);
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
    $("#close" + id).html('+');
    b = ($('#' + id).find('span').text().replace(/#/g, "") + id + " prout");
    var idx = $.inArray(b, ids);
    if (idx == -1) {
      ids.push(b);
      currentIds.push(b);
    } else {
        ids.splice(idx, 1);
        currentIds.splice(idx, 1);
        to_remove.push($('#' + id).find('span').text().replace(/#/g, ""));
        droped_div = [];
        console.log("delete = " + droped_div)
        // droped_div.push($('#' + id).find('span').text().replace(/#/g, "") + " prout");

    }
    update(currentIds);
    // }
}

function addClause(ev) {
    ev.preventDefault();
    var id = ev.path[2].id.substr(ev.path[2].id.length - 1);

    $("#accordion").append(ev.path[2]);
    $("#close" + id).attr("onclick","deleteClause(event)");
    $("#close" + id).html('x');
    if ((a = $('#' + id).find('span').text().replace(/#/g, ""))) {
        ids.push("" + a + id + " prout");
        currentIds.push("" + a + " prout");
    }
    update(currentIds);

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

function update(elems) {
    jQuery.each(to_remove, function(i, val) {
        $('#' + capitalizeFirstLetter(val)).remove();
        $('#' + val + '-box').remove();
    });

    jQuery.each(elems, function(i, val) {
        if ($.inArray(val, droped_div) != -1) {
            return ;
        }
        if($("#" + capitalizeFirstLetter(elems[i].split(' ')[0])).length != 0) {
            return;
        }
        droped_div.push(val);
        $.ajax({
            type:'GET',
            url: "getData.php",
            data: {table: currentIds[i].replace(/#/g, ""), field: "nom", prefix: "dbauf__ref_"},
            success: function( data ) {
                var id = elems[i].split(' ')[0] + "-box";
                
                $q = "<div class='choice_box' id=" + id + "><select id='choice' style='width: 200px;'' class='chosen-select'>";
                $d = param[elems[i].split(' ')[0]];
                $q += ('<option value=' + $d + '>' + $d + '</option>');
                jQuery.each(JSON.parse(data), function(i, val) {
                    $d = val['nom'];
                    $q += ('<option value=' + $d + '>' + $d + '</option>');
                });
                $q += '</select></div><br/>';
                $("#box").append('<span id=' + capitalizeFirstLetter(elems[i].split(' ')[0])  + '>' + capitalizeFirstLetter(elems[i].split(' ')[0]) + '    : </span>');
                $("#box").append($q);
                jQuery('.chosen-select').chosen();
            },
            error:function (xhr, ajaxOptions, thrownError){
                alert(xhr.status+': '+thrownError);
            }
        });
    });
    to_remove = [];
}

</script>

<body>
<h1>Modifier une convention</h1>
<div type="text" id="box_convention"></div>
<div id="clause_container">
    <div id="div1""> 
        <h1>Clauses</h1>
    </div>
    <div type="text" id="accordion" name="clause"></div>
</div>
<div id="editor">
    <button id="cmd">Enregistrer les modifications</button>
</div>
<div type="text" id="div3"></div>
<div id="editor">
    <!-- <button id="pdf">Générer le PDF</button> -->
    <form class="form-inline" method="post" action="generate_pdf.php">
    <input type="hidden" name="id" id="convention-id" value=""/>
<button type="submit" id="pdf" name="generate_pdf" class="btn btn-primary"><i class="fa fa-pdf"" aria-hidden="true"></i>
Generate PDF</button>
</form>
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
var entityMap = {
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;',
  '/': '&#x2F;',
  '`': '&#x60;',
  '=': '&#x3D;'
};

function escapeHtml (string) {
  return String(string).replace(/[&<>"'`=\/]/g, function (s) {
    return entityMap[s];
  });
}

$('#cmd').click(function () {
    getValues();
    console.log(values)
    console.log(ids)
    console.log(o[convention_type])
    $.ajax({
        type:'GET',
        url: "deleteconvention.php",
        data: {where: "id_convention=" + parseInt(o[convention_type])},
        async: false,
        success: function( data ) {
            var req = ($("#form-control1").val() == "Obligatoire") ? 1 : 0;
            jQuery.each(ids, function(i, val) {
                var v = "'" + escapeHtml($('#' + val.replace(/\d+/g, '').split(' ')[0] + '-box').find(".chosen-single").text()) + "'";
                values = "('" + o[convention_type] + "', '" + val.match(/\d+/) + "', '" + val.replace(" prout", "").replace(/\d+/g, '') + "', " + v + ")";
                $.ajax({
                    type:'GET',
                    url: "addConvention.php",
                    data: {table: "convention__clause_param", field: " (id_convention, id_clause, parameter, value) ", values: values},
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

    $.ajax({
        type:'GET',
        url: "deletemod.php",
        data: {where: "id_convention=" + parseInt(o[convention_type])},
        async: false,
        success: function( data ) {
            jQuery.each($('#accordion').find('.editable'), function(i, val) {
                $.ajax({
                    type:'GET',
                    url: "addConvention.php",
                    data: {table: "convention__clause_mod", field: " (id_convention, id_old_clause, content) ", values: "('" + o[convention_type] + "', " + escapeHtml(val.id.replace("clause", "")) + ", '" + escapeHtml(val.innerHTML) + "')"},
                    success: function( data ) {
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=TypeCreate">');
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        alert(xhr.status+': '+thrownError);
                        $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=False">');
                    }
                });
            });
        },
        error:function (xhr, ajaxOptions, thrownError){
            alert(xhr.status+': '+thrownError);
        }
    });
    // $('head').append('<meta http-equiv="refresh" content="0;URL=./conventionmanagement.php?addconvok=Nothing">');
});

</script>
</body>
</html>