<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button id="saveData" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($message) { 
        foreach($message as $class=>$m){
            ?>
            <div class="alert alert-<?php echo $class; ?>"><i class="fa fa-exclamation-circle"></i> <?php echo $m; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php
        }
        ?>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
            <label> &nbsp; </label>
                <input type="button" class="btn btn-primary btn-block" id="addManually" value="<?php echo $text_add_manually; ?>">
            </div>
            <div class="col-md-3">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-success btn-block" id="rebuildSel" value="<?php echo $text_rebuild_selected; ?>" disabled>
            </div>
            <div class="col-md-3">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-danger btn-block" id="deleteSel" value="<?php echo $text_delete_selected; ?>" disabled>
            </div>
            <div class="col-md-3">
                <label><?php echo $text_limit; ?></label>
                <select id="limit" class="form-control">
                    <?php
                    foreach(array(50,100,150,200,300,500) as $l){
                        ?><option <?php echo $l==$limit?"selected":"";?>><?php echo $l; ?></option><?php
                    }
                    ?>
                </select><br>
            </div>
        </div>
        <style>
        .table-dark{
            background: #222;
            color: #ccc;
        }
        .table-stripped tr:nth-child(2n){
            background: #444;
            color: #ccc;
        }
        tr.newOne{
            background: #ddd !important;
            color: #333 !important;
        }
        tr.selected{
            background: green !important;
        }
        tr.collision{
            background: #900 !important;
            color: #fcc !important;
            font-weight: bold;
        }
        tr.collision.selected{
            background: #c00 !important;
        }
        </style>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-seo-url" class="form-horizontal">
        <input type="hidden" name="action" value="save">
        <table class="table table-dark table-stripped">
            <thead>
                <tr><th><input type="checkbox" id="selector2"></th><th><?php echo $text_query; ?></th><th><?php echo $text_alias; ?></th></tr>
            </thead>
            <tbody>
            <?php
            $collisions = array_flip($collisions);
            foreach($list as $i=>$row){
                $i = $row["url_alias_id"];
                $collisionClass = "";
                if(isset($collisions[$row["keyword"]])) $collisionClass = 'class="collision"';
                ?>
                <tr <?php echo $collisionClass; ?>>
                <td><input type="checkbox" name="sel[]" value="<?php echo $i;?>"></td>
                <td contenteditable="true" class="editable"><?php echo $row["query"]; ?></td>
                <input type="hidden" name="query[<?php echo $i;?>]" value="<?php echo $row["query"]; ?>">
                <td contenteditable="true" class="editable"><?php echo $row["keyword"]; ?></td>
                <input type="hidden" name="keyword[<?php echo $i;?>]" value="<?php echo $row["keyword"]; ?>">
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        </form>
        <?php
        echo "$pagination<br>$results";
        ?>
        <br><br><br><br>
        <p><?php echo $text_global; ?></p>
        <div class="row">
            <div class="col-md-4">
                <label> &nbsp; </label>
                <?php if($listmode=="fulllist"){ ?>
                    <input type="button" class="btn btn-info btn-block" id="checkCol" value="<?php echo $text_check_collisions; ?>" <?php echo count($list)==0?"disabled":"";?>>
                <?php }else{ ?>
                    <input type="button" class="btn btn-primary btn-block" id="showAll" value="<?php echo $text_show_all; ?>">
                <?php } ?>
            </div>
            <div class="col-md-4">

            </div>
            <div class="col-md-4">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-danger btn-block" id="deleteDef" value="<?php echo $text_delete_def; ?>" <?php echo count($list)==0?"disabled":"";?>>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-success btn-block" id="rebuildAll" value="<?php echo $text_rebuild_all; ?>" <?php echo count($list)==0?"disabled":"";?>>
            </div>
            <div class="col-md-4">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-success btn-block" id="autoAddNew" value="<?php echo $text_rebuild_new; ?>" >
            </div>
            <div class="col-md-4">
                <label> &nbsp; </label>
                <input type="button" class="btn btn-danger btn-block" id="deleteAll" value="<?php echo $text_delete_all; ?>" <?php echo count($list)==0?"disabled":"";?>>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$("#limit").on("change",function(){
    location.href = "<?php echo htmlspecialchars_decode($action); ?>&limit="+this.value
});
$(".editable").on("keyup",function(){
    $(event.target).next().val(this.innerHTML);
});
$("#showAll").on("click",function(){
    location.href = "<?php echo htmlspecialchars_decode($action); ?>";
});
$("#selector2").on("click",function(){
    $("table input[type=checkbox]").prop("checked",this.checked);
});
$("#rebuildSel").on("click",function(){
    if(confirm("<?php echo $warn_rebuild; ?>")){
        $("[name=action]").val("rebuildSel");
        $("#form-seo-url")[0].submit();
    }
});
$("#rebuildAll").on("click",function(){
    if(confirm("<?php echo $warn_rebuild_all; ?>")){
        $("[name=action]").val("autoRebuildAll");
        $("table").html("");
        $("#form-seo-url")[0].submit();
    }
});
$("#checkCol").on("click",function(){
    $("[name=action]").val("checkCollisions");
    $("table").html("");
    $("#form-seo-url")[0].submit();
});
$("#autoAddNew").on("click",function(){
    if(confirm("<?php echo $warn_rebuild_new; ?>")){
        $("[name=action]").val("autoAddNew");
        $("table").html("");
        $("#form-seo-url")[0].submit();
    }
});
$("#deleteSel").on("click",function(){
    if(confirm("<?php echo $warn_delete; ?>")){
        $("[name=action]").val("deleteSel");
        $("#form-seo-url")[0].submit();
    }
});
$("#deleteAll").on("click",function(){
    if(confirm("<?php echo $warn_delete_all; ?>")){
        if(confirm("<?php echo $warn_r_u_sure; ?>")){
            $("[name=action]").val("deleteGodDamnAllOfIt");
            $("table").html("");
            $("#form-seo-url")[0].submit();
        }
    }
});
$("#deleteDef").on("click",function(){
    if(confirm("<?php echo $warn_delete_def; ?>")){
        if(confirm("<?php echo $warn_r_u_sure; ?>")){
            $("[name=action]").val("deleteDefaults");
            $("table").html("");
            $("#form-seo-url")[0].submit();
        }
    }
});
$("#saveData").on("click",function(){
    $("[name=action]").val("save");
    $("#form-seo-url")[0].submit();
});
$("#addManually").on("click",function(){
    $("#form-seo-url tbody").html(`<tr class="newOne"><td><i class="fa fa-trash btn-danger btn" onclick="$(this).closest('tr').remove()"></i><td contenteditable="true" class="editable"></td><input type="hidden" name="newquery[]"><td contenteditable="true" class="editable"></td><input type="hidden" name="newkeyword[]"></tr>`+$("#form-seo-url tbody").html());
    $("#form-seo-url tbody .editable")[0].focus();
    $(".editable").off("keyup").on("keyup",function(){
        $(event.target).next().val(this.innerHTML);
    });
});
$(document).on("click",function(){
    $("#form-seo-url tr").removeClass("selected");
    let l = $("input[type=checkbox][name='sel[]']:checked");
    $(l).closest("tr").addClass("selected")
    if(l.length){
        $("#rebuildSel,#deleteSel").removeAttr("disabled");
    }else{
        $("#rebuildSel,#deleteSel").attr("disabled",true);
    }
    
    if(l.length!=$("#limit").val()){
        $("#selector2").attr("checked",false);
    }
});
</script>
<?php echo $footer; ?>