<style>
    .log-title {
        border: 1px solid grey !important;
        border-radius: 3px !important;
        cursor: pointer;
        padding: 5px;
    }
    .log-div {
        border-radius: 0 !important;
        padding: 10px;
    }
    .log-error {
        color: #2a2a2a;
        font-size: 17px;
    }
</style>


<section class="panel">
    <header class="panel-heading">
        <div class="row">
            <div class="col-sm-12">
                Лог ошибок
            </div>
        </div>
    </header>
    <div class="panel-body">

        <?php if(!$error_count):?>
            <h3 class="orange">Ошибок нет, все хорошо!</h3>
        <?php else:?>

            <div id="accordion">
                <?php
                $unique_key=0;
                foreach($aggregate_logs as $key=>$errors){?>
                    <h3 class="log-title"><?=$key?> (<?=count($errors)?>)</h3>
                    <div class="log-div">
                        <?foreach(array_reverse($errors) as $error=>$more){?>

                            <div class="log-error">
                                <?=Log_Highlighter::hl($error)?>
                            </div>

                            <?if(isset($more['time']) && count($more['time'])):?>
                                <div id="debug-accordion-time-<?=$unique_key?>">
                                    <h4 style="border: 1px solid grey !important;border-radius: 3px !important;cursor: pointer;padding: 5px;"><?=$more['time'][count($more['time'])-1]?> (<b><?=count($more['time'])?></b>)</h4>
                                    <div>
                                        <?foreach($more['time'] as $time){?>
                                            <span style="color: #f40000"><?=$time?></span><br>
                                        <?}?>
                                    </div>
                                </div>
                            <?endif;?>

                            <?if(isset($more['debug'])){?>
                                <div id="debug-accordion-<?=$unique_key?>">
                                    <h4 style="border: 1px solid grey !important;border-radius: 3px !important;cursor: pointer;padding: 5px;">DEBUG</h4>
                                    <div>
                                        <?foreach($more['debug'] as $d_row){?>
                                            <p><?=Log_Highlighter::debug($d_row)?></p>
                                        <?}?>
                                    </div>
                                </div><br>
                            <?}?>
                            <?php
                            $unique_key++;
                        }?>
                    </div>
                <?}?>
            </div>
            <script>
                $(function() {
                    <?for($i=0; $i<$unique_key; $i++){?>
                    $( "#debug-accordion-<?=$i?>" ).accordion({
                        collapsible: true,
                        active: false,
                        heightStyle: "content"
                    });
                    $( "#debug-accordion-time-<?=$i?>" ).accordion({
                        collapsible: true,
                        active: false,
                        heightStyle: "content"
                    });
                    <?}?>

                    $( "#accordion" ).accordion({
                        collapsible: true,
                        active: false,
                        heightStyle: "content"
                    });
                });
            </script>
        <?php endif;?>

        <?if($error_count):?>
            <div class="row">
                <div class="col-sm-12">
                    <form method="post">
                        <input type="hidden" name="path" value="<?=$last_file?>">
                        <input type="hidden" name="position" value="<?=$last_position?>">
                        <input type="hidden" name="line" value="<?=$last_line?>">
                        <input type="submit" class="btn btn-lg btn-success" value="Всё исправлено">
                    </form>
                </div>
            </div>
        <?endif;?>

    </div>
</section>