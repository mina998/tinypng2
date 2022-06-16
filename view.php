<?php if (!isset($option)) return; ?>
<style>
    .tiny_keys{
        margin:0 220px;border-collapse:collapse;
    }
    .tiny_keys, .tiny_keys th, .tiny_keys td{
        border: 1px solid #dcdcde;
        padding:5px;
    }
</style>
<div class="wrap">
    <h1>常规选项</h1>
    <hr style="margin-bottom: 30px;">
    <div style="text-indent:220px; color: #db931d; font-weight:bold">该插件需要网站开启SSL证书, 否则无法使用压缩功能</div>
    <form method="post" action="<?php echo wp_nonce_url('admin.php?page='.$this->plugin_file); ?>">
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="mode">任务模式</label></th>
                <td>
                    <input name="mode" type="hidden"  value="<?php echo $option->mode;?>">
                    <input id="mode" type="checkbox" <?php if ($option->mode){ ?> checked="checked" <?php }?>>
                    "勾选" 使用任务模式 <code>此功能赞不支持</code>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="keys">TinyPNG密钥</label></th>
                <td>
                    <?php
                        if(!empty($option->fail)){
                    ?>
                    <p style="margin-bottom: 10px">验证失败:</p>
                    <div class="error-message" style="font-weight: 400; margin-bottom:10px; line-height:1.5;">
                        <?php
                            foreach($option->fail as $item){
                                echo $item . '<br />';
                            }
                        ?>
                    </div>
                    <?php } ?>
                    <textarea id="keys" rows="4" name="keys" class="regular-text" placeholder="支持多条,一行一条.示例: DRTm5Sj33xDsNR0h3306K0R2kZTqTZcL#500"></textarea>
                    <p>
	                    <?php
	                    wp_nonce_field();
	                    submit_button();
	                    ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
    <?php if ( !empty($option->keys) ) { ?>
    <table class="tiny_keys">
        <tr>
            <th style="text-align:left">TinyPNG密钥</th>
            <th>总次数</th>
            <th>已使用</th>
            <th>操作</th>
        </tr>
        <?php foreach ($option->keys as $id => $item) {?>
        <tr class="<?php echo $id; ?>">
            <td><?php echo $id; ?></td>
            <td class="total"><?php echo $item['total']; ?></td>
            <td class="used"><?php echo $item['used']; ?></td>
            <td id="<?php echo $id; ?>">
                <button class="test">验证</button>
                <button class="del">删除</button>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
    <hr style="margin: 30px 0">
</div>
<script>
    jQuery(function($) {
        $(":checkbox").change(function(){
            if($(this).is(':checked')){
                $(this).prev('input').val(1)
            }else{ $(this).prev('input').val(0) }
        })
        $('.del').click(function() {
            let key = $(this).parent().attr('id');
            $.post('<?php echo admin_url('admin-ajax.php')?>', {'key':key, 'q':'del', 'action':'ask_key'}, function (msg){
                alert(msg)
                if(msg === key){
                    $('.'+key).remove();
                }
            })
        })
        $('.test').click(function() {
            let key   = $(this).parent().attr('id');
            let total = $(this).parent().siblings('.total').html()
            $.post('<?php echo admin_url('admin-ajax.php')?>', {'key':key, 'q':'test', 'total':total, 'action':'ask_key'}, function (msg){
                if(msg >= 0){
                    $('td#'+key).siblings('.used').html(msg)
                    alert('验证完成');
                }else alert('验证失败')
            })
        })

    })
</script>

